<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Purchase Order";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	if(strpos($_SERVER['REQUEST_URI'], "poedit")==true)
	{
		$mode="Edit";$direct_add='0';
		$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_purchaseorder where purchaseorder_id=$purchaseorder_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$purchaseorder_date = date('d-m-Y',strtotime($rel['purchaseorder_date']));
		$po_type_status=$rel['po_type_status'];
	}
	else if(strpos($_SERVER['REQUEST_URI'], "direct_po_add")==true)
	{
		$mode="Add";$direct_add='1';
		$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_purchaseorder where purchaseorder_id=$purchaseorder_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$purchaseorder_date = date('d-m-Y',strtotime($rel['purchaseorder_date']));
		$po_type_status='1';
	}
	else if(strpos($_SERVER['REQUEST_URI'], "po_req_add")==true)
	{
		$mode="Edit";$direct_add='2';
		$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_purchaseorder where purchaseorder_id=$purchaseorder_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$purchaseorder_date = date('d-m-Y',strtotime($rel['purchaseorder_date']));
		$po_type_status='1';
	}
	else
	{
		$mode="Add";$direct_add='0';
		$purchaseorder_date=date('d-m-Y');
		$po_type_status='';
	}
//	echo $_SESSION['company_id'];
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
			<?php //include_once('../include/equick_link.php');?>
    		<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
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
				<form class="form-horizontal" role="form" id="purchaseorder_add" action="javascript:;" method="post" name="purchaseorder_add">
						<div class="row">
					 
