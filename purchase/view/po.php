<?php 
session_start();
//var_dump($_SESSION);
include('../include/urlfile.php');	
// error_reporting(E_ALL);
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
$quot_type=0;
$currency_id=$_SESSION['currency_id'];
$conversion_rate = $_SESSION['currency_rate'];
$vendor_reference='';$quotation_no='';$quotation_date=date('d-m-Y');
$godown="";
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    PO_LIST_UPDATE
]);
$disable = '';
$branch_id = $_SESSION['branch_id'];
$purchaseorder_id='';
if(strpos($_SERVER['REQUEST_URI'], "poedit")==true)
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
    $po_valid_date = date('d-m-Y',strtotime($rel['po_valid_date']));
    $quotation_date = date('d-m-Y',strtotime($rel['quotation_date']));
    $po_type_status=$rel['po_type_status'];
    $vender_id=$rel['vender_id'];
    $currency_id = $rel['currency_id'];
    $_SESSION['selected_vendor'] = $vender_id;
    $conversion_rate = $rel['conversion_rate'];
    $godown = $rel['godown_id']; 
    $quot_type = $rel['quot_type'];  
    $isDisabled = true;
    $isRequired = false;
    $branchId=$rel['branch_id'];
    if($row['po_approval_status']=='1'){
        $apstatus = '<span class="btn btn-success" >Approved</span>';
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
    
    $purchaseorder_due_date='';
    if($rel['purchaseorder_due_date']!="1970-01-01" && $rel['purchaseorder_due_date']!="0000-00-00" && $rel['purchaseorder_due_date'] != NULL)
    {
        $purchaseorder_due_date=date('d-m-Y',strtotime($rel['purchaseorder_due_date']));
    }

    if($rel['po_type'] == 1){
      $services = 'selected="selected"';
  }else if($rel['po_type'] == 2){
      $job_work = 'selected="selected"';
  }else{
      $goods = 'selected="selected"';
  }
  $disable = 'disabled';
} else if(strpos($_SERVER['REQUEST_URI'], "po_req")==true) {
//po_req_list
    $back="po_req_list";
    $mode="Add";$direct_add='1';$request=1;$viewmode="Add";
    $vender_id=$dbcon->real_escape_string($_REQUEST['id']);
    $branchId=$dbcon->real_escape_string($_REQUEST['branch_id']);
    $isDisabled = true;
    $isRequired = false;

    /*$query="select * from tbl_purchaseorder where purchaseorder_id=$purchaseorder_id";
    $rel=mysqli_fetch_assoc($dbcon->query($query)); */

    $pay_terms="select pay_terms from tbl_ledger where l_id=$vender_id";
    $rel1=mysqli_fetch_assoc($dbcon->query($pay_terms));
    $rel['payment_terms'] = $rel1['pay_terms'];
    $quot_type = $rel1['quot_type'];

    //$purchaseorder_date = date('d-m-Y',strtotime($rel['purchaseorder_date']));
    $purchaseorder_date=date('d-m-Y');
    $po_valid_date=date('d-m-Y');
    
    $purchaseorder_due_date=date('d-m-Y');

  /*  if($rel['purchaseorder_due_date']!="1970-01-01" && $rel['purchaseorder_due_date']!="0000-00-00" && $rel['purchaseorder_due_date'] != NULL)*/

   /* if($rel['purchaseorder_due_date']!="1970-01-01" && $rel['purchaseorder_due_date']!="0000-00-00")

    {
        $purchaseorder_due_date=date('d-m-Y',strtotime($rel['purchaseorder_due_date']));
    }*/

    $po_type_status='1';
    $cust_stateid = get_gst_statecode($dbcon,$vender_id);
    $cust = explode(",",$cust_stateid);
    $cust_state = $cust['1'];
//echo $purchaseorder_id;
    $apstatus = '<span class="btn  btn-warning">Approval Pending</span>';
} else if(strpos($_SERVER['REQUEST_URI'], "poemend")==true) {
    $back="po_list";
    $mode="Add";
    $viewmode="Revise";
    $direct_add='0';
    $request=0;
    $revise_status=true;
    $purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
    $query="select * from tbl_purchaseorder where purchaseorder_id=$purchaseorder_id";
    $rel=mysqli_fetch_assoc($dbcon->query($query)); 
    $purchaseorder_date = date('d-m-Y');
    $po_valid_date = date('d-m-Y',strtotime($rel['po_valid_date']));
    $quotation_date = date('d-m-Y',strtotime($rel['quotation_date']));

    $purchaseorder_due_date=date('d-m-Y');
    if($rel['purchaseorder_due_date']!="1970-01-01" && $rel['purchaseorder_due_date']!="0000-00-00" && $rel['purchaseorder_due_date'] != NULL)
    {
        $purchaseorder_due_date=date('d-m-Y',strtotime($rel['purchaseorder_due_date']));
    }

    $po_type_status=$rel['po_type_status'];
    $vender_id=$rel['vender_id'];

    $currency_id = $rel['currency_id'];
    //$_SESSION['selected_vendor'] = $vender_id;
    $conversion_rate = $rel['conversion_rate'];
    $quot_type = $rel['quot_type'];
    $godown = $rel['godown_id'];   
    $isDisabled = true;
    $isRequired = false;
    $branchId=$rel['branch_id'];
    $start_purchaseorder_id=$rel['start_purchaseorder_id'];
    if($row['po_approval_status']=='1'){
        $apstatus = '<span class="btn btn-success" >Approved</span>';
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
    $po_valid_date=date('d-m-Y');
    $po_type_status='';
    //$vender_id = $_SESSION['selected_vendor'];
    $isDisabled = false;
    $isRequired = true;
    $viewmode="Add";
    $purchaseorder_due_date=date('d-m-Y');
    
//$deleteid=delete_record('tbl_purchaseordertrn',"user_id=".$_SESSION['user_id']." and purchaseorder_id=0 and purchaseordertrn_status=0", $dbcon); 
    $apstatus = '<span class="btn  btn-warning">Approval Pending</span>';
}
$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));

