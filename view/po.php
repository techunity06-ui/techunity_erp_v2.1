<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
include_once("../include/function_database_query.php");
$form="Purchase Order";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];

//echo "<br/>";
//echo $_SERVER['HTTP_REFERER'];
//echo "<br/>";
//echo $_SERVER['REQUEST_URI'];
$countryid='101';
$stateid='1';
$cityid='1';
$currency_id=$_SESSION['currency_id'];
$conversion_rate = $_SESSION['currency_rate'];
$vendor_reference='';$quotation_no='';$quotation_date='d-m-Y';
$godown="";
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    PO_LIST_UPDATE
]);
$branch_id = $_SESSION['branch_id'];
if(strpos($_SERVER[REQUEST_URI], "poedit")==true)
{

    if(!in_array(PO_LIST_UPDATE,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
    $back="po_list";
    $mode="Edit";$direct_add='0';$request=0;$viewmode="Edit";
    $purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
    $query="select * from tbl_purchaseorder where purchaseorder_id=$purchaseorder_id";
    $rel=mysqli_fetch_assoc($dbcon->query($query)); 
    $purchaseorder_date = date('d-m-Y',strtotime($rel['purchaseorder_date']));
    $po_type_status=$rel['po_type_status'];
    $vender_id=$rel['vender_id'];
    $currency_id = $rel['currency_id'];
    $_SESSION['selected_vendor'] = $vender_id;
    $conversion_rate = $rel['conversion_rate'];
    $godown = $rel['godown_id'];   
    $isDisabled = true;
    $isRequired = false;
    $branchId=$rel['branch_id'];
    if($row['po_approval_status']=='1'){
        $apstatus = '<span class="btn  btn-success" >Approved</span>';
    }
    else{
        $apstatus = '<span class="btn  btn-warning">Approval Pending</span>';
    }
    $product_wise="";
    $powise="";
    if(strtolower($rel['delivery_type'])=="product_wise"){
        $product_wise='selected="selected"';
    }else{
        $powise='selected="selected"';
    }
	
	if($rel['po_type'] == 1){
		$services = 'selected="selected"';
	}else if($rel['po_type'] == 2){
		$job_work = 'selected="selected"';
	}else{
		$goods = 'selected="selected"';
	}
} else if(strpos($_SERVER[REQUEST_URI], "po_req")==true) {
//po_req_list
    $back="po_req_list";
    $mode="Add";$direct_add='1';$request=1;$viewmode="Add";
    $vender_id=$dbcon->real_escape_string($_REQUEST['id']);
    $branchId=$dbcon->real_escape_string($_REQUEST['branch_id']);
    $isDisabled = true;
    $isRequired = false;

/*$query="select * from tbl_purchaseorder where purchaseorder_id=$purchaseorder_id";
$rel=mysqli_fetch_assoc($dbcon->query($query)); 
$purchaseorder_date = date('d-m-Y',strtotime($rel['purchaseorder_date']));*/
$purchaseorder_date=date('d-m-Y');
$po_type_status='1';
//echo $purchaseorder_id;
$apstatus = '<span class="btn  btn-warning">Approval Pending</span>';
} else if(strpos($_SERVER[REQUEST_URI], "poemend")==true) {
    $back="po_list";
    $mode="Add";
    $viewmode="Revise";
    $direct_add='0';
    $request=0;
    $revise_status=true;
    $purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
    $query="select * from tbl_purchaseorder where purchaseorder_id=$purchaseorder_id";
    $rel=mysqli_fetch_assoc($dbcon->query($query)); 
    $purchaseorder_date = date('d-m-Y',strtotime($rel['purchaseorder_date']));
    $po_type_status=$rel['po_type_status'];
    $vender_id=$rel['vender_id'];
    $currency_id = $rel['currency_id'];
    $_SESSION['selected_vendor'] = $vender_id;
    $conversion_rate = $rel['conversion_rate'];
    $godown = $rel['godown_id'];   
    $isDisabled = true;
    $isRequired = false;
    $branchId=$rel['branch_id'];
    $start_purchaseorder_id=$rel['start_purchaseorder_id'];
    if($row['po_approval_status']=='1'){
        $apstatus = '<span class="btn  btn-success" >Approved</span>';
    }
    else{
        $apstatus = '<span class="btn  btn-warning">Approval Pending</span>';
    }
    $product_wise="";
    $powise="";
    if(strtolower($rel['delivery_type'])=="product_wise"){
        $product_wise='selected="selected"';
    }else{
        $powise='selected="selected"';
    }

} else {
    $back="po_list";
    $mode="Add";$direct_add='0';$request=0;
    $purchaseorder_date=date('d-m-Y');
    $po_type_status='';
    $vender_id = $_SESSION['selected_vendor'];
    $isDisabled = false;
    $isRequired = true;
    $viewmode="Add";

//$deleteid=delete_record('tbl_purchaseordertrn',"user_id=".$_SESSION['user_id']." and purchaseorder_id=0 and purchaseordertrn_status=0", $dbcon); 
    $apstatus = '<span class="btn  btn-warning">Approval Pending</span>';
}
$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));

