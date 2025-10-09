<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		FINANCE_BILL_OF_SUPPLY_CREATE,
		FINANCE_BILL_OF_SUPPLY_EDIT,
		FINANCE_SPARE_TO_BOS,
	]);
	$form="Bill of Supply";
	
	if(strpos($_SERVER[REQUEST_URI], "bill_of_supply_edit")==true){
		if(!in_array(FINANCE_BILL_OF_SUPPLY_EDIT,$bulkAccessArray)){
       		header("Location: ".DOMAIN."permission_access");
    	}
		$mode="Edit";
		$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_bill_of_supply where bill_of_supply_id=$invoiceid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		
		$bill_of_supply_no=$rel['bill_of_supply_no'];
		$cust_id=$rel['cust_id'];
		$bill_of_supply_date=date('d-m-Y',strtotime($rel['bill_of_supply_date']));
		$order_date='';
		if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00") {
			$order_date=date('d-m-Y',strtotime($rel['order_date']));
		}
	}
	else if(strpos($_SERVER[REQUEST_URI], "spare_to_bos")==true){
		if(!in_array(FINANCE_SPARE_TO_BOS,$bulkAccessArray)){
       		header("Location: ".DOMAIN."permission_access");
    	}
		$mode="Add";
		$bill_of_supply_date=date('d-m-Y');
		$complaint_id=$dbcon->real_escape_string($_REQUEST['id']);
		$comp_qry="select * from tbl_complaint where complaint_id=".$complaint_id;
		$comp_rel=mysqli_fetch_assoc($dbcon->query($comp_qry));
		$cust_id=$comp_rel['cust_id'];
	}
	else{
		if(!in_array(FINANCE_BILL_OF_SUPPLY_CREATE,$bulkAccessArray)){
       		header("Location: ".DOMAIN."permission_access");
    	}
		$mode="Add";
		$bill_of_supply_date=date('d-m-Y');
	}
	
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));