if($mode =='Add'){
    $rel['con_address'] = $set_head['address'];
}

$setconf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
$set_conf=mysqli_fetch_assoc($dbcon->query($setconf));
$type_conf = $set_conf['indent_po_pro_type'];
$pro_search = $set_conf['purchase_pro_search'];
$purchase_party_show = $set_conf['purchase_party_show'];
$po_terms_conditions = $set_conf['po_terms_conditions'];
$financial_year=get_financial_year_new($dbcon);
$getspecialConfiguration=getspecialConfiguration($dbcon);
//echo $purchaseorder_id;


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PO</title>
    <?php include_once($include.'/include_css_file.php');?>
    <style>
       .row_margin
       {
          margin-top:10px !important;
      }
      .currency_icon{
          color: green;
          font-size: 12px;
          font-weight: bold;
      }
  </style>
</head>
<body>
    <section id="container" class="sidebar-closed">
        <?php include_once($include.'/include_top_menu.php');?>
        <?php include_once($include.'/left_menu.php');?>
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
                                    <li><a href="<?=ROOT.PURCHASE_ROOT.'po_list'?>"><?=$form?> List</a></li>
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
                                        <a href="<?=ROOT.PURCHASE_ROOT.'po'?>"><button class="btn btn-success btn-flat">Add Purchase Order</button></a>
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
                                                   <input type="hidden" name="cust_stateid" id="cust_stateid" value="<?=$cust_state?>">
                                                   <label class="col-md-4 control-label">Vendor</label>
                                                   <div class="col-md-7">
                                                    <select class="select2" name="vender_id" id="vender_id" onChange="get_po_tax(this.value);get_statecode(this.value);get_grossbalance(this.value);get_invoice_total_tax();get_gtotal();get_ledger_details(this.value);load_transportation_vendor_wise();load_paymentterms_vendor_wise();" required title="Select Vender">
                                                        <?=getcust($dbcon,$vender_id,$purchase_party_show);?> 
                                                    </select>
                                                    <strong style="display:none;color:green" id="gross">Gross balance : <span class="gross"></span></strong> <br><strong id="statecode" style="display:none;color:blue">GST StateCode : <span class="statecode"></span></strong><strong id="sez_enable_text" style="display:none;color:red">This Party Is SEZ Enabled</strong> 
                                                </div>

                                                <button type="button" onClick="vendor_price_modal()" title="Vendor Price List" class="btn btn-primary btn-xs"><i class="fa fa-eye" aria-hidden="true"></i> </button>
                                                
                                            </div>
                                        </div>
                                        <div class="col-md-1">   
                                            <button accesskey="n" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" value="R1" onclick="showledger();" title="Short-Cut To Open PopUp, Shift + Alt + n" ><i class="fa fa-plus"></i> Add Vendor</button>
                                            <!-- <a href="#"  data-original-title="Short-Cut To Open PopUp, Shift + Alt + n " data-toggle="tooltip" data-placement="top" ><i class="fa fa-info-circle fa-sm" style="color: black;"></a></i> -->
                                        </div>
                                        <div class="col-md-3" >
                                            <div class="form-group">
                                                <?php 
                                                $ck='';
                                                if($mode=="Add" && $viewmode=='Add'){
                                                    $ck='checked="checked"';
                                                }else{
                                                    if($rel['cons_same_as']==1){
                                                        $ck='checked="checked"';
                                                    }
                                                }
                                                ?>
                                                <label class="col-md-8 control-label" >
                                                    <input id="same_as" name="same_as" type="checkbox" class="" title="Other Name"  <?=$ck?> value="1" style="height:20px;" onChange="consinee_change();"> Same as Shipping Address
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4" id="consignee" style="<?= (empty($rel['consignee_id'])) ? "display:none;" : "" ?>">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label"> Shipping Branch</label>
                                                <div class="col-md-8 col-xs-11" >
                                                    <select class="select2" name="consignee_id" id="consignee_id">
                                                        <?=get_branch($dbcon,$rel['consignee_id'])?>
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
                                        <div class="col-md-4" style="display:none">
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
                                                <label class="col-md-4 control-label">Series * </label>
                                                <div class="col-md-8 col-xs-11">
                                                   <select <?= $disable ?> class="select2" name="invoicetype_id" id="invoicetype_id" onchange="load_pono(this.value)" required>
                                                        <option value="">--Select Series--</option>
                                                        <?=get_invoice_type_list($dbcon,6,$rel['invoicetype_id'])?>
                                                   </select>
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
                                                    <input id="quotation_date" name="quotation_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$quotation_date?>" placeholder="Quotation Date">
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
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Transportation Detail</label>
                                                <div class="col-md-7">
                                                    <!--<input type="text" id="mode_of_dispatch" name="mode_of_dispatch" class="form-control" title="Mode of Dispatch" value="<?=$rel['mode_of_dispatch']?>" placeholder="Mode of Dispatch" >-->
                                                    <select style="padding-right: 0px;" class="form-control" name="dispatch_doc_no" id="dispatch_doc_no" >
                                                        <?=get_trasports($dbcon,$rel['mode_of_dispatch']);?>
                                                    </select>
                                                </div>
                                                <input type="hidden" id="trnsp_id"  value="<?=$rel['mode_of_dispatch']?>">
                                                <input type="button" name="addproduct4" id="addproduct4" data-toggle="modal" data-target="#bs-dispatch-modal" class="btn btn-primary btn-xs hide" value="+" title="Add Mode of Dispatch" />
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
                                                    <input id="purchaseorder_due_date" name="purchaseorder_due_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$purchaseorder_due_date?>" placeholder="Purchase Order Date">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4" style="display: none;">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Select Godown</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <select class="select2" name="godown_id" id="godown_id"  required title="Select Godown">
                                                        <?=getgodown($dbcon,$godown);?> 
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if($set_conf['branch_wise_manage']==1){?>
                                            <div class="col-md-4">
                                                <?php echo getBranchBox($dbcon, $branch_id, $branchId, $isDisabled, $isRequired); ?>
                                            </div>
                                        <?php} ?>
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

                                        <div class="col-md-4" >
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">PO Valid Till Date *</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input id="po_valid_date" name="po_valid_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$po_valid_date?>" placeholder="PO Valid Till Date">
                                                </div>
                                            </div>
                                        </div>
                                         <div class="col-md-4" >
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Kind Attn.</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <select class="form-control" name="kind_attn" id="kind_attn" title="Select Kind Attn.">
                                                        <option value='' >select Kind Attn.</option>
                                                    </select>
                                                    <input type="hidden" name="kind_attn_hidden" id="kind_attn_hidden" value="<?=$rel['kind_attn']?>"/>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Sales Type</label>
                                                    <div class="col-md-8 col-xs-11">
                                                    
                                                        <select class="form-control" name="sales_type" id="sales_type">
                                                            <option value="1" <?php if($rel['sales_type']==1){ echo "selected"; } else{ echo ""; } ?>>Item Wise Tax</option>
                                                            <option value="2" <?php if($rel['sales_type']==2){ echo "selected"; } else{ echo ""; } ?> >Merchant</option>
                                                            <option value="5" <?php if($rel['sales_type']==5){ echo "selected"; } else{ echo ""; } ?> >Import</option>
                                                            <option value="3" <?php if($rel['sales_type']==3){ echo "selected"; } else{ echo ""; } ?> >GST 0%</option>
                                                            <option value="4" <?php if($rel['sales_type']==4){ echo "selected"; } else{ echo ""; } ?> >GST 5%</option>
															<option value="6" <?php if($rel['sales_type']==6){ echo "selected"; } else{ echo ""; } ?> >GST 12%</option>
															<option value="7" <?php if($rel['sales_type']==7){ echo "selected"; } else{ echo ""; } ?> >GST 18%</option>
															<option value="8" <?php if($rel['sales_type']==8){ echo "selected"; } else{ echo ""; } ?> >GST 24%</option>
                                                        </select>
                                                    </div>
                                                 </div>
                                            </div>
                                        <?phpif($getspecialConfiguration['oilfield_permission']==1){ ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" >Terms</label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input id="terms" name="terms" type="text" class="form-control" title="Date" value="<?php echo $rel['terms']; ?>" placeholder="Terms">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" >Shipped via</label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input id="shipped_via" name="shipped_via" type="text" class="form-control" title="Date" value="<?php echo $rel['shipped_via']; ?>" placeholder="Shipped via">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" >FOB</label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input id="fob" name="fob" type="text" class="form-control" title="Date" value="<?php echo $rel['fob']; ?>" placeholder="FOB">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php} ?>
                                    </div>
                                    <div class="col-md-12">
                                     <div class="col-md-4" style="display:none;">
                                        <div class="form-group">
                                           <label class="col-md-4 control-label">Currency  *</label>
                                           <div class="col-md-8 col-xs-11">
                                              <input id="currency_enable" name="currency_enable" type="checkbox" class="" title="" value="1" style="width:20%;height:25px;" onChange="currency_change();" <?php if($mode=='Edit' && $rel['currency_enable']==1){ echo "checked";  }  ?>>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="col-md-4 currency_div" style="display:block;">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Currency *</label>
                                       <div class="col-md-8 col-xs-12">
                                          <select class="select2" name="currency_id" id="currency_id" onChange="get_symbol();currency_rate_c();">
                                             <?=getcurrency($dbcon,$currency_id);?>
                                         </select>

                                     </div>
                                 </div>
                             </div>
                             <div class="col-md-4 currency_div" style="display:block;">
                                <div class="form-group">
                                   <label class="col-md-4 control-label">Rate *</label>
                                   <div class="col-md-6 col-xs-11">
                                      <input id="currency_rate" name="currency_rate" type="text" class="form-control valid numbersOnly" title="" value="<?=$rel['currency_rate']?>" placeholder="">
                                  </div>
                              </div>
                          </div>
                             <div class="col-md-4 currency_div" style="display:block;">
                                <div class="form-group">
                                   <label class="col-md-4 control-label">Subject</label>
                                   <div class="col-md-6 col-xs-11">
                                      <input id="po_sub" name="po_sub" type="text" class="form-control valid" title="" value="<?=$rel['po_sub']?>" placeholder="">
                                  </div>
                              </div>
                          </div>

                          <div class="col-md-4">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Purchase Order Type</label>
                                <div class="col-md-8"> 
                                    <label class="col-md-6" style="font-weight:bold;"><input type="radio" id="quot_type_domestic" name="quot_type" onclick="load_typeswise_terms(this.value,<?=$purchaseorder_id?>);" value="0" <?=($quot_type!='1')?'checked':''?>> Domestic</label>
                                    <label class="col-md-5 " style="font-weight:bold;"><input type="radio" id="quot_type_export" name="quot_type" onclick="load_typeswise_terms(this.value,<?=$purchaseorder_id?>);" value="1" <?=($quot_type=='1')?'checked':''?>> Import</label>
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
                                <a data-toggle="tab" href="#des" onClick="get_vendor_details('des')" aria-expanded="false">Product Description</a>
                            </li>
                            <li class="">
                                <a data-toggle="tab" href="#po_order" onClick="get_vendor_details('po_order')" aria-expanded="false">Purchase Order</a>
                            </li>
                            <li class="" style="display:none">
                                <a data-toggle="tab" href="#po_billing_terms" onClick="get_vendor_details('po_billing_terms')" aria-expanded="false">Billing Terms</a>
                            </li>
                            <li class="" style="display:none">
                                <a data-toggle="tab" href="#po_terms_cond" aria-expanded="false">Terms & Condition</a>
                            </li>
                            <li class="">
                                <a data-toggle="tab" href="#po_note" aria-expanded="false">Note</a>
                            </li>
                            <li class="" style="display:none">
                                <a data-toggle="tab" href="#po_header_footer" aria-expanded="false">Header/Footer</a>
                            </li>
                            <li class="">
                                <a data-toggle="tab" href="#po_vendor_details" onClick="get_vendor_details('po_vendor_details')" aria-expanded="false">Vendor Details</a>
                            </li>
                            <li class="">
                                <a data-toggle="tab" href="#po_history" onClick="get_vendor_details('po_history')" aria-expanded="false">PO History</a>
                            </li>
                            <li class="">
                                <a data-toggle="tab" href="#po_reports" aria-expanded="false">Reports</a>
                            </li>
                        </ul>
                    </header>
                    <div class="panel-body">
                        <div class="tab-content">
                            <div id="po_items" class="tab-pane active">
                                <!-- Add Data  -->   
                                <table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
                                    <tr id="field" >
                                         <?phpif($getspecialConfiguration['invoite_permission'] !=1 && $getspecialConfiguration['smpl_permission'] != '1'){ ?>
                                        <th width="4%" class="text-center"></th>
                                    <?php} ?>
                                        <?phpif($getspecialConfiguration['aeon_permission'] ==1){ ?>
                                        <th width="10%" class="text-center">Category</th>
                                        <?php }?>
                                        <th width="20%" class="text-center">Product</th>
                                        <th width="10%" class="text-center hidden" id="job_proc">Process</th>
                                        <th width="7%" class="text-center" style="display:none">HSN Code</th>
                                        <th width="7%" class="text-center">Per</th>
                                        <th width="7%" class="text-center">Quantity</th>
                                        <!--<th width="7%" class="text-center">Sqr/Ft</th>-->
                                        <th width="7%" class="text-center">Rate <span class="currency_icon"></span></th>
                                        <!--<th width="7%" class="text-center">Per</th>-->
                                        <th width="6%">Discount <span class="currency_icon"></span></th>
                                        <th width="9%" style="display:none">Taxable Value <span class="currency_icon"></span></th>
                                        <th width="15%" style="display:none">Tax</th>
                                        <th width="9%" class="text-center">Amount <span class="currency_icon"></span></th>
                                        <th width="5%" class="text-center"></th>
                                    </tr>
                                    <input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
                                    <tr id="field1">
                                         <?phpif($getspecialConfiguration['invoite_permission'] !=1 && $getspecialConfiguration['smpl_permission'] != '1'){ ?>
                                        <td>
                                            <button accesskey="p" style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" value="R1" onclick="showproduct();" title="Short-Cut To Open PopUp, Shift + Alt + p "><i class="fa fa-plus"></i> Add Product</button>
                                        </td>
                                    <?php} ?>
                                                        <!-- <td style="vertical-align:top;">
                                                            <select class="select2" name="product_type" id="product_type" onChange="load_product_po(this.value);" title="Select Product Type">
                                                            <//=getproducttype($dbcon,'');?>
                                                            </select>
                                                        </td>-->
                                                        <?phpif($getspecialConfiguration['aeon_permission'] ==1){ ?>
                                                            <td>
                                                                <select class="select2" name="cat_id" id="cat_id" title="Select Category" onchange="/*load_product_category_wise(this.value)*/">
                                                                    <?=get_all_category($dbcon,0);?>
                                                                </select>
                                                            </td>
                                                        <?php} ?>
                                                        <td style="vertical-align:top;max-width:300px">
                                                            <input id="product_id" name="product_id" style="width:100%;" placeholder="Select product" class="form-control" onChange="get_hsn(this.value);load_productdetail(this.value);job_work_process(this.value);" /><br>
                                                            <strong class="hsncode" style="display:none;color:blue">HSN Code : <span id="hsncode"></span></strong>
                                                            <br/><br/>
                                                            <textarea id="product_des" name="product_des" class="form-control" style="display:none" ></textarea><br>
                                                        </td>
                                                        <td class="vertical-align:top; hidden" id="job_proc1">
                                                            <select class="select2 hidden" name="process_id" id="process_id" title="Select Process">

                                                            </select>
                                                        </td>
                                                        <td style="vertical-align:top;display:none">
                                                            <input type="text" title="Enter HSN Code" id="product_hsn_code" name="product_hsn_code" class="form-control"/>
                                                        </td>
                                                        <td>
                                                            <select class="form-control"  title="Select Unit" placeholder="Unit" name="rate_unit_id" id="rate_unit_id" onchange="load_product_unit();get_product_price();">
                                                                <!-- <//=getunit($dbcon,0);?> -->
                                                                <option value="0">Select Unit</option>
                                                            </select><br>
                                                            <input type="hidden" name="p_qty" id="p_qty">
                                                        </td>
                                                        <td style="vertical-align:top;">
                                                         <div id="convert_unit_block" style="display:none;" >
                                                            <input type="text"  title="Enter Qty" min="0" id="product_conv_qty" name="product_conv_qty"  class="form-control numbersOnly" onkeyup="product_convert_qty(1);" onChange="get_discount('per');" />
                                                            <input type="hidden" name="conv_unitid" id="conv_unitid" value="" />
                                                            <input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />
                                                            <span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs" id="convert_unit_show">  </span>
                                                        </div>
                                                        <div id="base_unit_block">
                                                            <input type="text"  title="Enter Qty" min="0" id="product_qty" name="product_qty"  class="form-control numbersOnly" onkeyup="product_convert_qty(2);" onchange="get_discount('per');" />
                                                            <span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs" id="unit_show" >  </span>
                                                            <input type="hidden" name="unitid" id="unitid" value="" />
                                                            <input type="hidden" id="product_qty_hide" name="product_qty_hide" value="" />
                                                        </div>
                                                    </td>
                                                    <td style="vertical-align:top;">
                                                        <input type="text"  title="Enter Rate" min="0" id="product_rate" name="product_rate" onkeyup="get_amount();" onchange="get_discount('per');"  data-pcard="0" data-pcardid="0" class="form-control checkPurchaseCard numbersOnly"/>
                                                        <br>
                                                        <button type="button" onClick="vendor_product_price_modal()" title="Vendor Product Price List" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> </button>
                                                    </td>
                                                    <td style="vertical-align:top;">
                                                        <input type="number" title="Enter Discount" min="0" id="product_discount" name="product_discount"  onkeyup="get_discount('amt');" class="form-control numbersOnly" placeholder="in Rs."/><br/>
                                                        <input type="number"  title="Enter Discount Percentage" min="0" id="discount_per" name="discount_per" onkeyup="get_discount('per');" class="form-control numbersOnly" placeholder="in %" max="100"/>
                                                    </td>
                                                    <td style="vertical-align:top;display:none">
                                                        <input type="number" title="Taxable Value" min="0" id="taxable_value" name="taxable_value" class="form-control" readonly />
                                                    </td>
                                                    <td style="vertical-align:top;display:none">
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

                                                        <input type="button"  name="addrow" id="addrow" onClick="open_approv_quo1();load_unit_product();delivery_schedule()"  class="btn btn-primary delivary_product_wise" value="Add" />

                                                        <!--<button type="button" class="btn btn-xs btn-success" data-original-title="Alloca" data-toggle="tooltip" data-placement="top" onClick="open_approv_quo1()"><i class="fa fa-exclamation-triangle"></i></button>-->

                                                    </td>
                                                    <input type='hidden' name='edit_id' id='edit_id' value='' />
                                                    <input type='hidden' name='pro_cal_type' id='pro_cal_type' value='' />
                                                </tr>
                                            </table>
                                            <br>
                                            <!-- Display Added Data In Below Div -->
                                            <div id="sale_productdata"></div>
                                            <!-- Display The Total Amount -->
                                            <div class="col-md-12" style="margin-top:10px;">

                                               <div class="col-md-6">
                                                    <div class="card">
                                                        <ul class="nav nav-tabs" id="my_tab_id" role="tablist">
                                                            <li role="presentation" id="tab2" class="active"><a href="#remark-section" aria-controls="remark-section" role="tab" data-toggle="tab">Remark</a></li>
                                                            <li role="presentation" id="tab1"><a href="#terms-section" aria-controls="terms-section" role="tab" data-toggle="tab">Terms & Condition</a></li>
                                                            <li role="presentation" id="tab3"><a href="#podoc-section" aria-controls="terms-section" role="tab" data-toggle="tab">PO Document</a></li>
                                                        </ul>
                                                        <div class="tab-content"> 
                                                            <div role="tabpanel" class="tab-pane active" id="remark-section">
                                                                <div class="form-group" style="margin-top:20px;">
                                                                    <div class="col-md-12 tax_details">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div role="tabpanel" class="tab-pane" id="terms-section">
                                                                <div class="form-group" style="margin-top:20px;">
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
                                                                             <label class="col-md-2 control-label">Terms Condition</label>
                                                                             <div class="col-md-10 col-xs-11">
                                                                                <textarea class="form-control" placeholder="Terms Condition" name="po_condition" id="po_condition" ><?php if(!empty($rel['po_condition'])) { echo $rel['po_condition']; } else { echo $po_terms_conditions; }?></textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                   
                                                                        <div class="col-md-12" style="margin-top:12px" id="format_2">
                                                                            <div class="col-md-3">
                                                                                <div class="form-group">
                                                                                    <input type="radio" class="" name="terms_type" id="common_terms" value="0" onchange="load_typeswise_terms();" 
                                                                                    <?phpif($rel['terms_type'] == '0'){ echo 'checked="checked"';}else{ if($mode == 'Add'){echo 'checked="checked"';} }?> > Common Terms 
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-3">
                                                                                <div class="form-group">
                                                                                    <input type="radio" class="" name="terms_type" id="party_terms" value="1" onchange="load_typeswise_terms();"
                                                                                    <?phpif($rel['terms_type'] == '1'){ echo 'checked="checked"';}?> > Party Wise
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-3">
                                                                                <div class="form-group">
                                                                                    <input type="radio" class="" name="terms_type" id="multi_condition" value="2" onchange="load_typeswise_terms();"
                                                                                    <?phpif($rel['terms_type'] == '2'){ echo 'checked="checked"';}?> > Multi Condition
                                                                                </div>
                                                                            </div>   

                                                                            <div class="form-group" id="po_terms_cond_div">

                                                                            </div> 
                                                                        </div>
                                                                </div>
                                                            </div>
                                                            <div role="tabpanel" class="tab-pane" id="podoc-section">
                                                                <div class="form-group" style="margin-top:20px;">
                                                                    <table class="display table table-bordered table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th width="40%" class="text-center">Document Name</th>
                                                                                <th width="50%" class="text-center">Upload Image</th>
                                                                                <th width="10%" class="text-center">Action</th> 
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td><input type="text" class="form-control" id="doc_name" name="doc_name" placeholder="Document Name"></td>
                                                                                <td><input type="file" class="form-control" id="doc_attach" name="doc_attach" ></td>
                                                                                <td><button type="button" class="btn btn-primary" id="dfd_attch_btn" onclick="add_document_attach()">Add</button></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <div class="form-group" style="margin-top:20px;" id="po_doc_list"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                  
                                              </div>

                                              <div class="col-md-6">
                                                <!-- Below code is hide by Umair according to pathik this is the not useful (07102020) -->
                                                <div class="form-group">
                                                    <label class="col-md-5 control-label">Total * <span class="currency_icon"></span></label>
                                                    <div class="col-md-5 col-xs-11">
                                                        <input id="total" name="total" type="text" readonly="readonly" class="form-control" title="Total" max="0"  value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;}?>" placeholder="total">
                                                    </div>
                                                </div>
                                                <div class="form-group hide">
                                                    <label class="col-md-5 control-label">Transport charges </label>
                                                    <div class="col-md-5 col-xs-11">
                                                        <input id="paking" name="paking" type="number"  min="0"  class="form-control" title="Transport" value="<?phpif($mode=="Add"){echo 0;}else if($mode="Edit"){echo $rel['packing'];}?>" onKeyUp="get_amount();" placeholder="Transport">
                                                    </div>
                                                </div>
                                                <div class="form-group hide">
                                                    <label class="col-md-5 control-label">Round Off</label>
                                                    <div class="col-md-5 col-xs-11">
                                                        <input id="round_off" name="round_off" type="number" class="form-control" title="Round Off" value="<?phpif($mode=="Add"){echo 0;}else if($mode="Edit"){echo $rel['round_off'];}?>" onKeyUp="get_amount();" placeholder="Round Off">
                                                    </div>
                                                </div>
                                                <!-- Dimple Panchal : start -->
                                                        <?php //$tcs_applicable = $dbcon->query("SELECT tcs_applicable FROM tbl_finance_setting as comp WHERE company_id=".$_SESSION['company_id'])
                                                        //  ->fetch_object()->tcs_applicable; 
                                                        if($tcs_applicable) {?>
                                                            <div class="form-group">
                                                                <label class="col-md-5 control-label">Select Formula</label>
                                                                <div class="col-md-5 col-xs-11">
                                                                    <select class="form-control" name="formula_id" id="formula_id" onChange="get_gtotal();">
                                                                        <?php echo get_tax_formula($dbcon,$rel['formulaid'],' and tax_type=1'); ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="col-md-5 control-label">Tax</label>
                                                                <div class="col-md-5 col-xs-11">
                                                                    <input type='text' class="form-control" name='tcs_total' id='tcs_total' value='0' />
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                        <!-- Dimple Panchal : end -->
                                                        <!-- Above code is hide by Umair according to pathik this is the not useful (07102020) -->
                                                        <div class="invoiceTotalTax">
                                                        </div>
                                                        <div class="sundryadded">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-5 control-label">Round Off * <span class="currency_icon"></span></label>
                                                            <div class="col-md-5 col-xs-11">
                                                                <input id="round_of" name="round_of" type="text"  class="form-control" title="total" value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $rel['round_of'];}?>" placeholder="Round Off"readonly="readonly">
                                                                <!--<input id="total" name="total" type="hidden" value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;} ?>" placeholder="total"readonly="readonly">-->
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-5 control-label">Grand Total * <span class="currency_icon"></span></label>
                                                            <div class="col-md-5 col-xs-11">
                                                                <input id="g_total" name="g_total" type="text"  class="form-control" title="total" value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $rel['g_total'];}?>" placeholder="total"readonly="readonly">
                                                                <!--<input id="total" name="total" type="hidden" value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;} ?>" placeholder="total"readonly="readonly">-->
                                                            </div>
                                                        </div>
                                                        <div class="form-group currency_total_div" style="display: none">
                                                            <label class="col-md-5 control-label">Grand Total (<span class="currency_type_name"></span>)*</label>
                                                            <div class="col-md-5 col-xs-11">
                                                                <input id="currency_total" name="currency_total" type="text"  class="form-control" title="total" value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $rel['currency_total'];}?>" placeholder="total"readonly="readonly">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                         <label class="col-md-5 control-label">Select Bill Sundry</label>
                                                         <div class="col-md-2">
                                                            <?php $get_bill_sundry = get_bill_sundry_ledger($dbcon,0); ?>
                                                            <select class="form-control" name="bill_sundry" id="bill_sundry" onchange="get_sundry_label(this.value)">
                                                               <option value="0">Select</option>
                                                               <?php foreach ($get_bill_sundry as $sundry) {

                                                                  ?>
                                                                  <option value="<?php echo $sundry['l_id'] ?>"><?php echo $sundry['l_name']; ?></option>

                                                              <?php } ?>
                                                          </select>
                                                      </div>
                                                      <div class="col-md-2">
                                                        <input id="bill_sundry_amount" name="bill_sundry_amount" type="text" class="form-control numbersOnly" placeholder="Amount" title="Amount" value="<?=$rel['amount']?>" placeholder="" >
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" type="button" value="R1" onclick="addBillSundry()"><i class="fa fa-plus"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                           
                                            </div>
                                        </div>
                                        <div id="des" class="tab-pane">
                                            <div class="col-md-6" style="margin-top:12px;padding:10px" >
                                               <label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;"> Description </label>
                                               <div class="col-md-12">
                                                  <div class="form-group">
                                                     <textarea class="form-control" placeholder="Product Description" name="pro_des" id="pro_des" ></textarea>
                                                 </div>
                                             </div>
                                         </div>

                                         <div class="col-md-6" style="margin-top:12px;padding:10px" >
                                           <label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;"> Specification </label>
                                           <div class="col-md-12">
                                              <div class="form-group">
                                                 <textarea class="form-control" placeholder="Product Specification" name="pro_spe" id="pro_spe" ></textarea>
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
                        <button type="button" class="btn btn-success" id="save_con" name="save_con" onclick="open_consignee_concept()" style="display:none">Submit</button>
                        <a href="<?=ROOT.PURCHASE_ROOT.'po_list'?>" type="button" class="btn btn-danger">Cancel</a>
                        <div class="col-md-3"></div>
                    </div>
                    <!--Vendor row end-->   
                    <input type='hidden' name='po_request' id='po_request' value='<?=$request?>' />
                    <input type='hidden' name='financial_year' id='financial_year' value='<?=$financial_year['financial_year_id'];?>' />
                    <input type='hidden' name='viewmode' id='viewmode' value='<?=$viewmode?>' />
                    <input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
                    <!-- <input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" /> -->
                    <input type='hidden' name='eid' id='eid' value='<?=$purchaseorder_id;?>' />   
                    <input type='hidden' name='start_purchaseorder_id' id='start_purchaseorder_id' value='<?=$start_purchaseorder_id;?>' />   
                    <input type='hidden' name='prev_purchaseorder_id' id='prev_purchaseorder_id' value='<?=$purchaseorder_id;?>' />   
                    <input type='hidden' name='revise_status' id='revise_status' value='<?=$revise_status;?>' />   
                    <input type='hidden' name='back' id='back' value='<?=$back;?>' /> 
                    <input type='hidden' name='direct_po_create' id='direct_po_create' value="<?=$set_conf['direct_po_create']?>">
                    <?php 
                    if($direct_add=='1'){
                        ?>    
                        <input type="hidden" name="po_ref_id" id="po_ref_id" value="<?=$rel['purchaseorder_id']?>" />
                    <?php} ?>  
                    <?php include_once($include1.'po_consignee_concept.php')?>
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
<?php include_once('../../crm/include/add_cust.php');?>
<?php //include_once('../include/add_vender.php');?>
<?php include_once('../../crm/include/add_product.php');?>
<?php include_once($path.'administration/include/add_city.php');?>
<?php include_once($path.'administration/include/add_state.php');?>
<?php include_once($include.'/add_payterms.php');?>
<?php //include_once($include.'/add_placesupally.php');?>
<?php //include_once($include.'/add_modedispatch.php');?>
<?php include_once($include.'/add_po_dispach_date.php');?>
<?php include_once($include_finance.'add_ledger.php');?>
<?php include_once($include1.'vendor_price_list.php');?>
<?php include_once($include1.'vendor_product_price_list.php');?>
<?php include_once($include1.'view_delivery_detail.php');?>
<?php include_once($include.'/footer.php');?>
<?php include_once($path.'administration/include/add_product.php');?>
<?php include_once($path.'administration/include/add_hsn_in_popup.php');?>
<!--footer end-->
</section>
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'/include_js_file.php');?>   
<script src="<?=ROOT.PURCHASE_ROOT?>js/app/po.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/payment_terms.js?<?=time()?>"></script>
<!-- <script src="<?=ROOT?>js/app/mode_disptch.js?<?=time()?>"></script> -->
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/product_mst.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/state_mst.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/city_mst.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/product_mst.js?<?=time(); ?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/hsn_master.js?<?php echo time(); ?>"></script>
<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/add_ledger_js.js?<?=time()?>"></script>
<script src="<?=ROOT?><?=ADMINISTRATION_ROOT?>js/app/ledger.js?<?=time()?>"></script>
<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/common_form_finance.js?<?=time()?>"></script>
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
        CKEDITOR.replace( 'pro_des', {
          enterMode: CKEDITOR.ENTER_BR
      });

        CKEDITOR.replace( 'pro_spe', {
          enterMode: CKEDITOR.ENTER_BR
      });
        CKEDITOR.replace( 'con_address', {
          enterMode: CKEDITOR.ENTER_BR
      });
        CKEDITOR.replace( 'remark', {
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
consinee_change();
get_tax_details_table();
get_invoice_total_tax();
load_po_datatable();
show_data();
product_load();
delivery_type_permission();
load_products();
job_work_process();
tc_format_view();
get_symbol();
jQuery('.numbersOnly').keyup(function () { 
	this.value = this.value.replace(/[^0-9\.]/g,'');
});
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
    load_typeswise_terms(<?=$purchaseorder_id?>);
    get_revise_po_no(<?=$purchaseorder_id?>,<?=$start_purchaseorder_id?>);
<?php} ?>
<?php if($mode=='Edit'){?>
    load_typeswise_terms(<?=$purchaseorder_id?>);
    
    <?php }  else {?>
        load_transportation_vendor_wise();
        <?php }?>
    </script>
    <?php 
    echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
    echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
    if($mode=="Add" && $viewmode=="Add"){
//  echo "<script>show_data();</script>";
        echo "<script>load_typeswise_terms(".$quot_type.",'')</script>";
        //echo "<script>get_series_no(6);</script>";
    }
    if($direct_add=='1'){
/*echo "<script>entry_po_req_data(".$rel['purchaseorder_id'].");</script>";
echo "<script>
$('#po_type_status').attr('style','pointer-events: none;').attr('readonly','readonly');
</script>";*/
}
?>
<?php 
    /*echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
    echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";*/

    echo "<script>load_state(".$countryid.",'con_stateid',".$stateid.")</script>";
    echo "<script>load_city(".$stateid.",'con_cityid',".$cityid.")</script>";
    echo "<script>direct_po_create_no()</script>";
    ?>
</body>
</html>