$setconf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
$set_conf=mysqli_fetch_assoc($dbcon->query($setconf));
$type_conf = $set_conf['indent_po_pro_type'];
$pro_search = $set_conf['purchase_pro_search'];
$purchase_party_show = $set_conf['purchase_party_show'];
$po_terms_conditions = $set_conf['po_terms_conditions'];
//echo $purchaseorder_id;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('../include/include_css_file.php');?>
</head>
<body>
    <section id="container" class="sidebar-closed">
        <?php include_once('../include/include_top_menu.php');?>
        <?php include_once('../include/left_menu.php');?>
        <section id="main-content">
            <section class="wrapper">
                <?php//include_once('../include/equick_link.php');?>
                <div class="row">
                    <div class="col-lg-12">
                        <section class="panel">
                            <header class="panel-heading">
                                <h3><?=$mode.' '.$form?></h3>
                            </header>
                            <div class="">
                                <ul class="breadcrumb">
                                    <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="<?=ROOT.'po_list'?>"><?=$form?> List</a></li>
                                </ul>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <section class="panel">
                            <header class="panel-heading">
                                New <?=$form?>
                                <?php if($purchaseorder_id!=''){ ?>
                                    <span class="tools pull-right">
                                        <a href="<?=ROOT.'po'?>"><button class="btn btn-success btn-flat">Add Purchase Order</button></a>
                                    </span>
                                <?php } ?>
                            </header>

                            <div class="panel-body">
                                <form class="form-horizontal" role="form" id="purchaseorder_add" action="javascript:;" method="post" name="purchaseorder_add">
                                    <div class="row">
                                        <input type="hidden" name="inquiry_type" id="inquiry_type" value="1">
                                        <div class="col-md-12">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Vendor</label>
                                                    <div class="col-md-7">
                                                        <select class="select2" name="vender_id" id="vender_id" onChange="get_po_tax(this.value)" required title="Select Vender" $isDisabled>
                                                            <?=getcust($dbcon,$vender_id,$purchase_party_show);?> 
                                                        </select>
                                                    </div>
                                                    <button type="button" onClick="vendor_price_modal()" title="Vendor Price List" class="btn btn-primary btn-xs"><i class="fa fa-eye" aria-hidden="true"></i> </button>
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
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" ></label>
                                                    <div class="col-md-8 col-xs-11" id="consignee" style="<?= (empty($rel['consignee_id'])) ? "display:none;" : "" ?>">
                                                        <select class="select2" name="consignee_id" id="consignee_id">
                                                            <?=get_custmer_consignee($dbcon,$rel['vender_id'],$rel['consignee_id'])?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="col-md-4" style="display: none;">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Select Currency</label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <select class="select2" name="currency_id" id="currency_id"  required title="Select Currency">
                                                            <?=getcurrency($dbcon,$currency_id);?> 
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4" style="display: none;">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Conversion</label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input id="conversion_rate" name="conversion_rate" type="text" class="form-control" title="Conversion Rate" value="<?=$conversion_rate?>" placeholder="Conversion Rate" >
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Status *</label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <?=$apstatus?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4" style="display: none;">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Email ID </label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input id="vendor_email" name="vendor_email" type="text" class="form-control" title="Email ID" value="" placeholder="Email ID" readonly="readonly">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4" style="display: none;">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" >Mobile No. </label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input id="vendor_mobile" name="vendor_mobile" type="text" class="form-control" title="Mobile No." value="" placeholder="Mobile No." readonly="readonly">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" >Ref. </label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input id="vendor_reference" name="vendor_reference" type="text" class="form-control" title="Ref" value="<?=$rel['vendor_reference']?>" placeholder="Reference">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Purchase Order No </label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input id="purchaseorder_no" name="purchaseorder_no" type="text" class="form-control" title="Date" value="<?=$rel['purchaseorder_no']?>" placeholder="Purchase Order No" >
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Purchase Order Date </label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input id="purchaseorder_date" name="purchaseorder_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$purchaseorder_date?>" placeholder="Purchase Order Date">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Qtn No </label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input id="quotation_no" name="quotation_no" type="text" class="form-control" title="Date" value="<?=$rel['quotation_no']?>" placeholder="Quotation No" >
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" >Quotation Date </label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input id="quotation_date" name="quotation_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$rel['quotation_date']?>" placeholder="Qquotation_date Date">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4" style="display: none;">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Supply Type *</label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <select class="form-control" name="supply_type" id="supply_type" onChange="" required title="Select Sypply Type">
                                                            <option value=""> Select Type</option>
                                                            <option value="0" selected="selected">Intrastate</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4" style="display: none;">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">GST % *</label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <select class="form-control" name="gst_type" id="gst_type" onChange="" required title="Select GST">
                                                            <option value=""> Select GST</option>
                                                            <option value="po_wise" selected="selected">PO Wise</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Mode of Dispatch</label>
                                                    <div class="col-md-7">
                                                        <!--<input type="text" id="mode_of_dispatch" name="mode_of_dispatch" class="form-control" title="Mode of Dispatch" value="<?=$rel['mode_of_dispatch']?>" placeholder="Mode of Dispatch" >-->
                                                        <select style="padding-right: 0px;" class="form-control" name="dispatch_doc_no" id="dispatch_doc_no" >
                                                            <?=getmodeofdispache($dbcon,$rel['mode_of_dispatch']);?>
                                                        </select>
                                                    </div>
                                                    <input type="button" name="addproduct4" id="addproduct4" data-toggle="modal" data-target="#bs-dispatch-modal" class="btn btn-primary btn-xs" value="+" title="Add Mode of Dispatch" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" >Payment Terms</label>
                                                    <div class="col-md-7 col-xs-11">
                                                        <!--<input type="text" id="payment_terms" name="payment_terms" class="form-control" title="Payment Terms" value="<?=$rel['payment_terms']?>" placeholder="Payment Terms">-->
                                                        <select style="padding-right: 0px;" class="form-control" name="payment_terms" id="payment_terms" >
                                                            <?=getpaymentterms($dbcon,$rel['payment_terms']);?>
                                                        </select>
                                                    </div>
                                                    <input type="button" name="addproduct2" id="addproduct2" data-toggle="modal" data-target="#bs-payterms-modal-lg" class="btn btn-primary btn-xs" value="+" title="Add Payment Terms" />
                                                </div>
                                            </div>
                                            <div class="col-md-4 " >
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Delivery Type *</label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <select class="form-control" name="delivery_type" id="delivery_type" onChange="delivery_type_permission();" required title="Select Delivery Type">
                                                            <option value="po_wise" <?=$powise?> >PO Wise</option>
                                                            <option value="product_wise" <?=$product_wise?> >Product Wise</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 delivary_po_wise">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" > Delivery Date </label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input id="purchaseorder_due_date" name="purchaseorder_due_date" type="text" class="form-control default-date-picker" title="Date" value="<?php echo date("d-m-Y"); ?>" placeholder="Purchase Order Date">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Select Godown</label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <select class="select2" name="godown_id" id="godown_id"  required title="Select Godown">
                                                            <?=getgodown($dbcon,$godown);?> 
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <?php echo getBranchBox($dbcon, $branch_id, $branchId, $isDisabled, $isRequired); ?>
                                            </div>
											
											<div class="col-md-4" >
												<div class="form-group">
													<label class="col-md-4 control-label">PO Type *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="po_type" id="po_type" onChange="po_type_product_load(this.value);job_work_process()" required title="Select PO Type">
															<option value="0" <?=$goods?> >Goods</option>
															<option value="1" <?=$services?> >Services</option>
															<option value="2" <?=$job_work?> >Job Work</option>
														</select>
													</div>
												</div>
											</div>
                                        </div>
									</div>
                                    <!-- Tab Section Start By Umair -->
                                    <section class="panel" style="margin-top: 15px">
                                        <header class="panel-heading tab-bg-dark-navy-blue ">
                                            <ul class="nav nav-tabs">
                                                <li class="active">
                                                    <a data-toggle="tab" href="#po_items" aria-expanded="true">Items</a>
                                                </li>
                                                <li class="">
                                                    <a data-toggle="tab" href="#po_order" onClick="get_vendor_details('po_order')" aria-expanded="false" style="display:none">Purchase Order</a>
                                                </li>
                                                <li class="">
                                                    <a data-toggle="tab" href="#po_billing_terms" onClick="get_vendor_details('po_billing_terms')" aria-expanded="false" style="display:none">Billing Terms</a>
                                                </li>
                                                <li class="">
                                                    <a data-toggle="tab" href="#po_terms_cond" aria-expanded="false" style="display:none">Terms & Condition</a>
                                                </li>
                                                <li class="">
                                                    <a data-toggle="tab" href="#po_note" aria-expanded="false" style="display:none">Note</a>
                                                </li>
                                                <li class="">
                                                    <a data-toggle="tab" href="#po_header_footer" aria-expanded="false" style="display:none">Header/Footer</a>
                                                </li>
                                                <li class="">
                                                    <a data-toggle="tab" href="#po_vendor_details" onClick="get_vendor_details('po_vendor_details')" aria-expanded="false" style="display:none">Vendor Details</a>
                                                </li>
                                                <li class="">
                                                    <a data-toggle="tab" href="#po_history" onClick="get_vendor_details('po_history')" aria-expanded="false" style="display:none">PO History</a>
                                                </li>
                                                <li class="">
                                                    <a data-toggle="tab" href="#po_reports" aria-expanded="false" style="display:none">Reports</a>
                                                </li>
                                            </ul>
                                        </header>
                                        <div class="panel-body">
                                            <div class="tab-content">
                                                <div id="po_items" class="tab-pane active">
                                                    <!-- Add Data  -->   
                                                    <table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
                                                        <tr id="field" >
                                                            <!-- <th width="4%" class="text-center">Type</th>-->
                                                            <th width="25%" class="text-center">Product</th>
                                                            <th width="10%" class="text-center hidden" id="job_proc">Process</th>
                                                            <th width="7%" class="text-center">HSN Code</th>
                                                            <th width="7%" class="text-center">Quantity</th>
                                                            <!--<th width="7%" class="text-center">Sqr/Ft</th>-->
                                                            <th width="7%" class="text-center">Rate</th>
                                                            <!--<th width="7%" class="text-center">Per</th>-->
                                                            <th width="6%">Discount</th>
                                                            <th width="9%">Taxable Value</th>
                                                            <th width="15%">Tax</th>
                                                            <th width="9%" class="text-center">Amount</th>
                                                            <th width="5%" class="text-center"></th>
                                                        </tr>
                                                        <input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
                                                        <tr id="field1">
                                                            <!-- <td style="vertical-align:top;">
                                                            <select class="select2" name="product_type" id="product_type" onChange="load_product_po(this.value);" title="Select Product Type">
                                                            <?php //=getproducttype($dbcon,'');?>
                                                            </select>
                                                        </td>-->
                                                       <td style="vertical-align:top;max-width:310px">
                                                            <input id="product_id" name="product_id" style="width:100%;" placeholder="Select product" onChange="load_productdetail(this.value);job_work_process(this.value)" />
                                                            <br/><br/>
                                                            <textarea id="product_des" name="product_des" class="form-control" ></textarea><br>
														</td>
														<td class="vertical-align:top; hidden" id="job_proc1">
															 <select class="select2 hidden" name="process_id" id="process_id" title="Select Process">
																
															 </select>
														</td>
                                                        <td style="vertical-align:top;">
                                                            <input type="text" title="Enter HSN Code" id="product_hsn_code" name="product_hsn_code" class="form-control"/>
                                                            <br>
                                                            <button type="button" onClick="vendor_product_price_modal()" title="Vendor Product Price List" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> </button>
                                                        </td>
                                                        <td style="vertical-align:top;">
                                                            <input type="number"  title="Enter Qty" min="0" id="product_qty" name="product_qty"  class="form-control" onkeyup="product_convert_qty(2);"/>
                                                            <input type="hidden" name="unitid" id="unitid" value="" />
                                                            <input type="hidden" id="product_qty_hide" name="product_qty_hide" value="" />
                                                            <span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs" id="unit_show" >  </span>
                                                            <div id="convert_unit_block" style="display:none;" >
                                                                <input type="number"  title="Enter Qty" min="0" id="product_conv_qty" name="product_conv_qty"  class="form-control" onkeyup="product_convert_qty(1);" />
                                                                <input type="hidden" name="conv_unitid" id="conv_unitid" value="" />
                                                                <input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />
                                                                <span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs" id="convert_unit_show">  </span>
                                                            </div>
                                                        </td>
                                                        <td style="vertical-align:top;">
                                                            <input type="number"  title="Enter Rate" min="0" id="product_rate" name="product_rate" onkeyup="get_amount();" class="form-control checkPurchaseCard"/>
                                                        </td>
                                                        <td style="vertical-align:top;">
                                                            <input type="number" title="Enter Discount" min="0" id="product_discount" name="product_discount" onkeyup="get_discount('amt');" class="form-control" placeholder="in Rs."/><br/>
                                                            <input type="number"  title="Enter Discount Percentage" min="0" id="discount_per" name="discount_per" onkeyup="get_discount('per');" class="form-control" placeholder="in %" max="100"/>
                                                        </td>
                                                        <td style="vertical-align:top;">
                                                            <input type="number" title="Taxable Value" min="0" id="taxable_value" name="taxable_value" class="form-control" readonly />
                                                        </td>
                                                        <td style="vertical-align:top;">
                                                            <select class="form-control" name="formulaid" id="formulaid" onChange="get_amount();">
                                                                <?phpecho get_tax_formula($dbcon,$rel['formulaid'],' and tax_type=0'); //Dimple Panchal ?>
                                                            </select>
                                                            <!-- <input type="hidden" name="formulaid" id="formulaid" class="form-control" readonly /> -->
                                                            <input type="hidden" name="formula_tax_id" id="formula_tax_id" class="form-control" readonly /></br>
                                                            <input type="text" name="product_amount_tax" id="product_amount_tax" class="form-control" readonly />
                                                            <input type="hidden"  name="sel_tax" id="sel_tax" class="form-control" readonly />
                                                        </td>
                                                        <td style="vertical-align:top;"> 
                                                            <input type="number"  min="0" id="product_amount" readonly="readonly" name="product_amount" class="form-control" onmouseover="this.title=this.value"/>
                                                        </td>
                                                        <td width="5%">
                                                            <input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary delivary_po_wise" value="Add"/>

                                                            <input type="button"  name="addrow" id="addrow" onClick="open_approv_quo1()"  class="btn btn-primary delivary_product_wise" value="Add" />

                                                            <!--<button type="button" class="btn btn-xs btn-success" data-original-title="Alloca" data-toggle="tooltip" data-placement="top" onClick="open_approv_quo1()"><i class="fa fa-exclamation-triangle"></i></button>-->

                                                        </td>
                                                        <input type='hidden' name='edit_id' id='edit_id' value='' />
                                                    </tr>
                                                </table>
                                                <br>
                                                <!-- Display Added Data In Below Div -->
                                                <div id="sale_productdata"></div>
                                                <!-- Display The Total Amount -->
                                                <div class="col-md-12" style="margin-top:10px;">
                                                    <div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Terms Condition</label>
															<div class="col-md-9 col-xs-11">
																<textarea class="form-control" placeholder="Terms Condition" name="po_condition" id="po_condition" ><?php if(!empty($rel['po_condition'])) { echo $rel['po_condition']; } else { echo $po_terms_conditions; }?></textarea>
															</div>
														</div>
													</div>
                                                    <div class="col-md-6">
                                                        <!-- Below code is hide by Umair according to pathik this is the not useful (07102020) -->
                                                        <div class="form-group hide">
                                                            <label class="col-md-6 control-label">Total *</label>
                                                            <div class="col-md-4 col-xs-11">
                                                                <input id="total" name="total" type="text" readonly="readonly" class="form-control" title="dispatch_no" max="0"  value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;}?>" placeholder="total">
                                                            </div>
                                                        </div>
                                                        <div class="form-group hide">
                                                            <label class="col-md-6 control-label">Transport charges </label>
                                                            <div class="col-md-4 col-xs-11">
                                                                <input id="paking" name="paking" type="number"  min="0"  class="form-control" title="Transport" value="<?phpif($mode=="Add"){echo 0;}else if($mode="Edit"){echo $rel['packing'];}?>" onKeyUp="get_amount();" placeholder="Transport">
                                                            </div>
                                                        </div>
                                                        <div class="form-group hide">
                                                            <label class="col-md-6 control-label">Round Off</label>
                                                            <div class="col-md-4 col-xs-11">
                                                                <input id="round_off" name="round_off" type="number" class="form-control" title="Round Off" value="<?phpif($mode=="Add"){echo 0;}else if($mode="Edit"){echo $rel['round_off'];}?>" onKeyUp="get_amount();" placeholder="Round Off">
                                                            </div>
                                                        </div>
                                                        <!-- Dimple Panchal : start -->
                                                        <?php //$tcs_applicable = $dbcon->query("SELECT tcs_applicable FROM tbl_finance_setting as comp WHERE company_id=".$_SESSION['company_id'])
                                                        //  ->fetch_object()->tcs_applicable; 
                                                        if($tcs_applicable) {?>
                                                            <div class="form-group">
                                                                <label class="col-md-6 control-label">Select Formula</label>
                                                                <div class="col-md-4 col-xs-11">
                                                                    <select class="form-control" name="formula_id" id="formula_id" onChange="get_gtotal();">
                                                                        <?php echo get_tax_formula($dbcon,$rel['formulaid'],' and tax_type=1'); ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="col-md-6 control-label">Tax</label>
                                                                <div class="col-md-4 col-xs-11">
                                                                    <input type='text' class="form-control" name='tcs_total' id='tcs_total' value='0' />
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                        <!-- Dimple Panchal : end -->
                                                        <!-- Above code is hide by Umair according to pathik this is the not useful (07102020) -->
                                                        <div class="form-group">
                                                            <label class="col-md-6 control-label">Grand Total (<?=$_SESSION['currency_name']?>) *</label>
                                                            <div class="col-md-4 col-xs-11">
                                                                <input id="g_total" name="g_total" type="text"  class="form-control" title="total" value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $rel['g_total'];}?>" placeholder="total"readonly="readonly">
                                                                <!--<input id="total" name="total" type="hidden" value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;} ?>" placeholder="total"readonly="readonly">-->
                                                            </div>
                                                        </div>
                                                        <div class="form-group currency_total_div" style="display: none">
                                                            <label class="col-md-6 control-label">Grand Total (<span class="currency_type_name"></span>)*</label>
                                                            <div class="col-md-4 col-xs-11">
                                                                <input id="currency_total" name="currency_total" type="text"  class="form-control" title="total" value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $rel['currency_total'];}?>" placeholder="total"readonly="readonly">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="po_order" class="tab-pane"></div>
                                            <div id="po_billing_terms" class="tab-pane"></div>
                                            <div id="po_terms_cond" class="tab-pane">
                                                <div class="row">
                                                    
                                                </div>
                                            </div>
                                            <div id="po_note" class="tab-pane">
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Remarks </label>
                                                    <div class="col-md-9 col-xs-11">
                                                        <textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="po_header_footer" class="tab-pane">Header/Footer Details</div>
                                            <div id="po_vendor_details" class="tab-pane"></div>
                                            <div id="po_history" class="tab-pane"></div>
                                            <div id="po_reports" class="tab-pane">Reports Content</div>
                                        </div>
                                    </div>
                                </section>
								
                                <!-- Tab Section -->
                                <div class="row">
                                    <button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
                                    <a href="<?=ROOT.'po_list'?>" type="button" class="btn btn-danger">Cancel</a>
                                    <div class="col-md-3"></div>
                                </div>
                                <!--Vendor row end-->   
                                <input type='hidden' name='po_request' id='po_request' value='<?=$request?>' />
                                <input type='hidden' name='viewmode' id='viewmode' value='<?=$viewmode?>' />
                                <input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
                                <input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
                                <input type='hidden' name='eid' id='eid' value='<?=$purchaseorder_id;?>' />   
                                <input type='hidden' name='start_purchaseorder_id' id='start_purchaseorder_id' value='<?=$start_purchaseorder_id;?>' />   
                                <input type='hidden' name='prev_purchaseorder_id' id='prev_purchaseorder_id' value='<?=$purchaseorder_id;?>' />   
                                <input type='hidden' name='revise_status' id='revise_status' value='<?=$revise_status;?>' />   
                                <input type='hidden' name='back' id='back' value='<?=$back;?>' /> 
                                <?php 
                                if($direct_add=='1'){
                                    ?>    
                                    <input type="hidden" name="po_ref_id" id="po_ref_id" value="<?=$rel['purchaseorder_id']?>" />
                                <?php} ?>  
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
    <?php //include_once('../include/add_vender.php');?>
    <?php include_once('../include/add_product.php');?>
    <?php include_once('../include/add_city.php');?>
    <?php include_once('../include/add_state.php');?>
    <?php include_once('../include/add_payterms.php');?>
    <?php include_once('../include/add_placesupally.php');?>
    <?php include_once('../include/add_modedispatch.php');?>
    <?php include_once('../include/add_po_dispach_date.php');?>
    <?php include_once('../include/vendor_price_list.php');?>
    <?php include_once('../include/vendor_product_price_list.php');?>
    <?php include_once('../include/footer.php');?>

    <!--footer end-->