?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<section id="container" class="sidebar-closed">
    <?php include_once('../include/include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once('../include/left_menu.php');?>
      <!--sidebar end-->
      <!--main content start-->
          <section id="main-content">
          <section class="wrapper">
		
			<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
							<h3> <?=$mode .' '.$form?></h3>
							<?php//include_once("../include/head_menu.php") ?>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.'bill_of_supply_list'?>">Invoice List</a></li>
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
	<form class="form-horizontal" role="form" id="bill_of_supply_add" action="javascript:;" method="post" name="bill_of_supply_add">
			<div class="row">
					<!--<div class="col-md-4">
							<label class="col-md-4 control-label"> Invoice type </label>
							<div class="col-md-6 col-xs-11">
								<select style="padding-right: 0px;" class="form-control" name="invoicetype_id" id="invoicetype_id" onChange="load_invoiceno(this.value)" <?phpif($mode=='Edit'){?> readonly="readonly"<?php}?> >
									<?//=getinvoicetype($dbcon,$load_inv_type);?>
								</select>
							</div>
	    			</div>-->
					<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Bill Of Supply No *</label>
								<div class="col-md-6">
									<input id="bill_of_supply_no" name="bill_of_supply_no" type="text" class="form-control" title="Enter Bill Of Supply No" value="<?=$bill_of_supply_no?>" placeholder="Bill Of Supply No" required>		
								</div>
					         </div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label">Bill Of Supply Date*</label>
							<div class="col-md-6">
								<input id="bill_of_supply_date" name="bill_of_supply_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$bill_of_supply_date?>" placeholder="Bill Of Supply Date" autocomplete="off">
							</div>
						</div>	
					</div>
				<div class="col-md-12"></div>
					<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Ref. No</label>
								<div class="col-md-6">
									<input id="order_no" name="order_no" type="text" class="form-control" title="Enter Ref. No" value="<?=$rel['order_no']?>" placeholder="Ref. No">		
								</div>
					         </div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label">Ref. Date</label>
							<div class="col-md-6">
								<input id="order_date" name="order_date" type="text" class="form-control default-date-picker valid" title="Date" value="<?=$order_date?>" placeholder="Ref. Date" autocomplete="off">
							</div>
						</div>	
					</div>
				<div class="col-md-12"></div>
			
				<div class="clearfix"></div>
			  							
					<div class="col-md-12"></div>
					
					<div class="col-md-8">
					 <div class="form-group">
						<label class="col-md-3 control-label">Company *</label>
						<div class="col-md-6">
							<select class="select2" name="cust_id" id="cust_id" onChange="" >
								<?=getcust($dbcon,$cust_id);?>	
							</select>
						</div>
						
					 </div>									
					</div>
			
					<!--<div class="col-md-3" id="consignee" <?if(empty($rel['consignee_id'])){ echo "style='display:none'"; }?>>
					 <div class="form-group">
						<label class="col-md-3 control-label">Consignee *</label>
						<div class="col-md-6 col-xs-11">
							<select class="select2" name="consignee_id" id="consignee_id">
								<?//=get_custmer_consignee($dbcon,$rel['cust_id'],$rel['consignee_id'])?>
							</select>
						</div>
						<div class="col-md-2">
							<input type="button" class="btn btn-primary" name="addcust" id="addcust" onClick="open_consignee_click();" value="New Consignee"/>
						</div>
					 </div>									
					</div>-->
										
			</div>
	<div class="col-md-12">
		<div class="form-group">
			
				<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
					<tr id="field">
						<th width="5%" class="text-center">Type</th>
						<th width="20%" class="text-center">Product Detail</th>
						<th width="8%" class="text-center">HSN Code</th>
						<th width="6%" class="text-center">Quantity</th>
						<th width="7%" class="text-center">Rate</th>
						<th width="7%" class="text-center">Per</th>
						<th width="6%">Discount</th>
						<th width="10%">Taxable Value</th>
						<th width="13%">Tax</th>
						<th width="10%" class="text-center">Amount</th>
						<th width="5%" class="text-center"></th>
					</tr>
					<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
					<tr id="field1">
						
						<td style="vertical-align:top;">
							<select class="select2" name="product_type_sel" id="product_type_sel" onChange="load_product_typeiwse(this.value);" title="Select Product Type">
								<?=getproducttype($dbcon,'0');?>
							</select>
							<!-- <input type="hidden" name="product_type" id="product_type" value="0" /> -->
						</td>
						
						<td style="vertical-align:top;">
							<select class="select2" title="Select product" name="product_id" id="product_id" onChange="load_productdetail(this.value);"><!--load_qty()-->
								<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
							</select>
							<!--<input type="button"  name="addproduct" id="addproduct" data-toggle="modal" data-target="#bs-example-modal-addproduct"  class="btn btn-primary" value="+"/>-->
							<br/><br/>
							<textarea id="product_des" name="product_des" class="form-control" placeholder="Product Description"></textarea>
						</td>	
						<td style="vertical-align:top;">
							<input type="text"  title="Enter HSN Code" id="product_hsn_code" name="product_hsn_code" class="form-control"/>
						</td>
						<td style="vertical-align:top;">
							<input type="number"  title="Enter Qty" min="0" id="product_qty" name="product_qty"  class="form-control" onKeyUp="get_amount();"/><br/>
							
						</td>
					
						<td style="vertical-align:top;">
							<input type="number"  title="Enter Rate" min="0" id="product_rate" name="product_rate" onKeyUp="get_amount();" class="form-control"/><br/>
							<!--<button type="button" title="Show Previous Rate History" name="rate_history" id="rate_history" onclick="load_rate_hist()" style="display:none;" class="btn btn-info"><i class="fa fa-eye"></i> show</button>-->
						</td>
						<td style="vertical-align:top;">
							<select class="select2"  title="Select Unit" name="unit_id" id="unit_id">
								<?=getunit($dbcon,0);?>
							</select>
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
								<?
									echo getformula($dbcon,$rel['formulaid']);
								?>
							</select>
						</td>
						
						<td style="vertical-align:top;"> 
							<input type="number" min="0" id="product_amount" readonly="readonly" name="product_amount" class="form-control" onmouseover="this.title=this.value"/>
						</td>
						<td style="vertical-align:top;"> 
							<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>	
						</td>
							 <input type='hidden' name='edit_id' id='edit_id' value='' />
					
						</tr>
			</table>								
  
		
		</div>
	</div>
		<div id="sale_productdata"></div>
	<div class="clearfix"></div>
	 
		<div class="col-md-6">
		 <div class="form-group">
		  <label class="col-md-4 control-label">Remarks </label>
				<div class="col-md-6 col-xs-11">
				<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
			</div>
		 </div>
		 
		</div>	
						
						<div class="col-md-6">
							<?
								if($set_head['show_charges']=='1'){
									$ttl_display="display:block";
								}else{
									$ttl_display="display:none";
								}
							?>	
								
							<div class="form-group" style="<?=$ttl_display?>">
								<label class="col-md-5 control-label">Total *</label>
								<div class="col-md-5 col-xs-11">
									<input id="total" name="total" type="text" readonly="readonly" class="form-control" title="Grand Total" max="0"  value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;}?>" placeholder="total">
					
								</div>
							</div>	
								
							<!--<div class="form-group">
								<label class="col-md-5 control-label">Discount </label>
								<div class="col-md-2 col-xs-11">
									<input id="discount_per" name="discount_per" type="number" class="form-control col-md-6" title="in % Max 100" min="0"  value="<?phpif($mode=='Edit'){ echo $rel['discount_per'];}?>" placeholder="in %" onKeyUp="add_discount('per');" max="100" style="width: 80px;" >
									
								</div>
								<div class="col-md-3 col-xs-11">
									<input id="discount_amt" name="discount_amt" type="number" class="form-control col-md-6" title="in Rs." min="0"  value="<?phpif($mode=='Edit'){ echo $rel['discount'];}?>" placeholder="in Rs." onKeyUp="add_discount('amt');" >
								</div>
							</div>-->
							<!--
							<div class="form-group">
								<label class="col-md-5 control-label">Freight </label>
								<div class="col-md-5 col-xs-11">
								<input id="freight" name="freight" type="number" class="form-control" title="Transport" min="0"  value="<?phpif($mode=='Edit'){ echo $rel['freight'];}?>" placeholder="Freight" onKeyUp="add_freight();" >
					
								</div>
							</div>-->
							 
							<!--<div class="form-group">
								<label class="col-md-5 control-label">Tax </label>
								<div class="col-md-5 col-xs-11">
								<select class="form-control" name="formulaid" id="formulaid" onChange="get_gtotal(this.value);"  title="Select Formula">
									<?//=getformula($dbcon,$rel['formulaid']);?>
								</select>
								</div>
							</div>-->
							<div id="showformulatextbox">
							<?
							if($mode=='Edit')
							{
							if(!empty($rel['tax1_name']))
							{
							?>
							<div class="form-group">
								<label class="col-md-5 control-label"><?=$rel['tax1_name']?></label>
								<div class="col-md-5 col-xs-11">
								<input id="taxvalue0" name="taxvalue0" value= "<?=$rel['taxvalue1']?>"type="text" class="form-control" readonly="readonly">
								</div>
							</div>
					<input id="taxname0" name="taxname0" value= "<?=$rel['tax1_name']?>" type="hidden" class="form-control">
							<?
							}
							if(!empty($rel['tax2_name']))
							{
							?>
							<div class="form-group">
								<label class="col-md-5 control-label"><?=$rel['tax2_name']?></label>
								<div class="col-md-5 col-xs-11">
								<input id="taxvalue1" name="taxvalue1" value= "<?=$rel['taxvalue2']?>"type="text" class="form-control" readonly="readonly">
						</div>
					</div>
					<input id="taxname1" name="taxname1" value= "<?=$rel['tax2_name']?>" type="hidden" class="form-control">
							<?
							}if(!empty($rel['tax3_name']))
							{
							?>
					<div class="form-group">
								<label class="col-md-5 control-label"><?=$rel['tax3_name']?></label>
								<div class="col-md-5 col-xs-11">
								<input id="taxvalue2" name="taxvalue2" value= "<?=$rel['taxvalue3']?>"type="text" class="form-control" readonly="readonly">
						</div>
					</div>
					<input id="taxname2" name="taxname2" value= "<?=$rel['tax3_name']?>" type="hidden" class="form-control">
							<?
							}} 
							?>
							</div>
							<!-- <div class="form-group">
								<label class="col-md-5 control-label">Round Off</label>
								<div class="col-md-5 col-xs-11">
								<input id="round_off" name="round_off" type="number" class="form-control" title="Round Off"  value="<?phpif($mode=='Edit'){ echo $rel['round_off'];}else{ echo "0";}?>" placeholder="Round Off" onKeyUp="add_freight();" >
					
								</div>
							</div>	-->
							<div class="form-group">
								<label class="col-md-5 control-label">Net Amount *</label>
								<div class="col-md-5 col-xs-11">
								<input id="g_total" name="g_total" type="text" class="form-control" title="Net Amount" value="<?=$rel['g_total']?>" placeholder="Grand Total" readonly="readonly">
								</div>
							</div>	
							
						<div class="form-group">
							<label class="col-md-5 control-label">Select Print</label>
							<div class="col-md-5">
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
					<!--<button type="button" onClick="bos_submit();" class="btn btn-success" id="saveprint" name="saveprint">Save and Print</button> &nbsp;-->
					<a href="<?=ROOT.'bill_of_supply_list'?>" type="button" class="btn btn-danger">Cancel</a>
					<div class="col-md-3"></div>			
				</div>		</div>
				</div><!--Vendor row end-->	
					<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
					<input type='hidden' name='save_print' id='save_print' value='' />
					<input type='hidden' name='eid' id='eid' value='<?=$rel['bill_of_supply_id']?>' />
					<input type='hidden' name='complaint_id' id='complaint_id' value='<?=$complaint_id?>' />
					  
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
	  
	<?php include_once('../include/footer.php');?>
    <!--footer end-->

</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php
	include_once('../include/include_js_file.php');
?>   
<script src="<?=ROOT?>js/app/bill_of_supply.js?<?=time();?>"></script>
<script src="<?=ROOT?>js/app/customer.js"></script>

<script>
$(".select2").select2({
	width: '100%'
});
/*$("#product_id").select2({
	width: '83%'
});*/

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
$('#cust_id').select2('readonly',true);
</script>
<?
if($mode=="Add"){
	echo "<script>load_invoiceno();</script>";
}
if($complaint_id){
	echo "<script>copy_comp_spare_trn_data(".$complaint_id.");</script>";
}
?>
</body>
</html>