<!--	<div class="col-md-12" style="margin-top:10px;">
		<label class="col-md-2 control-label" style="">Select PO Type</label>
		<div class="col-md-3 col-xs-11" style="padding-left:15px">
			<select class="form-control" name="po_type_status" id="po_type_status" required title="Select PO Type" >
				<option value="1" <?=($po_type_status=='1')?'selected':''?>>Created</option>	
				<option value="2" <?=($po_type_status=='2')?'selected':''?>>Request</option>	
				<!--<option value="3" <?=($po_type_status=='3')?'selected':''?>>Cancel</option>	--
			</select>
		</div>	
	</div>	-->				
					
				<div class="col-md-12" style="margin-top:10px;">
					<div class="col-md-6">
					 <div class="form-group">
					  <label class="col-md-4 control-label">Select Vendor</label>
						<div class="col-md-6 col-xs-11">
							<select class="select2" name="vender_id" id="vender_id" onChange="load_consignee(this.value);" required title="Select Vender">
								<option value="">Choose Vendor</option>
								<?=getvender($dbcon,$rel['vender_id'],'vendor_cat=1');?>	
							</select>
						</div>
						<input type="button"  name="addcust" id="addcust" data-toggle="modal" data-target="#bs-example-modal-lg"  class="btn btn-primary" value="+"/>
					 </div>	
					</div>	
					<div class="col-md-6">
					 <div class="form-group">  	
						<?php 
							$ck='';
							if(empty($rel['consignee_id'])){
								$ck='checked="checked"';
							}
						?>
					
						<label class="col-md-3 control-label" >
							<input id="same_as" name="same_as" type="checkbox" class="" title="Other Name"  <?=$ck?> value="1" style="width:15px;height:25px;" onChange="consinee_change(this.checked);"> 
							Same Consignee
						</label>
						<div class="col-md-5 col-xs-11" id="consignee" style="<?php if(empty($rel['consignee_id'])){ echo "display:none;"; } ?>">
							<select class="select2" name="consignee_id" id="consignee_id">
								<?=get_custmer_consignee($dbcon,$rel['vender_id'],$rel['consignee_id'])?>
							</select>
						</div>
					 </div>	
					</div>
				</div>
				<div class="col-md-12" style="margin-top:10px;">
					<div class="col-md-6">
					 <div class="form-group">
					  <label class="col-md-4 control-label">Purchase Order No </label>
					  <div class="col-md-6 col-xs-11">
						<input id="purchaseorder_no" name="purchaseorder_no" type="text" class="form-control" title="Date" value="<?=$rel['purchaseorder_no']?>" placeholder="Purchase Order No" >
						</div>
					 </div>	
					</div>	
					<div class="col-md-6">
					 <div class="form-group">  	
					  <label class="col-md-3 control-label" >Purchase Order date </label>
						<div class="col-md-5 col-xs-11">
							<input id="purchaseorder_date" name="purchaseorder_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$purchaseorder_date?>" placeholder="Purchase Order Date">
						</div>
					 </div>	
					</div>
				</div>
				<div class="col-md-12">
						<div class="col-md-6">
						 <div class="form-group">
						  <label class="col-md-4 control-label">Mode of Dispatch</label>
							<div class="col-md-6 col-xs-11">
								<!--<input type="text" id="mode_of_dispatch" name="mode_of_dispatch" class="form-control" title="Mode of Dispatch" value="<?=$rel['mode_of_dispatch']?>" placeholder="Mode of Dispatch" >-->
								<select style="padding-right: 0px;" class="form-control" name="dispatch_doc_no" id="dispatch_doc_no" >
									<?=getmodeofdispache($dbcon,$rel['mode_of_dispatch']);?>
								</select>
								
							</div>
							<input type="button" name="addproduct4" id="addproduct4" data-toggle="modal" data-target="#bs-dispatch-modal" class="btn btn-primary" value="+"/>
						 </div>	
						</div>	
						<div class="col-md-6">
						 <div class="form-group">  	
						  <label class="col-md-3 control-label" >Payment Terms</label>
							<div class="col-md-5 col-xs-11">
								<!--<input type="text" id="payment_terms" name="payment_terms" class="form-control" title="Payment Terms" value="<?=$rel['payment_terms']?>" placeholder="Payment Terms">-->
								<select style="padding-right: 0px;" class="form-control" name="payment_terms" id="payment_terms" >
									<?=getpaymentterms($dbcon,$rel['payment_terms']);?>
								</select>
							</div>
							<input type="button" name="addproduct2" id="addproduct2" data-toggle="modal" data-target="#bs-payterms-modal-lg" class="btn btn-primary" value="+"/>
						 </div>	
						</div>
				</div>	
				

				
				<div class="col-md-12" style="margin-top:10px;">
							
				</div>	
						
					 		 	
				<div class="col-md-12" style="margin-top:10px;">
							 				 	
				<div class="form-group">
					<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
						<tr id="field" >
							<th width="4%" class="text-center">Type</th>
							<th width="25%" class="text-center">Product</th>
							<th width="7%" class="text-center">HSN Code</th>
							<th width="7%" class="text-center">Quantity</th>
							<!--<th width="7%" class="text-center">Sqr/Ft</th>-->
							<th width="7%" class="text-center">Rate</th>
							<th width="7%" class="text-center">Per</th>
							<!--<th width="6%">Discount</th>-->
							<th width="9%">Taxable Value</th>
							<th width="15%">Tax</th>
							<th width="9%" class="text-center">Amount</th>
							<th width="5%" class="text-center"></th>
						</tr>
					<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
					<tr id="field1">
						
						<td style="vertical-align:top;">
							<select class="select2" name="product_type" id="product_type" onChange="load_product(this.value);" title="Select Product Type">
								<?=getproducttype($dbcon,'');?>
							</select>
						</td>
						<td style="vertical-align:top;">
							<div>
							<select class="select2" title="Select product" name="product_id" id="product_id" onChange="load_productdetail(this.value);load_product_tax(this.value,'purchase')">
								<option value="">Choose Product</option>
								<?=getproduct($dbcon,0,'0,1,2,4')?>
							</select>
							<input type="button"  name="addproduct" id="addproduct" data-toggle="modal" data-target="#bs-example-modal-addproduct"  class="btn btn-primary" value="+"/>
							</div>
							<br/>
							<textarea id="product_des" name="product_des" class="form-control" ></textarea>
						</td>	
						<td style="vertical-align:top;">
							<input type="text" title="Enter HSN Code" id="product_hsn_code" name="product_hsn_code" class="form-control"/>
						</td>
						<td style="vertical-align:top;">
							<input type="number"  title="Enter Qty" min="0" id="product_qty" name="product_qty"  class="form-control" onkeyup="get_amount();"/>
						</td>
						<!--<td style="vertical-align:top;">
							<input type="number" title="Enter Sqr/Ft" min="0" id="sqr_ft" name="sqr_ft" onkeyup="get_amount();" class="form-control"/>
						</td>-->
						<td style="vertical-align:top;">
							<input type="number"  title="Enter Rate" min="0" id="product_rate" name="product_rate" onkeyup="get_amount();" class="form-control"/>
						</td>
						<td style="vertical-align:top;">
							<select class="select2"  name="unitid" id="unitid"  title="Select Unit">
								<?=getunit($dbcon,0);?>
							</select>
						</td>
						
						<!--<td style="vertical-align:top;">
							<input type="number" title="Enter Discount" min="0" id="product_discount" name="product_discount" onkeyup="get_discount('amt');" class="form-control" placeholder="in Rs."/><br/>
							<input type="number"  title="Enter Discount Percentage" min="0" id="discount_per" name="discount_per" onkeyup="get_discount('per');" class="form-control" placeholder="in %" max="100"/>
						</td>-->
						<td style="vertical-align:top;">
							<input type="number" title="Taxable Value" min="0" id="taxable_value" name="taxable_value" class="form-control" readonly/>
						</td>
						<td style="vertical-align:top;">

							<textarea  name="sel_tax" id="sel_tax" class="form-control" readonly></textarea>
							<input type="text" name="formulaid" id="formulaid" class="form-control" readonly />
							<input type="text" name="formula_tax_id" id="formula_tax_id" class="form-control" readonly />
							<input type="text" name="product_amount_tax" id="product_amount_tax" class="form-control" readonly />
						</td>
						
						<td style="vertical-align:top;"> 
							<input type="number"  min="0" id="product_amount" readonly="readonly" name="product_amount" class="form-control" onmouseover="this.title=this.value"/>
						</td>
						<td width="5%">
							<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
						</td>
							<input type='hidden' name='edit_id' id='edit_id' value='' />
					
						</tr>
					</table>
						</div>
                             </div>
	<div id="sale_productdata">
				<?php if($mode=="Edit"){
					
					$query="select purchaseordertrn_id,product_hsn_code,product.product_name,cat.unit_name,product.product_name,mst.description,mst.*,product_qty,product_rate,product_disc,product_amount from  tbl_purchaseordertrn as mst left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id  where purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id'];
					$result=$dbcon->query($query);
			
				?>
				<div class="form-group">
					  <div class="col-md-12 col-xs-11">
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
				<tr id="field">
					<th width="10%" class="text-center">Type</th>
					<th class="text-center" width="25%">Product Name</th>
					<th class="text-center" width="8%">HSN Code</th>
					<th class="text-center" width="8%">Qty</th>
					<th class="text-center" width="10%">Rate</th>
					<th class="text-center" width="6%">Per</th>
					<!--<th class="text-center" width="8%">Discount</th>-->
					<th class="text-center" width="10%">Taxable value</th>
					<th class="text-center" width="15%">Tax</th>
					<th class="text-center" width="12%">Amount</th>
					<th class="text-center" width="10%">Action</th>
						 	 
				</tr>
		<?php 
			$i=1;
			while($rel_trn=mysqli_fetch_assoc($result))
			{
		?>
				<tr>
					<td style="vertical-align:top;">
						<?=get_pro_type_name($rel_trn['product_type'])?>
					</td>
					<td style="vertical-align:top;">
						<?=$rel_trn['product_name']?>
						<?=(!empty($rel_trn['description'])?'<br/><strong>Desc.</strong> :'.$rel_trn['description']:'')?>
					</td>
					<td style="vertical-align:top;" class="text-center">
						<?=$rel_trn['product_hsn_code']?>
					</td>
					<td style="vertical-align:top;" class="text-center">
						<?=$rel_trn['product_qty']?>
					</td>	
					<td style="vertical-align:top;" class="text-right">
						<?=$rel_trn['product_rate']?>
					</td>				
					<td style="vertical-align:top" class="text-center">
						<?=$rel_trn['unit_name']?>
					</td>
					<!--<td style="vertical-align:top" class="text-left">
						<?php //=$rel_trn['product_discount'].'('.$rel_trn['discount_per'].'%)';?>
					</td>-->
					<td style="vertical-align:top" class="text-right">
						<?=$rel_trn['product_amount']?>
					</td>
					<td style="vertical-align:top" class="text-left">
						<?=(empty($rel_trn['tax_name1']) ? "" : $rel_trn['tax_name1'].' : '.$rel_trn['tax_amount1']).'<br/>
						'.(empty($rel_trn['tax_name2']) ? "" : $rel_trn['tax_name2'].' : '.$rel_trn['tax_amount2']).'<br/>
						'.(empty($rel_trn['tax_name3']) ? "" : $rel_trn['tax_name3'].' : '.$rel_trn['tax_amount3']).'<br/>';
						?>
						
					</td>
					<td style="vertical-align:top" class="text-right">
						<?=$rel_trn['total']?>
					</td>
					<input type="hidden" name="amount[]" id="amount<?=$i?>" value="<?=$rel_trn['total']?>"/>
											
					 <td style="vertical-align:top">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data(<?=$rel_trn['purchaseordertrn_id']?>,'tbl_purchaseordertrn','purchaseordertrn_id');"  ><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data(<?=$rel_trn['purchaseordertrn_id']?>,'tbl_purchaseordertrn','purchaseordertrn_id');" ><i class="fa fa-times"></i></button>
					</td>	
			</tr>
			<?php 
			$i++;
			}?>
			</table>
			</div>
                           
							</div>	
			<?php }?>
			
							 </div>	
					 <div class="col-md-6">
							
							<div class="form-group">
							  <label class="col-md-3 control-label">Remarks </label>
									<div class="col-md-9 col-xs-11">
									<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
								</div>
                             </div> 
					</div>
					 <div class="col-md-6">
							<div class="form-group">
								<label class="col-md-6 control-label">Total *</label>
								<div class="col-md-4 col-xs-11">
									<input id="total" name="total" type="text" readonly="readonly" class="form-control" title="dispatch_no" max="0"  value="<?php if($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;}?>" placeholder="total">
					
								</div>
							</div>	
							<div class="form-group">
								<label class="col-md-6 control-label">Transport charges </label>
								<div class="col-md-4 col-xs-11">
								<input id="paking" name="paking" type="number"  min="0"  class="form-control" title="Transport" value="<?php if($mode=="Add"){echo 0;}else if($mode="Edit"){echo $rel['packing'];}?>" onKeyUp="get_amount();" placeholder="Transport">
					
								</div>
							</div>	
							<!-- 
							<div class="form-group">
								<label class="col-md-6 control-label">Select Formula</label>
								<div class="col-md-4 col-xs-11">
								<select class="form-control" name="formulaid" id="formulaid" onChange="get_gtotal(this.value);">
									<?php 
									echo getformula($dbcon,$rel['formulaid']);
									 ?>
								</select>
								</div>
							</div>							
							<div class="col-md-12">
							<div id="showformulatextbox">
							<?php 
							if($mode=='Edit')
							{
							if(!empty($rel['tax1_name']))
							{
							?>
							<div class="form-group">
								<label class="col-md-6 control-label" ><?=$rel['tax1_name']?></label>
								<div class="col-md-4 col-xs-11" style="padding-right:5px;">
								<input id="taxvalue0" name="taxvalue0" value= "<?=$rel['taxvalue1']?>"type="text" class="form-control" readonly="readonly">
						</div>
					</div>
					<input id="taxname0" name="taxname0" value= "<?=$rel['tax1_name']?>" type="hidden" class="form-control">
							<?php 
							}
							if(!empty($rel['tax2_name']))
							{
							?>
							<div class="form-group">
								<label class="col-md-6 control-label" ><?=$rel['tax2_name']?></label>
								<div class="col-md-4 col-xs-11" style="padding-right:5px;">
								<input id="taxvalue1" name="taxvalue1" value= "<?=$rel['taxvalue2']?>"type="text" class="form-control" readonly="readonly">
						</div>
					</div>
					<input id="taxname1" name="taxname1" value= "<?=$rel['tax2_name']?>" type="hidden" class="form-control">
							<?php 
							}if(!empty($rel['tax3_name']))
							{
							?>
							<div class="form-group">
								<label class="col-md-6 control-label" ><?=$rel['tax3_name']?></label>
								<div class="col-md-4 col-xs-11" style="padding-right:5px;">
								<input id="taxvalue2" name="taxvalue2" value= "<?=$rel['taxvalue3']?>"type="text" class="form-control" readonly="readonly">
						</div>
					</div>
					<input id="taxname2" name="taxname2" value= "<?=$rel['tax3_name']?>" type="hidden" class="form-control">
							<?php 
							}} 
							?>
							</div>
							</div>
							-->
							<div class="form-group">
								<label class="col-md-6 control-label">Round Off</label>
								<div class="col-md-4 col-xs-11">
								<input id="round_off" name="round_off" type="number" class="form-control" title="Round Off" value="<?php if($mode=="Add"){echo 0;}else if($mode="Edit"){echo $rel['round_off'];}?>" onKeyUp="get_amount();" placeholder="Round Off">
					
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-md-6 control-label">Grand Total *</label>
								<div class="col-md-4 col-xs-11">
								
								<input id="g_total" name="g_total" type="text"  class="form-control" title="total" value="<?php if($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $rel['g_total'];}?>" placeholder="total"readonly="readonly">
							<!--<input id="total" name="total" type="hidden" value="<?php if($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;} ?>" placeholder="total"readonly="readonly">-->
							
								</div>
							</div>
							
							</div>	
					</div>
							<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
							
							<a href="<?=ROOT.'po_list'?>" type="button" class="btn btn-danger">Cancel</a>
							<div class="col-md-3"></div>					
			</div>
							<!--Vendor row end-->	
		<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
		<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
		<input type='hidden' name='eid' id='eid' value='<?=($mode=='Edit')?$rel['purchaseorder_id']:''?>' />	
		<?php 
			if($direct_add=='1'){
		?>		
			<input type="hidden" name="po_ref_id" id="po_ref_id" value="<?=$rel['purchaseorder_id']?>" />
		<?php 	} ?>	
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
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/po.js"></script>
	<!--<script src="<?=ROOT?>js/app/vender.js"></script>-->
	<script src="<?=ROOT?>js/app/product_mst.js"></script>
	<script src="<?=ROOT?>js/app/state_mst.js"></script>
	<script src="<?=ROOT?>js/app/city_mst.js"></script>
	<script src="<?=ROOT?>js/app/payment_terms.js"></script>
	<script src="<?=ROOT?>js/app/mode_disptch.js"></script>
	 <script src="<?=ROOT?>js/app/customer.js"></script>

<!--<script src="js/count.js"></script>-->
<script>
$(".select2").select2({
	width: '100%'
});
$("#product_id").select2({
	width: '86%'
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
</script>
<?php 
echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
if($mode=="Add"){
	echo "<script>show_data();</script>";
	echo "<script>get_series_no(6);</script>";
}
if($direct_add=='1'){
	echo "<script>entry_po_req_data(".$rel['purchaseorder_id'].");</script>";
	echo "<script>$('#vender_id').select2('readonly', true);
			$('#po_type_status').attr('style','pointer-events: none;').attr('readonly','readonly');
			get_series_no(6);
	</script>";
}
else if($direct_add=='2')
{
	echo "<script>entry_po_req_data(".$rel['purchaseorder_id'].");</script>";
}
?>
</body>
</html>
