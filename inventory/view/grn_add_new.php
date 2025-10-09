<?php 
	session_start();
	include('../include/urlfile.php');	
	// error_reporting(E_ALL);
	$form="G.R.N.";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	$branch_id = $_SESSION['branch_id'];
	

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    INVENTORY_GRN_LIST_SLUG_VIEW,INVENTORY_GRN_LIST_SLUG_CREATE,PRODUCTION_GRN_DIRECT
]);
	if(strpos($_SERVER['REQUEST_URI'], "grn_edit")==true){
		
		$mode="Edit";
		$grn_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select mst.*,po.purchaseorder_no,ledger.l_name,jo.jobwork_no from tbl_grn as mst
		left join tbl_purchaseorder as po on po.purchaseorder_id=mst.purchaseorder_id
		left join tbl_jobwork as jo on jo.jobwork_id=mst.purchaseorder_id
		left join tbl_ledger as ledger on ledger.l_id=mst.vender_id
		where mst.grn_id=$grn_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$grn_date=date('d-m-Y',strtotime($rel['grn_date'])); 
		$ref_date='';
		if($rel['ref_date']!="1970-01-01" && $rel['ref_date']!="0000-00-00" && $rel['ref_date']!=""){
			$ref_date=date('d-m-Y',strtotime($rel['ref_date']));
		} 
		if($rel['gir_date'] != ""){
			$gir_date   = date('d-m-Y h:i A',strtotime($rel['gir_date']));	
		}
		
		if($rel['ref_type']==1){
			$pono=$rel['jobwork_no'];
		}else{
			$pono=$rel['purchaseorder_no'];
		}
		$back="grn_list";
	}
	else if(strpos($_SERVER['REQUEST_URI'], "grn_add_job")==true){
		$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
		
		$query1="select * from tbl_jobwork as mst
		where mst.jobwork_id=".$purchaseorder_id;
		$rel2=mysqli_fetch_assoc($dbcon->query($query1));
		
		$rel['ref_type']=1;
		$mode="Add";
		$pmode="padd";
		$grn_date=date('d-m-Y');
		$ref_no=$rel2['jobwork_no'];
		//$ref_no=$rel2['jobwork_no'];
		$back="pending_job_work";
	}
	else if(strpos($_SERVER['REQUEST_URI'], "grn_add_po")==true){
		$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
		
		$query1="select * from tbl_purchaseorder as mst
		where mst.purchaseorder_id=".$purchaseorder_id;
		$rel2=mysqli_fetch_assoc($dbcon->query($query1));
		
		$rel['ref_type']=2;
		
		$mode="Add";
		$pmode="padd";
		$grn_date=date('d-m-Y');
		$ref_no=$rel2['purchaseorder_no'];
		$back="overdue_po_pro_list";
	}
	else{
		$mode="Add";
		$grn_date=date('d-m-Y');
		$back="grn_list";
	}
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	
	$set_conf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
	$set_conr=mysqli_fetch_assoc($dbcon->query($set_conf));
	$supplier_tc_no = $set_conr['supplier_tc_no'];
	$getspecialConfiguration=getspecialConfiguration($dbcon);

	$remark_req = "";

   if($getspecialConfiguration['hermattic_permission']=="1") {
       $remark_req = "required";
   }

   $inventory_party_show = $set_conr['inventory_party_show'];

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>GRN</title>
		<?php include_once($include.'include_css_file.php');?>
		<style type="text/css">
			
 .is_conversation_toggle {
	 background-color: #f1f1f1;
	 border: 1px solid #ddd;
	 width: 100px;
	 border-radius: 2em;
	 padding: 5px;
	 margin: 0 auto;
	 position: relative;
	 margin-top: 0px;
}
 .is_conversation_toggle span {
	 text-transform: uppercase;
	 font-weight: bold;
	 position: absolute;
	 top: 5px;
     font-size: 20px;
}
 .is_conversation_toggle span.yes_span {
	 left: -45px;
}
 .is_conversation_toggle span.no_span {
	 left: 115px;
}
 .is_conversation_toggle .toggle_icon {
	 position: relative;
	 z-index: 2;
	 cursor: pointer;
	 -webkit-transition: color 0.5s ease;
	 -moz-transition: color 0.5s ease;
	 -o-transition: color 0.5s ease;
	 transition: color 0.5s ease;
}
 .is_conversation_toggle .toggle_icon.yes {
	 margin-left: 2px;
	 float: left;
	 width: 50%;
}
 .is_conversation_toggle .toggle_icon.yes.selected {
	 color: #39bf3f;
}
 .is_conversation_toggle .toggle_icon.no {
	 margin-right: 2px;
	 float: right;
	 width: 45%;
}
 .is_conversation_toggle .toggle_icon.no.selected {
	 color: #bf002d;
}
 .is_conversation_toggle .toggle {
	 width: 42px;
	 height: 40px;
	 border-radius: 42px;
	 background-color: #ddd;
	 position: absolute;
	 z-index: 1;
	 left: 0px;
	 top: 0px;
	 -webkit-transition: background-color 0.5s ease;
	 -moz-transition: background-color 0.5s ease;
	 -o-transition: background-color 0.5s ease;
	 transition: background-color 0.5s ease;
}
 .is_conversation_toggle .toggle.yes {
	 background-color: rgba(57, 191, 63, 0.5);
}
 .is_conversation_toggle .toggle.no {
	 background-color: rgba(191, 0, 45, 0.3);
}
 .is_conversation_toggle .clearfix {
	 clear: both;
	 float: none;
}
 .is_conversation_wrap {
	 display: none;
}
 .fa-times-rectangle:before, .fa-window-close:before {
    margin-left: 12px;
}
.fa-check-square:before {
    content: "\f14a";
    margin-left: 0px;
}
 
		</style>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
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
										<li><a href="<?=ROOT.INVENTORY_ROOT.'grn_list'?>"><?=$form?> List</a></li>
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
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="grn_add" action="javascript:;" method="post" name="grn_add" enctype="multipart/form-data">
										<div class="row"> 
											<div class="col-md-12" style="margin-top:10px;">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">G.R.N. No.*</label>
														<div class="col-md-6 col-xs-11">
															<input type="text" id="grn_no" name="grn_no" class="form-control" title="GRN No." value="<?=$rel['grn_no']?>" placeholder="GRN No" readonly>
														</div>
													</div>
												</div>	
												<div class="col-md-4">  	
													<div class="form-group">  	
														<label class="col-md-4 control-label">G.R.N. Date*</label>
														<div class="col-md-6 col-xs-11">
															<input type="text" id="grn_date" name="grn_date" class="form-control default-date-picker" title="Date" value="<?=$grn_date?>" placeholder="Purchase Date">
														</div>
													</div>	
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Invoice No *</label>
														<div class="col-md-6 col-xs-11">
															<input type="text" id="invoice_no" name="invoice_no" class="form-control" title="Invoice No." value="<?=$rel['invoice_no']?>" placeholder="Invoice No">
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Challan No *</label>
														<div class="col-md-6 col-xs-11">
															<input type="text" id="challan_no" name="challan_no" class="form-control" title="Challan No." value="<?=$rel['challan_no']?>" placeholder="Challan No">
														</div>
													</div>
												</div>

												<input type="text" class="form-control hidden"  name="gir_no" id="gir_no" placeholder="GIR No." value="<?=$rel['gir_no']?>"  />
												<input type="text" class="form-control hidden" value="<?=$gir_date?>" name="gir_date" id="gir_date" autocomplete="off">

												<!-- <div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label" style="">GIR No.</label>
														<div class="col-md-6" style="padding-left: 9px;">
															<input type="text" class="form-control"  name="gir_no" id="gir_no" placeholder="GIR No." value="<?=$rel['gir_no']?>"  />
														</div>  
													</div>
												</div>
												<div class="col-md-4">
			                                        <div class="form-group">
			                                            <label class="col-md-4 control-label">GRI Date</label>
			                                            <div class="col-md-8">
			                                                <div data-date="<?=$gir_date?>" class="input-group date form_datetime-meridian">
			                                                    <input type="text" class="form-control" value="<?=$gir_date?>" name="gir_date" id="gir_date" autocomplete="off">
			                                                    <div class="input-group-btn">
			                                                        <button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
			                                                    </div>
			                                                </div>
			                                            </div>
			                                        </div>
			                                    </div>
												
											</div>
											<div class="col-md-12"> -->
												<?php if($branch_id=='0'){ ?>
													<div class="col-md-4">
													<div class="form-group">
													<label class="col-md-4 control-label">Branch Name *</label>
													<div class="col-md-6">
													<select class="select2" name="branch_id" id="branch_id" title="Select Branch" onChange="load_purhcase_order_data();" required>
                                                       <?=get_branch_name_company($dbcon,$branch_id,"","")?>
                                                    </select>
													</div>
													</div>
													</div>
													<?php }else{ ?>
													<input type="hidden" name="branch_id" id="branch_id" value="<?=$branch_id?>">
												<?php } ?>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label" style="">Vehicle No.</label>
														<div class="col-md-6" style="padding-left: 9px;">
															<input type="text" class="form-control"  name="vehicle_no" id="vehicle_no" placeholder="Vehicle No." value="<?=$rel['vehicle_no']?>"  />
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label" style="">Dispatch By.</label>
														<div class="col-md-6" style="padding-left: 9px;">
															<select style="padding-right: 0px;" class="form-control" name="mode_dispatch" id="mode_dispatch" >
                                                        		<?=getmodeofdispache($dbcon,$rel['mode_of_dispatch']);?>
                                                    		</select>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">  <hr> </div>
											<div class="col-md-12" style="margin-top:10px;">
												<div class="col-md-4">
													<label class="col-md-4 control-label" style="">GRN Against*</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<select class="select2" name="grn_against" id="grn_against" title="Select GRN Against" onChange="get_order_no();load_grn_no();hide_show_prouct_add_row(this.value);" required>
															<option value="">--Select GRN Against--</option>
															<option value="1" <?=($rel['ref_type']=='1')?'selected':''?>> Jobwork </option>
															<option value="2" <?=($rel['ref_type']=='2')?'selected':''?>> Purchase Order </option>
															<option value="3" <?=($rel['ref_type']=='3')?'selected':''?>>Service Purchase Order </option>
														<?php if(in_array(PRODUCTION_GRN_DIRECT,$bulkAccessArray)){ ?>
															<option value="4" <?=($rel['ref_type']=='4')?'selected':''?>>Direct GRN </option>
														<?php} ?>
															<option value="5" <?=($rel['ref_type']=='5')?'selected':''?>>Outside So GRN </option>
															<option value="6" <?=($rel['ref_type']=='6')?'selected':''?>>Returnable Chalan GRN </option>
															<option value="7" <?=($rel['ref_type']=='7')?'selected':''?>>Stock Transfer GRN </option>
														</select>
													</div>  
												</div>
												<div class="col-md-4">
													<label class="col-md-4 control-label" style="">Vendor*</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<select class="select2" name="vender_id" id="vender_id" required title="Select Vendor" onChange="get_order_no();load_purhcase_order_data();" >
															<option value="">--Select Vendor--</option>
															<?=getcust($dbcon,$vender_id,$inventory_party_show);?>	
														</select>
														<!--<input type="text" class="form-control"  name="vender_name" id="vender_name" placeholder="Vender Name" value="<?=$rel['l_name']?>" readonly />
														<input type="hidden" class="form-control"  name="vender_id" id="vender_id" placeholder="vender Id" />
														<input type="hidden" class="form-control"  name="request_no" id="request_no" placeholder="request_no" />-->
													</div>  
												</div>
												<div class="col-md-4 outside_jobwork_hide">
													<label class="col-md-4 control-label" style="white-space:nowrap;">Choose Order No *</label>
													<div class="col-md-8">
														<?if($mode=='Add'){?>
															<?if($pmode=="padd"){ ?>
																<input type="text" class="form-control" value="<?=$ref_no?>" readonly>
																
																<input type="hidden" name="purchaseorder_id" id="purchaseorder_id" value="<?=$purchaseorder_id?>" />
															<?php}else{ ?>
															
															<select class="select2" name="purchaseorder_id" id="purchaseorder_id" multiple onChange="load_purhcase_order_data()">
																<option value="">Choose Order No</option>
															</select>
															
															
															<?php} ?>
														<?}else{?>
															<input type="text" class="form-control" value="<?=$pono?>" readonly>
															<input type="hidden" name="purchaseorder_id" id="purchaseorder_id" value="<?=$rel['purchaseorder_id']?>" />
														
														<?}?>
													</div>
												</div>
												
											</div>	
											<!-- <div class="col-md-12" style="margin-top:10px;"><div class="col-md-4">
													<label class="col-md-4 control-label" style="">Conversation ?*</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<select class="select2" name="is_conversation" id="is_conversation" title="Select Conversation"  required>
															<option value="">--Select Conversation--</option>
															<option value="1" <?=($rel['is_conversation']=='1')?'selected':''?>> Yes </option>
															<option value="2" <?=($rel['is_conversation']=='2')?'selected':''?>> No </option>
														</select>
													</div>  
												</div></div> -->	

												<div class="col-md-12" style="margin-top:20px;<?=$display?>"><div class="col-md-4">
													<label class="col-md-5 control-label m-bot15" style="">Auto Conversation ?*</label>
													<!-- <div class="col-md-2" style="padding-left: 9px;"> -->
														<?php /*<select class="select2" name="is_conversation" id="is_conversation" title="Select Conversation"  required>
															<option value="">--Select Conversation--</option>
															<option value="1" <?=($rel['is_conversation']=='1')?'selected':''?>> Yes </option>
															<option value="2" <?=($rel['is_conversation']=='2')?'selected':''?>> No </option>
														</select>  */?>
														<!-- <input type="checkbox" class="form-control" id="is_conversation" name="is_conversation"> -->
														<!-- </div>  -->
													<div class="col-md-5">
														<div class="is_conversation_toggle">
															<span class="yes_span">Yes</span>
															<span class="no_span">No</span>
															<i class="fa fa-check-square fa-2x toggle_icon yes selected" aria-hidden="true"></i>
															<i class="fa fa-times-rectangle fa-2x toggle_icon no " aria-hidden="true"></i>
															
															<div class="toggle selected yes" style="left: 0px;"></div>
															<div class="clearfix"></div>
														</div>

														<div class="is_conversation_wrap">
															<input checked="checked" type="radio" checked="checked" value="1"  id="auto_conversation_yes" name="is_conversation" /> Yes
															<input type="radio"  id="auto_conversation_no" name="is_conversation" value="0" /> No
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 direct_grn_row" style="margin-top:10px;display: none;">
											
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit;table-layout: fixed;" id="product_list" class="display table table-bordered table-striped">
														<tr id="field">
															<!-- <th width="15%" class="text-center">Type</th> -->
															<th width="15%" class="text-center outside_jobwork" style="display:none;">Sales Order</th>
															<th width="25%" class="text-center">Product Detail</th>
															<th width="10%" class="text-center">Rate Unit</th>
															<th width="10%" class="text-center hide_act_add">Unit</th>
															<th width="10%" class="text-center hide_act_add">Qty</th>
															<th width="10%" class="text-center hide_act_add">Conv Unit</th>
															<th width="10%" class="text-center hide_act_add">Conv Qty.</th>
															<th width="15%" class="text-center hide_act_add">Godown</th>
															<th width="10%" class="text-center"></th>
														</tr>
														<tr id="field111">
															<!-- <td style="vertical-align:top;" width="20%">
																<select class="select2 prtype" name="product_type" id="product_type" onChange="load_product(this.value);" title="Select Product Type" style="width: 100%;">
																	<?=get_bom_producttype($dbcon,'');?>
																</select>
															</td> -->
															<td class="outside_jobwork" style="display:none;">
																	<select class="select2" name="outside_so_id" id="outside_so_id" title="Select Sales Order" style="width: 100%;">
																</select>
																</td>
															<td style="vertical-align:top;" width="25%">
																<!-- <select class="select2 selproduct" title="Select product" name="product_id" id="product_id" onchange="load_product_types();load_product_version(this.value,'');load_product_detail(this.value);" >
																	<?=getproduct_typewise($dbcon,'',$type_conf,$pro_search);?>
																</select> -->
																<input id="ourside_so_product_id" name="ourside_so_product_id" style="width:100%;" placeholder="Select product" onchange="load_product_detail(this.value);load_product_unit(this.value)"/>
																<br/><br/>
																<div id="get_spec_div" style="display:none">
																	<!--Width : <input type="text" class="form-control" name="product_width" id="product_width" value="<?=$mode=='Edit'?$rel['product_width']:0?>" onkeyup="get_ms_kg()" />
																	Height : <input type="text" class="form-control" name="product_height" id="product_height" value="<?=$mode=='Edit'?$rel['product_height']:0?>" onkeyup="get_ms_kg()" />
																	
																	Thickness : <input type="text" class="form-control" name="product_thickness" id="product_thickness" value="<?=$mode=='Edit'?$rel['product_thickness']:0?>" onkeyup="get_ms_kg()" />
																	
																	<input type="hidden" class="form-control" name="product_density" id="product_density" value="<?=$mode=='Edit'?$rel['product_density']:0?>" onkeyup="get_ms_kg()" /> -->
																	
																	<!-- <input type="text" class="form-control" name="product_kg" id="product_kg" value="<?=$mode=='Edit'?$rel['product_kg']:0?>" readonly />
																		<input type="checkbox" name="set_kg" id="set_kg" value="0" onclick="set_kg_to_qty(this.value)" />SET -->
																	</div>
																</td>	
																
																<td class="">
																<select class="select2" name="rate_unit" id="rate_unit" title="Select Rate Unit" style="width: 100%;">
																	<option value="">Select Rate Unit</option>
																</select>
																</td>
																<td style="vertical-align:top;" class="hide_act_add">
															<!--<select class="form-control" id="product_base_unit" name="product_base_unit" >
																	<option value="">--select Unit--</option>
																	<?//=getunit($dbcon);?>
																</select>-->
																<input class="form-control" type="text" name="product_base_unit_name" id="product_base_unit_name" value="" readonly />
																<input type="hidden" name="product_base_unit" id="product_base_unit"value="" />	

															</td>	
															<td style="vertical-align:top;" class="hide_act_add">
																<input type="number"  title="Enter Qty" min="0" id="product_base_qty" name="product_base_qty" onkeyup="convert_qty(1);" value="1"  class="form-control" />

																<input type="hidden" id="product_base_qty_hide" name="product_base_qty_hide" value="" />

															</td>
															<td style="vertical-align:top;" class="hide_act_add">
																<!--<select class="form-control" id="product_uom" name="product_uom" >
																	<option value="">--select UOM--</option>
																	<?//=getunit($dbcon);?>
																</select>-->
																<input class="form-control" type="text" id="product_conv_unit_name" name="product_conv_unit_name" value="" readonly />

																<input type="hidden" name="product_conv_unit" id="product_conv_unit"value="" />
															</td>
															<td style="vertical-align:top;" class="hide_act_add">
																<input type="number"  title="Enter Qty" min="0" id="product_conv_qty" 

																name="product_conv_qty"  class="form-control" onkeyup="convert_qty(2);"  value="1"  />
																<!--onkeyup="product_convert_qty(2);"-->
																<input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />

																<input type="hidden"  title="" id="product_spec_hid" name="product_spec_hid"  class="form-control" />
																<input type="hidden"  title="" id="product_spec_hid_qty" name="product_spec_hid_qty"  class="form-control" />
																<input type="hidden"  title="" id="product_spec_act_qty" name="product_spec_act_qty"  class="form-control" />
															</td>	
															<td>
																<select class='form-control' name='godown_id' id='godown_id' required >
																<?= get_all_godown($dbcon,"",1); ?>
																</select>
															</td>	
															<td style="vertical-align:top;">
																<!-- Sanat :: comment below button :: 03-03-2021 -->
																<!-- <input type="button"  name="addrow" id="addrow" onClick="return add_field();" class="btn btn-primary" value="Add"/> -->
																<input type="button" id="addprocess" class="btn btn-primary" data-original-title="Add Process" data-toggle="tooltip" data-placement="top" onclick="direct_add_grn_field();" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value="" />
															<input type='hidden' name='mode_edit' id='mode_edit' value="<?=$mode?>" />
															<input type='hidden' name='mode_edit_id' id='mode_edit_id' value="<?=$rel['bom_id']?>" />
															<input type='hidden' name='actual_qty' id='actual_qty' value="<?=$rel['bom_qty'];?>" />
															<input type='hidden' name='thread' id='thread' value="1" />
															<input type='hidden' name='level' id='level' value="1" />
															<input type='hidden' name='parent_id' id='parent_id' value="0" />
															<input type="hidden" name="" id="main_product" value="<?php if(isset($_GET['main_product'])){ echo $main_product; } else { echo $rel['bom_product']; } ?>" />
															<input type="hidden" name="p_bom_id" id="p_bom_id" value="" />
														</tr>
													</table>			
												</div>
							
											</div>
											<div class="col-md-12" style="margin-top:10px;">
												<div class="form-group">
													<div class="col-md-12 col-xs-11">
														<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped">
															<tr id="field">
																<th width="8%" class="text-center">Product Type</th>
																<th width="25%" class="text-center">Product Name</th>
																<th width="8%" class="text-center">Product Category</th>
																<th width="8%" class="text-center">Total Qty</th>
																<th width="8%" class="text-center">Pending Qty</th>
																<th width="8%" class="text-center">Quantity</th>
																<th width="10%" class="text-center" id="godown_id">Godown</th>
																<th width="5%" class="text-center">Action</th>
																
															</tr>
															<tbody id="field1" style="text-align:center">
															
															</tbody>
														</table>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-3 control-label">Remarks </label>
														<div class="col-md-9 col-xs-11">
															<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
														</div>
													</div> 
												</div>
												<?if($mode=="Add" && $set_conr['upload_reciept'] == "Yes"){ 
													$ttrt="required";
												}else{
													$ttrt="";
												}
												?>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Upload Receipt</label>
														<div class="col-md-7">
															<input type="file" class="form-control" id="grn_file" name="grn_file[]" multiple="multiple" <?=$ttrt?> />
														</div>
														<div class="col-md-2">
														<?phpif($mode=='Edit'){
															 $get_attch_qry="select * from tbl_grn_attch where grn_attch_status=0 and grn_id=".$rel['grn_id'];
															$attch_rs=$dbcon->query($get_attch_qry);
															while($attch_rel=mysqli_fetch_assoc($attch_rs)){
														?>
															<a href="<?=ROOT.RECEIPT_FILE_VWING.$attch_rel['grn_file']?>" class="btn btn-xs btn-primary" target="_blank" style="margin-bottom: 2px;" title="<?=$attch_rel['grn_file']?>"><i class="fa fa-eye"></i></a> 
															<button type="button" onClick="delete_attch(<?=$attch_rel['grn_attch_id']?>)" class="btn btn-xs btn-danger" target="_blank" style="margin-bottom: 2px;"><i class="fa fa-trash-o"></i></button>
															<br/>
														<?} }?>
														</div>
													</div> 
												</div>
												<div class="clearfix"></div>	
											</div>
											<?phpif($getspecialConfiguration['hermattic_permission']=="1") { ?>
											<div class="col-md-12">
												<div class="clearfix"></div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Material Inspected</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="material_inspected" id="material_inspected" title="Select Option" required>
															<option value="">--Select Option--</option>
															<option value="Yes" <?=($rel['material_inspected']=='Yes')?'selected':''?>> Yes </option>
															<option value="No" <?=($rel['material_inspected']=='No')?'selected':''?>> No </option>
															<option value="N.A." <?=($rel['material_inspected']=='N.A.')?'selected':''?>> N.A. </option>
														</select>
													</div>  
												</div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Test certificate</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="test_certificate" id="test_certificate" title="Select Option" onChange="get_order_no();" required>
															<option value="">--Select Option--</option>
															<option value="Received" <?=($rel['test_certificate']=='Received')?'selected':''?>> Received </option>
															<option value="Not Received" <?=($rel['test_certificate']=='Not Received')?'selected':''?>> Not Received </option>
															<option value="Not Applicable" <?=($rel['test_certificate']=='Not Applicable')?'selected':''?>> Not Applicable </option>
														</select>
													</div>  
												</div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Test certificate - Reviewed as per Code</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="test_certificate_code" id="test_certificate_code" title="Select Option" required>
															<option value="">--Select Option--</option>
															<option value="Yes" <?=($rel['test_certificate_code']=='Yes')?'selected':''?>> Yes </option>
															<option value="No" <?=($rel['test_certificate_code']=='No')?'selected':''?>> No </option>
															<option value="N.A." <?=($rel['test_certificate_code']=='N.A.')?'selected':''?>> N.A. </option>
														</select>
													</div>  
												</div>
												<div class="clearfix"></div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Dimensional Insception Done</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="dimension_inspected" id="dimension_inspected" title="Select Option" required>
															<option value="">--Select Option--</option>
															<option value="Yes" <?=($rel['dimension_inspected']=='Yes')?'selected':''?>> Yes </option>
															<option value="No" <?=($rel['dimension_inspected']=='No')?'selected':''?>> No </option>
															<option value="N.A." <?=($rel['dimension_inspected']=='N.A.')?'selected':''?>> N.A. </option>
														</select>
													</div>  
												</div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Inspection Report attached</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="inspection_report" id="inspection_report" title="Select Option" required>
															<option value="">--Select Option--</option>
															<option value="Yes" <?=($rel['inspection_report']=='Yes')?'selected':''?>> Yes </option>
															<option value="No" <?=($rel['inspection_report']=='No')?'selected':''?>> No </option>
															<option value="N.A." <?=($rel['inspection_report']=='N.A.')?'selected':''?>> N.A. </option>
														</select>
													</div>  
												</div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Qty Verified & Ok</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="qty_verified" id="qty_verified" title="Select Option" onChange="get_order_no();" required>
															<option value="">--Select Option--</option>
															<option value="Yes" <?=($rel['qty_verified']=='Yes')?'selected':''?>> Yes </option>
															<option value="No" <?=($rel['qty_verified']=='No')?'selected':''?>> No </option>
															<option value="N.A." <?=($rel['qty_verified']=='N.A.')?'selected':''?>> N.A. </option>
														</select>
													</div>  
												</div>	
												<div class="clearfix"></div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Checked & Release for process</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="process_checked" id="process_checked" title="Select Option" onChange="get_order_no();" required>
															<option value="">--Select Option--</option>
															<option value="Yes" <?=($rel['process_checked']=='Yes')?'selected':''?>> Yes </option>
															<option value="No" <?=($rel['process_checked']=='No')?'selected':''?>> No </option>
															<option value="N.A." <?=($rel['process_checked']=='N.A.')?'selected':''?>> N.A. </option>
														</select>
													</div>  
												</div>

											</div>
										<?php} ?>
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='eid' id='eid' value='<?=$rel['grn_id']?>' />
											<input type='hidden' name='pmode' id='pmode' value='<?=$pmode?>' />
											<input type='hidden' name='back' id='back' value='<?=$back?>' />
											<input type='hidden' name='supplier_tc_no' id='supplier_tc_no' value='<?=$supplier_tc_no?>' />
											<!-- <input type="hidden" name="job_work_po_trn_id" id="job_work_po_trn_id" value="<?=$job_work_po_trn_id?>"> -->
											<div class="clearfix"></div>	
											<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
											<a href="<?=ROOT.INVENTORY_ROOT.'grn_list'?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-4"></div>
										</div>
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
		<?php include_once($include1.'add_batchmodel.php');?> 
		<?php include_once($include1.'jobwork_wise_grn.php');?> 
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/grn.js?<?=time()?>"></script>
		<script>
			//$('#container').addClass('sidebar-closed');
			$(".select2").select2({
				width: '100%'
			});
			$(".select4").select2({
				width: '100%'
			});	
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
			<?if($mode=='Edit'){?>
			$('#vender_id').select2('readonly',true);
			$('#branch_id').select2('readonly',true);
			$('#purchaseorder_id').select2('readonly',true);
			$('#grn_against').select2('readonly',true);
			load_purhcase_order_data(<?=$rel['purchaseorder_id']?>);
			<?}?>
			<?if($mode=='Add'){?>
			load_grn_no();
			<?}?>
			<?if($pmode=="padd"){?>
				//load_grn_no();
				$('#grn_against').select2('readonly',true);
				load_purhcase_order_data(<?=$purchaseorder_id?>);
			<?}?>


			$(function() {
	var $icon = $(".toggle_icon");
	var $toggle = $(".toggle")
	var $sad = $(".no");
	var $happy = $(".yes");
	var $yes = $("#auto_conversation_yes");
	var $no = $("#auto_conversation_no");
	
	$icon.on("click", function() {
		var $this = $(this);
		if ($this.hasClass("yes")) {
			$sad.removeClass("selected");
			$happy.addClass("selected");
			$toggle.removeClass("no");
			$toggle.addClass("yes");
			$yes.prop("checked", "checked");
			$(".div_process_type").slideUp();
			$toggle.animate({
				left: "0px"
			}, {
				queue: false,
				ease: 'easeInSine'
			});

			$(".auto_conversation_hide").hide();

			$('input.handle_qty').each(function(){ 
     			$(this).val('');
			});
			
		}
		else {
			$no.prop("checked", "checked");
			$sad.addClass("selected");
			$happy.removeClass("selected");
			$toggle.addClass("no");
			$toggle.removeClass("yes");
			$(".div_process_type").slideDown();
			$toggle.animate({
				left: "56px"
			}, {
				queue: false,
				ease: 'easeInSine'
			});
			$(".auto_conversation_hide").show();
		};
	});
});
$(".form_datetime-meridian").datetimepicker({
           format: "dd-mm-yyyy HH:ii P",
           showMeridian: true,
           autoclose: true,
           todayBtn: true,
           pickerPosition: "bottom-left",
           // startDate: today,
           // endDate: endDate
       }); 
		</script> 
	</body>
</html>