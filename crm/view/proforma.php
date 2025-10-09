<?php 
session_start();
include('../include/urlfile.php');           
// error_reporting(E_ALL);
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    FINANCE_PROFORMA_INVOICE_CREATE,
    FINANCE_PROFORMA_INVOICE_EDIT
]);

$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Proforma Invoice";
$countryid='101';
$stateid='1';
$cityid='1';
$hide_sales = 'hide';
$hide_quot = 'hide';
$terms_condition = '';
$quot_type=0;
if(strpos($_SERVER['REQUEST_URI'], "proformaedit")==false)
{
    if(!in_array(FINANCE_PROFORMA_INVOICE_CREATE,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
    $mode="Add";
    $date=date('d-m-Y');
    $load_inv_type='';
    $deleteid=delete_record('tbl_proforma_trn',"trancation_status=3", $dbcon);
    $checked = 'checked';

}
else
{
    if(!in_array(FINANCE_PROFORMA_INVOICE_EDIT,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }

    $mode="Edit";
    $invoiceid = $dbcon->real_escape_string($_REQUEST['id']);
    $query = "select * from tbl_proforma_invoice where invoice_id=$invoiceid";
    $rel = mysqli_fetch_assoc($dbcon->query($query));

    if(!$rel){
        header("Location: ".ROOT."proforma_list");
    }

    if($rel['sales_order_id']!='0'){
        $hide_sales = '';
    }  
    if($rel['quotation_id']!='0'){
        $hide_quot = '';
    } 
// echo "<pre>";print_r($rel);die();

    $order_date='';$dispatch_date='';
    if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
    {
        $order_date=date('d-m-Y',strtotime($rel['order_date']));
    }
    if($rel['dispatch_date']!="1970-01-01 00:00:00" && $rel['dispatch_date']!="0000-00-00 00:00:00")
    {
        $dispatch_date=date('d-m-Y h:i a',strtotime($rel['dispatch_date']));
    }
    $invoice_no=$rel['invoice_no'];
    $challan_no=$rel['challan_no'];
    $load_inv_type=$rel['invoicetype_id'];
    $terms_condition = $rel['terms_condition'];
    $quot_type=$rel['quot_type'];
}
$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));
$terms_condition = (empty($terms_condition)) ? $set_head['conditions'] : $terms_condition;
$currency_id = ($rel['currency_id']) ? $rel['currency_id'] : $set_head['currency_id'];
$getspecialConfiguration=getspecialConfiguration($dbcon);
$companyConfiguration=getCompanyConfiguration($dbcon);
$so_pro_type = $companyConfiguration['so_pro_type'];
$sales_pro_search = $companyConfiguration['sales_pro_search'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>PROFORMA</title>
  <?php include_once('../../include/include_css_file.php');?>
  <style type="text/css">
        .currency_icon{
            color:green;
            font-size:12px;
            font-weight: bold;
        }
  </style>
</head>
<body>
  <section id="container" class="sidebar-closed">
   <?php include_once('../../include/include_top_menu.php');?>
   <!--sidebar start-->
   <?php include_once('../../include/left_menu.php');?>
   <!--sidebar end-->
   <!--main content start-->
   <section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <!--breadcrumbs start -->
                <section class="panel">
                    <header class="panel-heading">
                        <h3 style="float:left;"> <?=$mode .' '.$form?></h3>
                        <?php // include_once("../include/head_menu.php") ?>
                        <br/>
                    </header>
                    <div class="">
                        <ul class="breadcrumb">
                            <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                            <li><a href="<?=ROOT.CRM_ROOT.'proforma_list'?>">Proforma Invoice List</a></li>
                        </ul>
                    </div>
                </section>
                <!--breadcrumbs end -->
            </div>
        </div>
        <!--state overview start-->
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        New <?=$form?>
                    </header>
                    <div class="panel-body">
                        <form class="form-horizontal" role="form" id="invoice_add" action="javascript:;" method="post" name="invoice_add">
                            <div class="row">
                                <div class="col-md-12" style="margin-bottom: 20px">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">
                                                <input type="radio" class="performa_invoice_type" id="performa_invoice_quotation" name="performa_invoice_type" style="height: 18px;width: 18px;" value="1" onchange="get_quotation_and_salesorder();load_company_data();tc_format_view()" <?php if($rel['performa_invoice_type']=='1'){ echo "checked"; } ?> >
                                                <strong>Quotation</strong></label>
                                                <label class="col-md-4 control-label">
                                                    <input type="radio" class="performa_invoice_type" id="performa_invoice_so" name="performa_invoice_type" style="height: 18px;width: 18px;" value="2" onchange="load_company_data();get_quotation_and_salesorder();tc_format_view()" <?php if($rel['performa_invoice_type']=='2'){ echo "checked"; } ?>>
                                                    <strong>Sales Order</strong></label>
                                                    <label class="col-md-4 control-label">
                                                        <input type="radio" class="performa_invoice_type" id="performa_invoice_direct" name="performa_invoice_type" style="height: 18px;width: 18px;" value="3" onchange="get_quotation_and_salesorder();load_company_data();tc_format_view()" <?php if($rel['performa_invoice_type']=='3'){ echo "checked"; } ?> <?=$checked?>>
                                                        <strong>Direct</strong></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Company *</label>
                                                        <div class="col-md-6 col-xs-10">
                                                            <select class="select2" name="cust_id" id="cust_id" onchange="load_consignee(this.value);get_quotation_and_salesorder();get_statecode(this.value);get_grossbalance(this.value);get_invoice_total_tax();get_gtotal();get_ledger_details(this.value);" >
                                                                <!-- <=getcust($dbcon,$rel['cust_id'],'');?>   -->
                                                            </select>
                                                            <strong style="display:none;color:green" id="gross">Gross balance : <span class="gross"></span></strong> <br><strong id="statecode" style="display:none;color:blue">GST StateCode : <span class="statecode"></span></strong><strong id="sez_enable_text" style="display:none;color:red">This Party Is SEZ Enabled</strong> 
                                                        </div>
                                                        <div class="col-md-1">
                                                            <button accesskey="n" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" value="R1" onclick="showledger();" data-tooltip="Add New Company" id="addlegderbtn"><i class="fa fa-plus"></i></button>
                                                            <button accesskey="n" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" value="R1" onclick="showparty();" data-tooltip="Add New Company" style="display: none;" id="addpartybtn"><i class="fa fa-plus"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                            <div class="form-group">
                                                <?php 
                                                $ck='';
                                                if(empty($rel['consignee_id'])){
                                                    $ck='checked="checked"';
                                                }
                                                ?>
                                                <label class="col-md-8 control-label" >
                                                    <input id="same_as" name="same_as" type="checkbox" class="" title="Other Name"  <?=$ck?> value="1" style="width:15px;height:25px;" onChange="consinee_change(this.checked);"> Same Consignee
                                                </label>
                                            </div>
                                        </div>
                                                <div class="col-md-4" id="consignee" style="<?= (empty($rel['consignee_id'])) ? "display:none;" : "" ?>">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label"> Delivery At </label>
                                                        <div class="col-md-6 col-xs-10">
                                                            <select class="select2" name="consignee_id" id="consignee_id">
                                                                <!--  <=get_custmer_consignee($dbcon,$rel['cust_id'],$rel['consignee_id'])?> -->
                                                            </select>
                                                        </div>
                                                            <!-- <div class="col-md-3">
                                                            <input type="button" class="btn btn-primary" name="addcust" id="addcust" onClick="open_consignee_click();" value="New Consignee"/>
                                                        </div> -->
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="col-md-4 hide quotation_div <?=$hide_quot?>">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label"> Quotation No.</label>
                                                        <div class="col-md-8 col-xs-10">
                                                            <select style="padding-right: 0px;" class="form-control" name="quotation_id" id="quotation_id" placeholder="" onchange="insert_quotation_salesorder_item(this.value); get_quotation_detail(this.value);">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4  sales_order_div <?=$hide_sales?>">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label"> Sales Order No.</label>
                                                        <div class="col-md-8 col-xs-10">
                                                            <select style="padding-right: 0px;" class="form-control" name="sales_order_id" id="sales_order_id_data" placeholder="" onchange="insert_quotation_salesorder_item(this.value);get_so_detail(this.value);">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Proforma Invoice No *</label>
                                                        <div class="col-md-8 col-xs-12">
                                                            <input id="invoice_no" name="invoice_no" type="text" class="form-control" title="Enter Invoice No" placeholder="Invoice No" value="<?=$invoice_no?>" placeholder="Invoice No" required>      
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Proforma Invoice Date*</label>
                                                        <div class="col-md-8 col-xs-12">
                                                            <input id="invoice_date" name="invoice_date" type="text" class="form-control default-date-picker required valid" title="Invoice Date" placeholder="Invoice Date" value="<?phpif($mode=='Add'){echo $date;}else if($mode=='Edit'){echo date('d-m-Y',strtotime($rel['invoice_date']));}?>" placeholder="Invoice Date">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Payment Terms </label>
                                                        <div class="col-md-8 col-xs-12">
                                                            <input id="payment_terms" name="payment_terms" type="text" class="form-control" title="" value="<?=$rel['payment_terms']?>" placeholder="Payment Terms">    
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12" >
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">P.O. No </label>
                                                        <div class="col-md-8 col-xs-12">
                                                            <input id="order_no" name="order_no" type="text" class="form-control" title="Enter Order No" value="<?=$rel['order_no']?>" placeholder="P.O. No">     
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">P.O. Date</label>
                                                        <div class="col-md-8 col-xs-12">
                                                            <input id="order_date" name="order_date" type="text" class="form-control default-date-picker valid" title="Date" value="<?=$order_date?>" placeholder="P.O. Date">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Vehicle No.</label>
                                                        <div class="col-md-8 col-xs-12">
                                                            <input id="vehicle_no" name="vehicle_no" type="text" class="form-control" title="Vehicle No" value="<?=$rel['vehicle_no']?>" placeholder="Vehicle No">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?phpif($getspecialConfiguration['umaboy_permission']=="1" || $getspecialConfiguration['umaboy_permission']=="1"){ ?>
                                                <div class="col-md-12">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Delivery Note </label>
                                                            <div class="col-md-8 col-xs-12">
                                                                <input id="delivery_note" name="delivery_note" type="text" class="form-control" title="Enter Delivery Note" value="<?=$rel['delivery_note']?>" placeholder="Delivery Note">     
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Supplier's Ref </label>
                                                            <div class="col-md-8 col-xs-12">
                                                                <input id="supplier_ref" name="supplier_ref" type="text" class="form-control" title="Enter Supplier's Ref" value="<?=$rel['supplier_ref']?>" placeholder="Supplier's Ref">     
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Other Reference(s) </label>
                                                            <div class="col-md-8 col-xs-12">
                                                                <input id="other_reference" name="other_reference" type="text" class="form-control" title="Enter Other Reference(s)" value="<?=$rel['other_reference']?>" placeholder="Other Reference(s)">     
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Dispatch Document No. </label>
                                                            <div class="col-md-8 col-xs-12">
                                                                <input id="dispatch_document_no" name="dispatch_document_no" type="text" class="form-control" title="Enter Dispatch Document No." value="<?=$rel['dispatch_document_no']?>" placeholder="Dispatch Document No.">     
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Dispatch Document Date </label>
                                                            <div class="col-md-8 col-xs-12">
                                                                <input id="dispatch_document_date" name="dispatch_document_date" type="text" class="form-control default-date-picker valid" title="Enter Dispatch Document Date" value="<?=$rel['dispatch_document_date']?>" placeholder="Dispatch Document Date">    
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Dispatched Through  </label>
                                                            <div class="col-md-8 col-xs-12">
                                                                <input id="dispatched_through" name="dispatched_through" type="text" class="form-control" title="Enter Dispatched Through " value="<?=$rel['dispatched_through']?>" placeholder="Dispatched Through ">     
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Destination </label>
                                                            <div class="col-md-8 col-xs-12">
                                                                <input id="destination" name="destination" type="text" class="form-control" title="Enter Destination" value="<?=$rel['destination']?>" placeholder="Destination">     
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php} ?>
                                            <div class="col-md-12">
                                                <div class="col-md-4 currency_div"  style="<?php if($mode=='Edit' && $rel['currency_enable']==1){ echo "display:block";  }else{echo 'display:block';}  ?>">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Convert Currency *</label>
                                                        <div class="col-md-8">
                                                            <select class="select2" name="currency_id" id="currency_id" onChange="get_symbol();currency_rate_c();">
                                                                <?=getcurrency($dbcon,$currency_id);?>
                                                            </select>

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 currency_div" style="<?php if($mode=='Edit' && $rel['currency_enable']==1){ echo "display:block";  }else{echo 'display:block';}  ?>">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Rate *</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <input id="currency_rate" name="currency_rate" type="text" class="form-control valid numbersOnly" title="" value="<?=$rel['currency_rate']?>" placeholder="">
                                                        </div>
                                                    </div>  
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">GST Type</label>
                                                        <div class="col-md-8 col-xs-11">
                                                        
                                                            <select class="form-control" name="gst_type" id="gst_type"  onchange="calculate_gst_to_all_product(this.value)">
                                                                <option value="1" <?php if($rel['gst_type']==1){ echo "selected"; } else{ echo ""; } ?>>Item Wise Tax</option>
                                                                <option value="2" <?php if($rel['gst_type']==2){ echo "selected"; } else{ echo ""; } ?> >Merchant</option>
                                                                <option value="3" <?php if($rel['gst_type']==3){ echo "selected"; } else{ echo ""; } ?> >SEZ</option>
                                                                <option value="4" <?php if($rel['gst_type']==4){ echo "selected"; } else{ echo ""; } ?> >GST 0%</option>
                                                                <option value="5" <?php if($rel['gst_type']==5){ echo "selected"; } else{ echo ""; } ?> >GST 5%</option>
                                                                <option value="6" <?php if($rel['gst_type']==6){ echo "selected"; } else{ echo ""; } ?> >GST 12%</option>
                                                                <option value="7" <?php if($rel['gst_type']==7){ echo "selected"; } else{ echo ""; } ?> >GST 18%</option>
                                                                        <option value="8" <?php if($rel['gst_type']==8){ echo "selected"; } else{ echo ""; } ?> >GST 24%</option>       
                                                            </select>
                                                        </div>
                                                     </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Sales Order Type</label>
                                                        <div class="col-md-8"> 
                                                            <label class="col-md-6" style="font-weight:bold;"><input type="radio" id="quot_type_domestic" name="quot_type" onclick="load_typeswise_terms(this.value,<?=$sales_order_id?>);" value="0" <?=($quot_type!='1')?'checked':''?> > Domestic</label>
                                                            <label class="col-md-5 " style="font-weight:bold;"><input type="radio" id="quot_type_export" name="quot_type" onclick="load_typeswise_terms(this.value,<?=$sales_order_id?>);" value="1" <?=($quot_type=='1')?'checked':''?>> Export</label>
                                                        </div>
                                                    </div>  
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Terms of Delivery </label>
                                                        <div class="col-md-8 col-xs-12">
                                                            <textarea class="form-control" name="terms_delivery" id="terms_delivery" placeholder="Terms of Delivery"><?=$rel['terms_delivery']?></textarea>     
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">LR-RR No </label>
                                                        <div class="col-md-8 col-xs-12">
                                                            <input type="text" name="lr_rr_no" id="lr_rr_no" value="<?=$rel['lr_rr_no']?>" class="form-control" placeholder="LR-RR No">     
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Port of Loading </label>
                                                        <div class="col-md-8 col-xs-12">
                                                            <input type="text" name="port_of_loading" id="port_of_loading" value="<?=$rel['port_of_loading']?>" class="form-control" placeholder="Port of Loading">     
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Final Destination </label>
                                                        <div class="col-md-8 col-xs-12">
                                                            <input type="text" name="final_destination" id="final_destination" value="<?=$rel['final_destination']?>" class="form-control" placeholder="Final Destination">     
                                                        </div>
                                                    </div>
                                                </div><div class="col-md-4">
													<div class="form-group">	
														<label class="col-md-4   control-label">Client Id</label>
														<div class="col-md-8 col-xs-12"> 
															<input type="text" id="client_id" name="client_id" class="form-control" title="Client Id" value="<?=$rel['client_id']?>" placeholder="Client Id">
														</div>
													</div>	
												</div>
                                        </div>
                                        	
                                        <div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label" >Transport</label>
														<div class="col-md-8 col-xs-11">      
															<select class="form-control" name="transid" id="transid" onchange="load_trans_add();">
																<?=gettransp($dbcon,$rel['transid']);?>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label" >Transport Address</label>
														<div class="col-md-8 col-xs-11">      
															<select class="form-control" name="trans_add" id="trans_add" >
																<?php //=getpaymentterms($dbcon,$rel['payment_terms']);?>
															</select>
															<input type="hidden" name="trans_add_ed" id="trans_add_ed" value="<?=$rel['trans_add']?>" />
														</div>
													</div>
												</div>
											</div>
                                        <div class="col-md-12">
                                            <div class="card">
                                                <ul class="nav nav-tabs" id="my_tab_id" role="tablist">
                                                    <li role="presentation" id="tab1" class="active"><a href="#product-details" aria-controls="product-details" role="tab" data-toggle="tab">Product Details</a></li>
                                                    <li role="presentation" id="tab2"><a href="#product-desc" aria-controls="product-desc" role="tab" data-toggle="tab">Description</a></li>
                                                </ul>
                                                <!-- Tab panes -->
                                                <div class="tab-content">
                                                    <!-- Remaks Tab Start -->
                                                    <div role="tabpanel" class="tab-pane active" id="product-details">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <table cellspacing="10" style="border-spacing:10px;table-layout: fixed;" id="product_list" class="display table table12 table-striped table-bordered  ">
                                                                    <tr id="field">
                                                                        <!-- <th width="5%" class="text-center"></th> -->
                                                                        <?php if($companyConfiguration['category_selection_active']==1){ ?>
                                                                            <th width="8%" class="text-center">Category</th>
                                                                        <?php} ?>
                                                                        <th width="20%" class="text-center">Product Detail</th>
                                                                        <?php if($getspecialConfiguration['global_eng_permission']==1){?>
                                                                                            <th width="8%" class="text-center">Size</th>
                                                                                        <?php }?>
                                                                        <th width="8%" class="text-center" style="display:none">HSN Code</th>
                                                                        <th width="7%" class="text-center">Per</th>
                                                                        <th width="6%" class="text-center">Quantity</th>
                                                                        <th width="7%" class="text-center">Rate<span class="currency_icon"></span></th>
                                                                        <th width="6%">Discount<span class="currency_icon"></span></th>
                                                                        <!-- <th width="10%">Taxable Value</th>
                                                                            <th width="13%">Tax</th> -->
                                                                            <th width="10%" class="text-center">Amount<span class="currency_icon"></span></th>
                                                                            <th width="5%" class="text-center"></th>
                                                                        </tr>
                                                                        <input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
                                                                        <input type="hidden" id="inquiry_type" name="inquiry_type" value="1">
                                                                        <tr id="field1">
                                                                            <?php if($companyConfiguration['category_selection_active']==1){ ?>
                                                                                <td data-label="PRODUCT CATEGORY" style="vertical-align:top;">
                                                                                    <select class="select2" title="Select Category" name="product_category_id" id="product_category_id" <?php if($companyConfiguration['cat_wise_product_load'] ==1){?>onchange="product_load()"<?php }?>>
                                                                                        <?=get_all_category($dbcon,$rel['product_category_id']);?>
                                                                                    </select>
                                                                                </td>
                                                                            <?php} ?>
                                                                            <td data-label="PRODUCT NAME" class="resclear" style="vertical-align:top;">
                                                                                <input id="product_id" name="product_id" style="width:100%;" placeholder="Select product" onchange="load_productdetail(this.value);get_hsn(this.value);" /><br>
                                                                                <strong class="hsncode" style="display:none;color:blue">HSN Code : <span id="hsncode"></span></strong>&nbsp;&nbsp;&nbsp;
                                                                                <?phpif($getspecialConfiguration['oilfield_permission']==1){ ?>
                                                                                <button accesskey="n" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" onclick="showproduct()"><i class="fa fa-plus"></i> Add Product</button>
                                                                            <?php} ?>
                                                                            </td>
                                                                            <?php if($getspecialConfiguration['global_eng_permission']==1){?>
                                                                                    <td>
                                                                                    <input type="text" class="form-control" id="item_size" name="item_size" value="">
                                                                                    </td>
                                                                                    <?php }?>
                                                                            <td data-label="HSN CODE" style="vertical-align:top; display: none;">
                                                                                <input type="text"  title="Enter HSN Code" placeholder="HSN Code" id="product_hsn" name="product_hsn" class="form-control"/>
                                                                                <input type="hidden" id="product_hsn_code" name="product_hsn_code"/>
                                                                            </td>
                                                                            <td data-label="PER" style="vertical-align:top;">
                                                                                <select class="form-control" title="Select Unit" name="rate_unit_id" id="rate_unit_id" onchange="load_product_unit();getrate();">
                                                                                    <option value="0">Select Unit</option>
                                                                                    <?php //=getunit($dbcon,0);?>
                                                                                </select>
                                                                                <input type="hidden" name="p_qty" id="p_qty">
                                                                            <td data-label="QTY" style="vertical-align:top;">
                                                                                <div id="convert_unit_block" style="display:none;" >
                                                                                    <input type="text"  title="Enter Qty" min="0" id="product_conv_qty" name="product_conv_qty"  class="form-control numbersOnly" onkeyup="product_convert_qty(1);"onChange="get_discount('per');" />
                                                                                    <input type="hidden" name="conv_unitid" id="conv_unitid" value="" />
                                                                                    <input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />
                                                                                    <span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs" id="convert_unit_show">  </span>
                                                                                </div>  
                                                                                <div id="base_unit_block">
                                                                                    <input type="text"  title="Enter Qty" min="0" id="product_qty" name="product_qty"  class="form-control numbersOnly" onkeyup="product_convert_qty(2);" onchange="get_discount('per');" />
                                                                                    <input type="hidden" name="unitid" id="unitid" value="" />
                                                                                    <input type="hidden" id="product_qty_hide" name="product_qty_hide" value="" />
                                                                                    <span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs" id="unit_show" >  </span>
                                                                                </div>
                                                                                <!-- <input type="number"  title="Enter Qty" min="0" id="product_qty" name="product_qty"  class="form-control" onkeyup="get_amount();"/><br/> -->
                                                                                <!--<button type="button" title="Serial Number" name="serial_btn" id="serial_btn" onclick="open_serial_number()" class="btn btn-info"><i class="fa fa-refresh"></i> Serial</button>-->
                                                                            </td>
                                                                            <td data-label="RATE" style="vertical-align:top;">
                                                                                <input type="number"  title="Enter Rate" min="0" id="product_rate" name="product_rate"  placeholder="Rate" onkeyup="get_amount();" class="form-control"/><br/>
                                                                                <!--<button type="button" title="Show Previous Rate History" name="rate_history" id="rate_history" onclick="load_rate_hist()" style="display:none;" class="btn btn-info"><i class="fa fa-eye"></i> show</button>-->
                                                                            </td>
                                                                            <td data-label="DISCOUNT" style="vertical-align:top;">
                                                                                <input type="number" title="Enter Discount" min="0" id="product_discount" name="product_discount" onkeyup="get_discount('amt');" class="form-control" placeholder="in Rs."/><br/>
                                                                                <input type="number"  title="Enter Discount Percentage" min="0" id="discount_per" name="discount_per" onkeyup="get_discount('per');" class="form-control" placeholder="in %" max="100"/>
                                                                            </td>
                                                                       <!--  <td data-label="TAXABLE VALUE" style="vertical-align:top;">
                                                                            <input type="number" title="Taxable Value" placeholder="Taxable Value" min="0" id="taxable_value" name="taxable_value" class="form-control" readonly/>
                                                                        </td>
                                                                        <td data-label="TAX" style="vertical-align:top;">
                                                                            <select class="form-control" name="formulaid" id="formulaid" onChange="get_amount();">
                                                                                <?php 
                                                                                //echo getformula($dbcon,$rel['formulaid']);
                                                                                ?>
                                                                            </select>
                                                                        </td> -->
                                                                        <td data-label="AMOUNT" style="vertical-align:top;"> 
                                                                            <input type="number" min="0" id="product_amount" readonly="readonly" name="product_amount" class="form-control" onmouseover="this.title=this.value"/>
                                                                        </td>
                                                                        <td data-label="ACTION" style="vertical-align:top;"> 
                                                                        <?php if ($getspecialConfiguration['durva_permission']==1){?>
                                                                            <input type="button"  name="addrow1" id="addrow1" onClick="open_batch_wise_qty()"  class="btn btn-primary product_add_batch_wise" value="Add" />
                                                                            <button type="button" class="btn btn-primary" id="addrow" style=" display:none;" onclick="add_field()">Add</button>
                                                                        <?php }else{?>
                                                                            <input type="button"  name="addrow" id="addrow" onclick="return add_field();"  class="btn btn-primary" value="Add"/> 
                                                                        <?php }?>

                                                                        </td>
                                                                        <input type='hidden' name='edit_id' id='edit_id' value='' />
                                                                        <input type='hidden' name='pro_cal_type' id='pro_cal_type' value='' />
                                                                        <input type="hidden" name="cust_stateid" id="cust_stateid">
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" id="product-desc" >
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Description</label>
                                                                    <div class="col-md-12">
                                                                        <textarea class="form-control" id="product_des" name="product_des" placeholder="Enter Product Description"><?=$rel['product_des']?></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Specification</label>
                                                                    <div class="col-md-12">
                                                                        <textarea class="form-control" id="product_spec" name="product_spec" placeholder="Enter Product Specification"><?=$rel['product_spec']?></textarea> 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="sale_productdata">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="tax_details"></div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Remarks </label>
                                                <div class="col-md-6 col-xs-11">
                                                    <textarea id="remark" name="remark" placeholder="Remarks" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
                                                </div>
                                            </div>
                                            <div class="col-md-12"> 
                                                <div class="col-md-3">
                                                   <div class="form-group">
                                                        <input type="radio" class="" name="tc_format" id="format1" value="1" onchange="tc_format_view();" <?phpif($rel['tc_format'] == '1'){ echo 'checked="checked"';}else{ if($mode == 'Add'){echo 'checked="checked"';} }?> > Format-1                 
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                   <div class="form-group">
                                                        <input type="radio" class="" name="tc_format" onchange="tc_format_view();" id="format2" value="2" <?phpif($rel['tc_format'] == '2'){ echo 'checked="checked"';}?>> Format-2                 
                                                    </div>
                                                </div>

                                                <div class="col-md-12" style="margin-top:12px" id="format_1">
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label">Terms & Conditions </label>
                                                        <div class="col-md-9 col-xs-11">
                                                            <textarea id="terms_condition" name="terms_condition" class="form-control" placeholder="Terms & Conditions"><?=$terms_condition?></textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12 " style="margin-top:12px" id="format_2">
                                                    <div class="col-md-3 common_terms">
                                                        <div class="form-group">
                                                            <input type="radio" class="" name="terms_type" id="common_terms" value="0" onchange="load_typeswise_terms();" 

                                                            <?phpif($rel['terms_type'] == '0'){ echo 'checked="checked"';}else{ if($mode == 'Add'){echo 'checked="checked"';} }?> > Common Terms 
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 party_terms">
                                                        <div class="form-group">
                                                            <input type="radio" class="" name="terms_type" id="party_terms" value="1" onchange="load_typeswise_terms();" 

                                                            <?phpif($rel['terms_type'] == '1'){ echo 'checked="checked"';}?> > Party Terms 
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 ledger_terms">
                                                        <div class="form-group">
                                                            <input type="radio" class="" name="terms_type" id="ledger_terms" value="2" onchange="load_typeswise_terms();" 

                                                            <?phpif($rel['terms_type'] == '2'){ echo 'checked="checked"';}?> > Ledger Terms 
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 quotation_terms">
                                                        <div class="form-group">
                                                            <input type="radio" class="" name="terms_type" id="quotation_terms" value="3" onchange="load_typeswise_terms();" 

                                                            <?phpif($rel['terms_type'] == '3'){ echo 'checked="checked"';}?> > Quotation Terms 
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 sales_order_terms">
                                                        <div class="form-group">
                                                            <input type="radio" class="" name="terms_type" id="sales_order_terms" value="4" onchange="load_typeswise_terms();" 

                                                            <?phpif($rel['terms_type'] == '4'){ echo 'checked="checked"';}?> > Sales Order Terms 
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 multi_condition">
                                                        <div class="form-group">
                                                            <input type="radio" class="" name="terms_type" id="multi_condition" value="5" onchange="load_typeswise_terms();" 

                                                            <?phpif($rel['terms_type'] == '5'){ echo 'checked="checked"';}?> > Multi Condition 
                                                        </div>
                                                    </div>

                                                    <div class="form-group" id="proforma_terms_cond_div">

                                                    </div> 
                                                </div>
                                            </div>
                                            

                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Reverse Charge  </label>
                                                <div class="col-md-1 col-xs-11">
                                                    <input id="reverse_charge_check"  name="reverse_charge_check" type="checkbox" class="" title="Reverse Charge" placeholder="Reverse Charge" <?=(empty($rel['reverse_charge'])?'':'checked="checked"')?>  value="1">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-5 control-label">Total * <span class="currency_icon"></span></label>
                                                <div class="col-md-5 col-xs-11">
                                                    <input id="total" name="total" type="text" readonly="readonly" class="form-control" title="Total" max="0"  value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;}?>" placeholder="Total">
                                                </div>
                                            </div>
                                            <!-- <div class="form-group">
                                                <label class="col-md-5 control-label">Discount * <span class="currency_icon"></span></label>
                                                <div class="col-md-5 col-xs-11">
                                                    <input id="discount" name="discount" type="number"  class="form-control" title="Discount" max="0"  value="<=$rel['discount']?>" onkeyup="addBillSundry('1')" placeholder="Total">
                                                </div>
                                            </div> -->
                                            <div class="invoiceTotalTax">

                                            </div>
                                            <div class="sundryadded">

                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-5 control-label">Net Amount * <span class="currency_icon"></span></label>
                                                <div class="col-md-5 col-xs-11">
                                                    <input id="g_total" name="g_total" type="text" class="form-control" title="Net Amount" value="<?=$rel['g_total']?>" placeholder="Grand Total" readonly="readonly">
                                                </div>
                                            </div>
                                            <div>
                                                <div class="form-group">
                                                    <label class="col-md-5 control-label">Select Bill Sundry</label>
                                                    <div class="col-md-2">
                                                        <?php $get_bill_sundry = get_bill_sundry_ledger($dbcon,0); ?>
                                                        <select class="form-control" name="bill_sundry" id="bill_sundry" onchange="get_sundry_label(this.value);">
                                                            <option value="0">Select</option>
                                                            <?php foreach ($get_bill_sundry as $sundry) { ?>
                                                                <option value="<?php echo $sundry['l_id'] ?>"><?php echo $sundry['l_name']; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input id="bill_sundry_amount" name="bill_sundry_amount" type="text" class="form-control numbersOnly" placeholder="Amount" title="Amount" value="<?=$rel['amount']?>" placeholder="" >
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" type="button" value="R1" onclick="addBillSundry();"><i class="fa fa-plus"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-5 control-label">Advance Payment * <span class="currency_icon"></span></label>
                                                <div class="col-md-5 col-xs-11">
                                                    <input id="advance_payment" name="advance_payment" type="text" class="form-control numbersOnly" onkeyup="get_gtotal()" onchange="get_gtotal()" title="Advance Amount" value="<?=(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['advance_payment'] : $rel['advance_payment_conv'])?>" placeholder="Advance Payment" >
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-5 control-label">Payable Amt * <span class="currency_icon"></span></label>
                                                <div class="col-md-2 col-xs-11">
                                                    <input id="adv_per" name="adv_per" type="text" class="form-control numbersOnly" title="Enter Valid Value" onkeyup="get_advance('per');"  value="<?=$rel['payable_per']?>" placeholder="in (%)" max="100" >
                                                </div>
                                                <div class="col-md-3 col-xs-11">
                                                    <input id="adv_amt" name="adv_amt" type="text" class="form-control numbersOnly" title="Enter Valid Value" onkeyup="get_advance('amt');"  value="<?=(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['payable_amt'] : $rel['payable_amt_conv'])?>" placeholder="in Rs." >
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-5 control-label">Pending Amount * <span class="currency_icon"></span></label>
                                                <div class="col-md-5 col-xs-11">
                                                    <span id="pending_amount" style="color:red;font-weight: bold;"><?=$rel['pending_amt']?></span>
                                                    <input type="hidden" name="pen_amt" id="pen_amt" value="<?=$rel['pending_amt']?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-md-5 control-label">Select Print</label>
                                                <div class="col-md-5 col-xs-11">
                                                    <select class="form-control" name="print_status" id="print_status">
                                                        <option value="1">ORIGINAL</option>
                                                        <option value="2">DUPLICATE</option>
                                                        <option value="3">TRIPLICATE</option>
                                                        <option value="4">EXTRA</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-success" id="save" name="save">Save</button>
                                            <button type="button" onClick="invoice_submit();" class="btn btn-success" id="saveprint" name="saveprint">Save and Print</button> &nbsp;
                                            <a href="<?=ROOT.CRM_ROOT.'proforma_list'?>" type="button" class="btn btn-danger">Cancel</a>
                                            <div class="col-md-3"></div>
                                        </div>
                                        <input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
                                        <input type='hidden' name='o_total' id='o_total' value='<?=$rel['g_total']?>' />
                                        <input type='hidden' name='save_print' id='save_print' value='' />
                                        <input type='hidden' name='eid' id='eid' value='<?=$rel['invoice_id']?>' />
                                        <?php$receiptno= 'rec/'.$invoiceid;?>
                                        <input type='hidden' name='receipt_no' id='receipt_no' value='<?=$receiptno?>' />
                                        <input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />

                                        <input type='hidden' name='pro_type' id='pro_type' value='<?=$so_pro_type?>' />

                                        <input type='hidden' name='pro_search' id='pro_search' value='<?=$sales_pro_search?>' />
                                        
                                        <input type="hidden" name="edit_customer_id" id="edit_customer_id" value="<?=$rel['cust_id']?>">
                                        <!-- <input type="hidden" id="sales_order_id" value="<?=$rel['sales_order_id']?>"> -->
                                        <input type="hidden" id="edit_sales_order_id" value="<?=$rel['sales_order_id']?>">
                                        <input type="hidden" id="edit_quotation_id" value="<?=$rel['quotation_id']?>">
                                        <input type="hidden" id="edit_consignee_id" value="<?=$rel['consignee_id']?>">
                                        <input type='hidden' name='invoicetype_id' id='invoicetype_id' value='<?php if($mode == "Edit"){ echo $rel['invoicetype_id']; }?>' /> 
                                        <input type="hidden" name="print_path" id="print_path" value="<?=get_print_path($dbcon,'2');?>" />             
                                    </form>
                                </div>
                            </section>
                        </div>
                    </div>
                    <!--state overview end-->
                </section>
            </section>
            <!--main content end-->
            <!--footer start-->
            <?php include_once('../include/add_cust.php');?>
            <?php include_once('../include/add_product.php');?>
            <?php include_once('../../administration/include/add_city.php');?>
            <?php include_once('../../administration/include/add_state.php');?>
            <?php include_once('../../include/add_payterms.php');?>
            <?php include_once('../include/add_accessories_product.php');?>
            <?php include_once('../include/add_accessories_product_list.php');?>
            <?php include_once('../../finance/include/add_ledger.php'); ?>
            <?php include_once('../../administration/include/add_product.php'); ?>
            <?php include_once('../../administration/include/add_hsn_in_popup.php'); ?>
            <?php include_once('../../include/footer.php');?>
            <?php include_once('../../include/add_placesupally.php');?>
            <?php include_once('../../include/add_modedispatch.php');?>
            <!-- <php include_once('../../include/add_worktype.php');?>
            <php include_once('../../include/add_invdescription.php');?> -->
            <!--footer end-->
        </section>
        <!-- js placed at the end of the document so the pages load faster -->
        <?php 
        include_once('../../include/include_js_file.php');
        include_once('../../include/add_consignee.php');
//include_once('../include/serial_number_add.php');
        include_once('../../include/include_show_history.php');
        ?>   
        <script src="<?=ROOT.CRM_ROOT?>js/app/proforma.js"></script>
        <script src="<?=ROOT.CRM_ROOT?>js/app/customer.js?<?=time()?>"></script>     
        <script src="<?=ROOT.CRM_ROOT?>js/app/payment_terms.js"></script>
        <script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/product_mst.js?<?php echo time(); ?>"></script>
        <script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/add_ledger_js.js?<?=time()?>"></script>
        <script src="<?=ROOT?><?=ADMINISTRATION_ROOT?>js/app/ledger.js?<?=time()?>"></script>
        <script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/consignee.js?<?=time()?>"></script>
        <script src="<?=ROOT.CRM_ROOT?>js/app/place_supply.js"></script>
        <script src="<?=ROOT.CRM_ROOT?>js/app/mode_disptch.js"></script>
        <script src="<?=ROOT.CRM_ROOT?>js/app/work_type.js"></script>
        <script src="<?=ROOT.CRM_ROOT?>js/app/description_mst.js"></script>
        <!--<script src="js/count.js"></script>-->
        <script>
            $(".select2").select2({
                width: '100%'
            });
            $('.default-date-picker').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            });
            $(".form_datetime-meridian").datetimepicker({
                format: "dd-mm-yyyy HH:ii P",
                showMeridian: true,
                autoclose: true,
                todayBtn: true,
                pickerPosition: "bottom-left"
            });


            function paymentmode(id)
            {
                if(id=="2")
                {  
                    $('#cheque_dtl').val('');
                    $('#cheque_data').show();
                }
                else
                    $('#cheque_data').hide();
            }
            function consinee_change(val){
                if(val=='1'){
                    $('#consignee_id').select2("val","");
                    $('#consignee').hide();
                }
                else{
                    $('#consignee').show();
                }

            }

            CKEDITOR.replace( 'product_des', {
                enterMode: CKEDITOR.ENTER_BR
            });

            CKEDITOR.replace( 'terms_condition', {
                enterMode: CKEDITOR.ENTER_BR
            });
            
            CKEDITOR.replace( 'product_spec', {
                enterMode: CKEDITOR.ENTER_BR
            });
        </script>
        <?phpif($mode=="Add")
        {
            echo "<script>show_data();</script>";
//echo "<script>load_invoiceno(".$load_inv_type.");</script>";
            echo "<script>get_series_no() </script>";
            echo "<script>get_symbol() </script>";
            echo "<script>currency_rate_c()</script>";
            echo "<script>load_typeswise_terms('')</script>";
        }else{
            echo "<script>get_statecode(".$rel['cust_id'].")</script>";
            echo "<script>get_all_bill_sundry(".$invoiceid.")</script>";
            echo "<script>get_symbol() </script>";
            echo "<script>load_typeswise_terms(".$invoiceid.")</script>";
        }
        ?>
    </body>
    </html>