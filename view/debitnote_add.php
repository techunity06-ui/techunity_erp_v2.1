<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../include/function_database_query.php");
	$form="Debit note";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	
	if(strpos($_SERVER[REQUEST_URI], "debitnote_add_qc")==true) {
		$grn_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query_grn="select * from tbl_grn where grn_id=$grn_id";
		$rel_grn=mysqli_fetch_assoc($dbcon->query($query_grn));
		$mode="Add";
		$date=date('d-m-Y');
		$order_date='';
		$deleteid=delete_record('tbl_debitnote_trn',"debitnote_trn_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
		$vender_id=$rel_grn['vender_id'];
	}
	else if(strpos($_SERVER[REQUEST_URI], "debitnote_edit")==true) {
		$mode="Edit";
		$debitnote_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_debitnote where debitnote_id=$debitnote_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$order_date='';
		if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00"){
			$order_date=date('d-m-Y',strtotime($rel['order_date']));
		}
		$vender_id=$rel['vender_id'];
	}
	else {
		$mode="Add";
		$date=date('d-m-Y');
		$order_date='';
		$deleteid=delete_record('tbl_debitnote_trn',"debitnote_trn_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
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
							  <li><a href="<?=ROOT.'debitnote_list'?>"><?=$form?> List</a></li>
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
				<form class="form-horizontal" role="form" id="debitnote_add" action="javascript:;" method="post" name="debitnote_add">
				<div class="row">
					
				<?php if(!empty($grn_id)){?>
					<div class="col-md-12 text-center" style="margin-top:10px;">
						<center>
							<div class="form-group">
								<label class="col-md-6 control-label">
								<input type="radio" id="debitnote_type_grn" name="debitnote_type" style="height: 18px;width: 18px;" value="1" <?=($rel['debitnote_type']!='2')?'checked':''?> >
								<strong>G.R.N.</strong></label>
							</div>
						</center>
					</div>
				<?php }else{?>
				<div class="col-md-12 text-center" style="margin-top:10px;">
					<div class="col-md-4 col-md-offset-3">
						<div class="form-group">
							<label class="col-md-6 control-label">
								<input type="radio" id="debitnote_type_grn" name="debitnote_type" style="height: 18px;width: 18px;" value="1" <?=($rel['debitnote_type']!='2')?'checked':''?> onclick="showtype(this.value)">
							<strong>G.R.N.</strong></label>
							<label class="col-md-6 control-label">
								<input type="radio" id="debitnote_type_direct" name="debitnote_type" style="height: 18px;width: 18px;" value="2" <?=($rel['debitnote_type']=='2')?'checked':''?> onclick="showtype(this.value)">
							<strong>Direct</strong></label>
						</div>		
					</div>		
				</div>
				<?php } ?>
				<div class="col-md-12"  style="margin-top:10px;">
					<div class="col-md-6">
						<div class="form-group">
						  <label class="col-md-4 control-label">Debitnote Series No </label>
						  <div class="col-md-6 col-xs-11">
							<input id="debitnote_no" name="debitnote_no" type="text" class="form-control" title="Date" value="<?=$rel['debitnote_no']?>" placeholder="Debitnote No" readonly >
							</div>
						 </div>	
					</div>	
					<div class="col-md-6">  	
						 <div class="form-group">  	
						  <label class="col-md-4 control-label" >Debitnote Date</label>
						  <div class="col-md-6 col-xs-11">
							<input id="debitnote_date" name="debitnote_date" type="text" class="form-control default-date-picker required" title="Date" value="<?php if($mode=='Add'){ echo $date;}else if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['debitnote_date']));}?>" placeholder="Debitnote Date">
							</div>
						 </div>	
					</div>	
				</div>	
				<div class="col-md-12">
					<div class="col-md-6">
						<label class="col-md-4 control-label" style="">Select Vendor </label>
						<div class="col-md-6 col-xs-11" style="padding-left:6px">
							<select class="select2" name="vender_id" id="vender_id" required title="Select Vendor" onChange="load_ven_grn(this.value);">
								<?=getcust($dbcon,$vender_id);?>	
							</select>
						</div>
					</div>
					
					<div class="col-md-6">
						<label class="col-md-4 control-label" style="">Bill No.</label>
						<div class="col-md-6">
							<input type="text" class="form-control" id="debitnote_ref_no" name="debitnote_ref_no" title="Enter Bill No." placeholder="Bill No." value="<?=$rel['debitnote_ref_no']?>" required>
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
			
			<div class="col-md-12" style="margin-top:10px;">
							 				 	
				<div class="form-group">
					<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
						<tr id="field" >
							<th width="4%" class="text-center grn">GRN</th>
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
						<td style="vertical-align:top;" class="grn">
							<select class="select2" name="grn_id" id="grn_id" onChange="load_grn_data(this.value);">
								<?=get_grn_for_debitnote($dbcon,$vender_id,"",$mode);?>
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
								<label class="col-md-6 control-label">Round Off</label>
								<div class="col-md-4 col-xs-11">
									<input id="round_off" name="round_off" type="number" class="form-control" title="Round Off" value="<?php if($mode=="Add"){echo 0;}else if($mode="Edit"){echo $rel['round_off'];}?>" onKeyUp="get_amount();" placeholder="Round Off">
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-6 control-label">Grand Total *</label>
								<div class="col-md-4">
									<input id="g_total" name="g_total" type="text" class="form-control" title="total" value="<?php if($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $rel['g_total'];}?>" placeholder="total" readonly="readonly">
								</div>
							</div>
						</div>	
					</div>
				<div class="col-md-12 text-center">
					<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
					<a href="<?=ROOT.'debitnote_list'?>" type="button" class="btn btn-danger">Cancel</a>
				</div>			
				</div>
				<!--Vendor row end-->	
				<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
				<input type='hidden' name='eid' id='eid' value='<?=$rel['debitnote_id']?>' />
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
	<?php include_once('../include/include_js_file.php');?>   

<script src="<?=ROOT?>js/app/debitnote.js?<?=time()?>"></script>

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
	load_debit_srs_no();
<?php }?>
<?php if(!empty($grn_id)){ ?>
	load_ven_grn(<?=$vender_id?>,<?=$grn_id?>);
	load_grn_data(<?=$grn_id?>);
	$('#vender_id').select2('readonly',true);
<?php } ?>
</script>
</body>
</html>