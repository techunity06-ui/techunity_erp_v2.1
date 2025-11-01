<?php 

	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../include/function_database_query.php");
	$form="Purchase Bill";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	if(strpos($_SERVER['REQUEST_URI'], "purchaseedit")==false) {
		$mode="Add";
		$date=date('d-m-Y');
		$order_date='';
		$deleteid=delete_record('tbl_potrancation',"potrancation_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
	}
	else {
		$mode="Edit";
		$poid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_pono where po_id=$poid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$order_date='';
		if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00"){
			$order_date=date('d-m-Y',strtotime($rel['order_date']));
		}
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
								  <li><a href="<?=ROOT.'purchase_list'?>"><?=$form?> List</a></li>
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
				<form class="form-horizontal" role="form" id="po_add" action="javascript:;" method="post" name="po_add">
						<div class="row">
					
				<div class="col-md-12 text-center" style="margin-top:10px;">
					<div class="col-md-4 col-md-offset-3">
						<div class="form-group">
							<label class="col-md-6 control-label">
								<input type="radio" id="purchase_type_grn" name="purchase_type" style="height: 18px;width: 18px;" value="1" <?=($rel['purchase_type']!='2')?'checked':''?> >
							<strong>G.R.N.</strong></label>
							<label class="col-md-6 control-label">
								<input type="radio" id="purchase_type_direct" name="purchase_type" style="height: 18px;width: 18px;" value="2" <?=($rel['purchase_type']=='2')?'checked':''?> >
							<strong>Direct</strong></label>
						</div>		
					</div>		
				</div>
				<div class="col-md-12"  style="margin-top:10px;">
					<div class="col-md-6">
						<div class="form-group">
						  <label class="col-md-4 control-label">Purchase Series No </label>
						  <div class="col-md-6 col-xs-11">
							<input id="po_no" name="po_no" type="text" class="form-control" title="Date" value="<?=$rel['po_no']?>" placeholder="Purchase No" readonly >
							</div>
						 </div>	
					</div>	
					<div class="col-md-6">  	
						 <div class="form-group">  	
						  <label class="col-md-4 control-label" >Purchase Bill date </label>
						  <div class="col-md-6 col-xs-11">
							<input id="po_date" name="po_date" type="text" class="form-control default-date-picker" title="Date" value="<?php if($mode=='Add'){ echo $date;}else if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['po_date']));}?>" placeholder="Purchase Date">
							</div>
						 </div>	
					</div>	
				</div>	
				<div class="col-md-12">
					<div class="col-md-6">
						<label class="col-md-4 control-label" style="">Select Vendor </label>
						<div class="col-md-6 col-xs-11" style="padding-left:6px">
							<select class="select2" name="vender_id" id="vender_id" required title="Select Vender" onChange="load_ven_grn(this.value);">
								<?=getcust($dbcon,$rel['vender_id']);?>	
							</select>
						</div>
						
					</div>
					
					<div class="col-md-6">
						<label class="col-md-4 control-label" style="">Bill No.</label>
						<div class="col-md-6">
							<input type="text" class="form-control" id="order_no" name="order_no" title="Enter Bill No."  placeholder="Bill No." value="<?=$rel['order_no']?>" required >
						</div>
					</div>
					<!--<div class="col-md-6">
						<label class="col-md-4 control-label" style="">Purchase Order</label>
						<div class="col-md-6 col-xs-11" style="padding-left:6px">
							<select class="select2" name="trn_purchaseorder_id_up" id="trn_purchaseorder_id_up" onChange="load_purhcase_order_data(this.value);load_product_tax(this.value,'purchase')" >
								<option value="">Choose Purchase Order</option>	
							</select>
						</div>
					</div>-->
				</div>
				<div class="clearfix"></div>
				 	
					<!--<div class="col-md-12">
						<div class="form-group" id="purchase_order_div" style="display:none;">
							<label class="col-md-2 control-label">Choose Purchase Order</label>
							<div class="col-md-3 col-xs-11">
								<select class="select2" name="purchaseorder_id" id="purchaseorder_id" onChange="load_purhcase_order_data(this.value)" >
									<option value="">Choose Purchase Order</option>	
								</select>
							</div>
						</div>		
					</div>	-->
			<div class="col-md-12" style="margin-top:10px;">
							 				 	
				<div class="form-group">
					<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
						<tr id="field" >
							<th width="4%" class="text-center">GRN</th>
							<th width="20%" class="text-center">Product</th>
							<th width="6%" class="text-center">Quantity</th>
							<th width="6%" class="text-center">Rate</th>
							<th width="6%" class="text-center">Per</th>
							<th width="6%">Discount</th>
							<th width="9%">Taxable Value</th>
							<th width="15%">Tax</th>
							<th width="9%" class="text-center">Amount</th>
							<th width="5%" class="text-center"></th>
						</tr>
					<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
					<tr id="field1">
						<td style="vertical-align:top;">
							<select class="select2" name="grn_id" id="grn_id" onChange="load_grn_data(this.value);">
								<?=get_grn_for_purchase($dbcon,$rel['vender_id'],"",$mode);?>
							</select>
						</td>
						<td style="vertical-align:top;">
							<select class="select2" title="Select product" name="product_id" id="product_id" onChange="load_productdetail(this.value);load_product_tax(this.value,'purchase')">
								<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
							</select>
							<!--<input type="button" name="addproduct" id="addproduct" data-toggle="modal" data-target="#bs-example-modal-addproduct" class="btn btn-primary" value="+"/>-->
							<br/><br/>
							<textarea id="product_des" name="product_des" class="form-control" ></textarea>
						</td>	
						<td style="vertical-align:top;">
							<input type="number" min="0" id="product_qty" name="product_qty"  class="form-control" onkeyup="get_amount();"/>
						</td>
						<td style="vertical-align:top;">
							<input type="number"  title="Enter Rate" min="0" id="product_rate" name="product_rate" onkeyup="get_amount();" class="form-control"/><br/>
							<!--<button type="button" title="Show Previous Rate History" name="rate_history" id="rate_history" onclick="load_rate_hist()" style="display:none;" class="btn btn-info"><i class="fa fa-eye"></i> show</button>-->
						</td>
						<td style="vertical-align:top;">
							<select class="select2"  name="unitid" id="unitid"  title="Select Unit">
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
							<textarea  name="sel_tax" id="sel_tax" class="form-control" readonly></textarea>
							<input type="hidden" name="formulaid" id="formulaid" class="form-control" readonly />
							<input type="hidden" name="formula_tax_id" id="formula_tax_id" class="form-control" readonly />
							<input type="hidden" name="product_amount_tax" id="product_amount_tax" class="form-control" readonly />
						</td>
						<td style="vertical-align:top;"> 
							<input type="number" min="0" id="product_amount" readonly="readonly" name="product_amount" class="form-control"/>
						</td>
						<td width="5%">
							<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
						</td>
						<input type='hidden' name='edit_id' id='edit_id' value='' />
					   </tr>
					</table>
						</div>
                             </div>
	<div id="sale_productdata"></div>	
	
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
								<div class="col-md-12 col-md-offset-3">
									<div class="col-md-3 col-xs-11">
										<select class="form-control"  id="ename">
											<?=get_all_expense($dbcon,'');?>
										</select>
									</div>
									<div class="col-md-3 col-xs-11"><input type="text" class="form-control" id="eamount"   placeholder="Amount"></div>
									<div class="col-md-3 col-xs-11"><input type="button" class="add-row btn btn-success" value="+"></div>
								</div>
								
								<div  class="col-md-8 col-md-offset-3">
									 <table class="table table-borederd" id="etable">
										<thead>
											<tr>
												<th>Select</th>
												<th>Expense</th>
												<th>Amount</th>
											</tr>
										</thead>
										<tbody>
											<?php if($mode=="Edit") { 
												
												$querye="select * from tbl_purchase_exp where exp_in_id='".$rel['po_id']."' and exp_e_amount!='0'";
												$rele=$dbcon->query($querye);
												$ecount=mysqli_num_rows($rele);
												$counte=1;
												while($rowe=mysqli_fetch_array($rele))
												{
												?>
												
												<tr>
													<td><input type='checkbox' name='record'></td>
													<td>
														<span id='ncnt<?php echo $counte; ?>'></span>
														
														<?php echo get_expense_by_id($dbcon,$rowe['exp_e_name']); ?>
														<input type='hidden' name='ename_a[]' value='<?php echo $rowe['exp_e_name'] ?>' class='ex_name' />
													</td>
													<td>
														<?php echo $rowe['exp_e_amount'] ?>
														<input type='hidden' name='eamount_a[]' value='<?php echo $rowe['exp_e_amount'] ?>'  class='ex_amount' />
													</td>
												</tr>
												
												<?php
												$counte++;
												}
												
											} ?>
										</tbody>
										
									</table>
									<button type="button" class="delete-row btn btn-danger">Delete Row</button>
									
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
								<label class="col-md-6 control-label"> Total  Expense *</label>
								<div class="col-md-4 col-xs-11">
								
								<input id="exp_total" name="exp_total" type="text"  class="form-control" title="total" value="<?php if($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $rel['exp_total'];}?>" placeholder="total"readonly="readonly">
							<!--<input id="total" name="total" type="hidden" value="<?php if($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;} ?>" placeholder="total"readonly="readonly">-->
							
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
				<div class="col-md-12 text-center">
					<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
					<a href="<?=ROOT.'purchase_list'?>" type="button" class="btn btn-danger">Cancel</a>
				</div>			
				</div>
				<!--Vendor row end-->	
				<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
				<input type='hidden' name='eid' id='eid' value='<?=$rel['po_id']?>' />
				<input type="hidden"  name="row_cnt" id="row_cnt" value="<?=($mode=='Edit')?$ecount:'0'?>" >
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
	<?php include_once('../include/include_show_purchase_history.php'); ?>
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   

	<script src="<?=ROOT?>js/app/purchase.js?<?=time()?>"></script>

<script>
//$('#container').addClass('sidebar-closed');
$(".select2").select2({
	width: '100%'
});
/* $("#product_id").select2({
	width: '83%'
}); */
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
<?php if($mode=='Add'){?>
	load_purchase_srs_no();
<?php }?>
</script>
<script type="text/javascript">
	$(".add-row").click(function(){
		var count =$('#row_cnt').val();
		var name = $("#ename").val();
		var amount = $("#eamount").val();
		var new_cnt=Number(count)+1;
		//alert(new_cnt);
		$('#row_cnt').val(new_cnt);
		get_expense_name(new_cnt,name);
		var markup = "<tr><td><input type='checkbox' name='record'></td><td><span id='ncnt"+new_cnt+"'></span><input type='hidden' name='ename_a[]' value='"+name+"' class='ex_name' /><input type='hidden' name='eamount_a[]' value='"+amount+"'  class='ex_amount' /></td><td>" + amount + "</td></tr>";
		$("#etable tbody").append(markup);
		get_final_total();
		//alert($('#row_cnt').val(Number(count)+1));
	});
	
	// Find and remove selected table rows
	$(".delete-row").click(function(){
		$("#etable tbody").find('input[name="record"]').each(function(){
			if($(this).is(":checked")){
				$(this).parents("tr").remove();
			}
		});
		get_final_total();
	});
        
	function get_expense_name(count,expense)
	{
		//alert(expense);
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase/',
			data: { mode : "expense_by_id",  eid : expense },
			success: function(response)
			{
				//alert(response);			
				//return response;
				$('#ncnt'+count).html(response);
			}
		});	
	}
	
	function get_final_total()
	{
		var g_total=Number($('#total').val());
		var add = 0;
		$(".ex_amount").each(function() {
			add += Number($(this).val());
			//alert(add);
		});
		//alert(add);
		var total=add+g_total;
		//alert(total);
		$('#g_total').val(total);
		$('#exp_total').val(add);
	}
</script>
</body>
</html>