</section>
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/po.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/payment_terms.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/mode_disptch.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/product_mst.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/state_mst.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/city_mst.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/customer.js?<?=time()?>"></script>

<script>

    $(".selproduct").select2({
        width: '100%',
        minimumInputLength: 2
    });	

    $(".select2").select2({
        width: '100%'
    });


    CKEDITOR.replace( 'po_condition', {
        enterMode: CKEDITOR.ENTER_BR
    });

/*$("#product_id").select2({
width: '86%'
});*/

$('.default-date-picker').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true
});

$(".form_datetime").datetimepicker({
    format: 'dd-mm-yyyy hh:ii',
    autoclose: true,
    todayBtn: true,
    pickerPosition: "bottom-left"

});
function add_customer_purchase()
{
    $("#bs-example-modal-lg").modal("show");
    $("#cat_id").val('1');
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

/* $(document).on('keyup', '.checkPurchaseCard', function(){
var type = $(this).data('type');
if(type=='1'){
var new_price = parseFloat($(this).val());
var discount = parseFloat($(this).data('discount'));
var tolerance = parseFloat($(this).data('tolerance'));

if(new_price >= tolerance || new_price <= discount){

$msg = "Please update your purchase card.";
toastr.warning($msg, "WARNING");
$(this).focus();
}
}

});*/
<?phpif($mode=="Add" && $viewmode=="Revise"){ ?>
copy_prev_purchase_trn(<?=$purchaseorder_id?>);
get_revise_po_no(<?=$purchaseorder_id?>,<?=$start_purchaseorder_id?>);
<?php} ?>
</script>
<?php 
//echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
//echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
if($mode=="Add" && $viewmode=="Add"){
//  echo "<script>show_data();</script>";
    echo "<script>get_series_no(6);</script>";
}
if($direct_add=='1'){
/*echo "<script>entry_po_req_data(".$rel['purchaseorder_id'].");</script>";
echo "<script>
$('#po_type_status').attr('style','pointer-events: none;').attr('readonly','readonly');
</script>";*/
}
?>
</body>
</html>
