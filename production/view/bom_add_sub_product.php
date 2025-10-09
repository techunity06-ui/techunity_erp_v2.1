<?php 
	session_start();
	include('../include/urlfile.php');	
	$form="BOM";
	
	$p_name=$_GET['id'];
	
	$sel_bom="select * from tbl_bomtrn where bom_trn_id=".$p_name;
	$r_bom=mysqli_fetch_assoc($dbcon->query($sel_bom));	

	$bom_level=$r_bom['bom_level'];
	$p_type=$r_bom['product_type'];
	$sp_name=$r_bom['product_id'];
	$level=$r_bom['bom_level'];
	$base_qty=$r_bom['product_base_qty'];
	$bom_id=$r_bom['bom_id'];
	$sub_pqty=$r_bom['product_qty'];
	$main_product=$r_bom['sale_product_id'];
	
	/*$p_type=$_GET['p_type'];
	$p_grp=$_GET['p_grp'];
	$sp_name=$_GET['sp_name'];
	$level=$_GET['level'];
	$base_qty=$_GET['base_qty'];
	$bom_id=$_GET['eid'];
	$sub_pqty=$_GET['sub_pqty'];
	$main_product=$_GET['main_product']; */
	
	//array_push($_SESSION['sitemap'],$sp_name);
	//$sitemap=array_unique($_SESSION['sitemap']);
	
	$new_level=$level+1;
	
	
//	echo $sub_pqty;
//	print_r($sitemap);
	
$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));	


//echo $r_bom['bom_level'];


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>BOM ADD SUB PRODUCT</title>
<?php include_once($include.'include_css_file.php');?>
<style>
	.error {
				font-weight: bold;
				color: #ef1717;
				
				font-size: 16px;
			}
			 #process_left,#process_right{
   margin: 5px;
    border: 1px solid #cccccc;
    list-style: none;
    padding-left: 0;
    height: 200px;
    overflow: auto;
    /* width: 250px; */
    border-radius: 5px;
  }
.mb-5{
	margin-bottom: 5px;
}
  ul li{
    cursor: pointer;
    padding: 5px 10px;
  }


  .selected{
    background-color: blue;
    color: white;
     margin: 2px;
  }

  .bigBtn{
    height: 50px;
    width: 55px;
    margin-top: 35px;
    margin-left: -5px;
    font-size: 20px;
    font-weight: 900;
  }
<?phpif(!$bom_to_po_req){ ?>
.po_req_mode{
	display:none;
}
<?}?>

<?php
if($bom_actual_add){
	echo ".hide_act_add{display:none;}";
}
else{
	echo ".show_act_add{display:none;}";
}
?>
</style>

</head>
<body>
<section id="container" class="sidebar-closed">
  <?php include_once($include.'include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once($include.'left_menu.php');?>
      <!--sidebar end-->
      <!--main content start-->
          <section id="main-content">
          <section class="wrapper">
		   
			<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
							<h3> <?=$mode.' '.$form?></h3>
						</header>	
						<div class="">
							<ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.PRODUCTION_ROOT.'bom_list'?>"><?=$form?> List</a></li>
							  <li><a href="<?php if($bom_id=='0') { echo ROOT.PRODUCTION_ROOT.'bom_add/'.$main_product; } else { echo ROOT.PRODUCTION_ROOT.'bom_edit/'.$bom_id;  } ?>"><?=get_pro_field($dbcon,$r_bom['sale_product_id'],'product_name'); ?></a></li>
							  <?php 
								
								generate_sitemap($dbcon,$r_bom['bom_trn_id']);
							  ?>
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
					  <?=$form?> For <strong><?php echo get_pro_field($dbcon,$sp_name,'product_name'); ?></strong>
					</header>	
				<div class="panel-body">
	<form class="form-horizontal" role="form" id="bom_add" action="javascript:;" method="post" name="bom_add">
	<div class="row">
	
	
	<input type="hidden" name="" id="product_type_get" value="<?=$p_type;?>" />
	<input type="hidden" name="" id="sel_product_id" value="<?=$sp_name;?>" />
	<input type="hidden" name="" id="main_product" value="<?=$main_product;?>" />
	
	<div class="col-md-12">
		<div class="col-md-6">
			<div class="form-group">
				<label class="col-md-4 control-label">Product Name *</label>
				<div class="col-md-6 col-xs-11">
					<input id="" name="" type="text" class="form-control" value="<?=get_pro_field($dbcon,$sp_name,'product_name');?>" readonly  required />
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
			  <label class="col-md-4 control-label">Quantity*</label>
				<div class="col-md-4 col-xs-11">
					<input type="number"  title="Enter Qty" min="0" id="sel_product_qty" name="sel_product_qty"  class="form-control" value="<?=$sub_pqty?>" readonly  />
				</div>
				
			</div>
		</div>
	</div>
	
	
	<div class="col-md-9 col-md-offset-2">
	<div class="form-group">
	<table cellspacing="10" style="border-collapse:inherit;" id="product_list" class="display table table-bordered table-striped">
		<tr id="field">
			<th width="20%" class="text-center">Type</th>
			<th width="30%" class="text-center">Product Detail</th>
			<th width="10%" class="text-center hide_act_add">Unit</th>
			<th width="20%" class="text-center hide_act_add">UOM</th>
			<th width="10%" class="text-center hide_act_add">Quantity</th>
			<th width="20%" class="text-center hide_act_add">ACtual Qty.</th>
			<th width="10%" class="text-center"></th>
		</tr>
	<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
	<tr id="field1">
		
		<td style="vertical-align:top;">
			<select class="select2" name="product_type" id="product_type" onChange="check_product_process_required(this.value);" title="Select Product Type">
				<?=get_bom_producttype($dbcon,'');?>
			</select>

			<input type="hidden" id="is_process_required" value="">
		</td>
		<td style="vertical-align:top;">
			<select class="select2" title="Select product" name="product_id" id="product_id" onchange="load_product_detail(this.value);" >
				<option value="">Choose Product</option>
				<?//=getproduct($dbcon,0,'0,1,2,4')?>
			</select>
			<br/><br/>
			<div id="get_spec_div" style="display:none">
				Width : <input type="text" class="form-control" name="product_width" id="product_width" value="<?=$mode=='Edit'?$rel['product_width']:0?>" onkeyup="get_ms_kg()" />
				
				Height : <input type="text" class="form-control" name="product_height" id="product_height" value="<?=$mode=='Edit'?$rel['product_height']:0?>" onkeyup="get_ms_kg()" />
				
				Thickness : <input type="text" class="form-control" name="product_thickness" id="product_thickness" value="<?=$mode=='Edit'?$rel['product_thickness']:0?>" onkeyup="get_ms_kg()" />
				
				<input type="hidden" class="form-control" name="product_density" id="product_density" value="<?=$mode=='Edit'?$rel['product_density']:0?>" onkeyup="get_ms_kg()" />
				
				Kg : <input type="text" class="form-control" name="product_kg" id="product_kg" value="<?=$mode=='Edit'?$rel['product_kg']:0?>" readonly /> 
				
				<input type="checkbox" name="set_kg" id="set_kg" value="0" onclick="set_kg_to_qty(this.value)" />SET
			</div>
		</td>	
		
		<td style="vertical-align:top;" class="hide_act_add">
			<select class="form-control" id="product_base_unit" name="product_base_unit" >
				<option value="">--select Unit--</option>
				<?=getunit($dbcon);?>
			</select>
		</td>	
		<td style="vertical-align:top;" class="hide_act_add">
			<select class="form-control" id="product_uom" name="product_uom" >
				<option value="">--select UOM--</option>
				<?=getunit($dbcon);?>
			</select>
		</td>
		<td style="vertical-align:top;" class="hide_act_add">
			<input type="number"  title="Enter Qty" min="0" id="product_act_qty" name="product_act_qty" onkeyup="get_conv_qty_bom()"  class="form-control" />
		</td>
		<td style="vertical-align:top;" class="hide_act_add">
			<input type="number"  title="Enter Qty" min="0" id="product_qty" name="product_qty"  class="form-control" onkeyup="get_convert_qty()" />
			
			<input type="hidden"  title="" id="product_spec_hid" name="product_spec_hid"  class="form-control" />
			<input type="hidden"  title="" id="product_spec_hid_qty" name="product_spec_hid_qty"  class="form-control" />
			<input type="hidden"  title="" id="product_spec_act_qty" name="product_spec_act_qty"  class="form-control" />
		</td>	
		
		<td style="vertical-align:top;">
			<input type="button"  name="addrow" id="addrow" onClick="return add_field();" class="btn btn-primary" value="Add"/>
		</td>
		<input type='hidden' name='edit_id' id='edit_id' value="" />
		<input type='hidden' name='mode_edit' id='mode_edit' value="<?=$mode?>" />
		<input type='hidden' name='mode_edit_id' id='mode_edit_id' value="<?=$rel['bom_id']?>" />
		<input type='hidden' name='actual_qty' id='actual_qty' value="<?=$sub_pqty;?>" />
		<input type='hidden' name='thread' id='thread' value="<?php echo $thread+1; ?>" />
		<input type='hidden' name='level' id='level' value="<?php echo $new_level; ?>" />
		<input type='hidden' name='parent_id' id='parent_id' value="<?php echo $p_name; ?>" />
	</tr>
</table>			
		
</div>
</div>
<div class="col-md-8 col-md-offset-3">
	<div class="form-group">
		<div class="col-md-4">
			<input type="text" class="form-control" id="fil_product_search" placeholder="Search Product Name" value="" />
		</div>
	</div>
</div>

			<div id="bom_productdata"></div>
		
		
</div>
		</div><!--Vendor row end-->	
		<input type='hidden' name='mode' id='mode' value='<?=$bom_id!='0'?'edit':''?>' />
		<input type="hidden" name="eid" id="eid" value="<?=$bom_id!=''?$bom_id:''?>" />
		<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
		<input type="hidden" name="save_print" id="save_print" value="" />			
		<?phpif($bom_actual_add){
			//Entry Same Actual Quantity if first time 
			$upd_act_qty=$dbcon->query("update tbl_bomtrn as trn
			inner join tbl_bom as mst on mst.bom_id=trn.bom_id
			set trn.product_actual_qty=trn.product_qty  where mst.bom_id=".$rel['bom_id']." and bom_actual_add_status=0");
		?>	
			<input type='hidden' name='bom_actual_add' id='bom_actual_add' value='1' />		
				
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
	
	<?php include_once($include.'footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	<?php include_once($include.'add_bom_grp.php');?>  
	<?php include_once($include.'allocate_process_model.php');?> 	
	<?php include_once($include1.'bom_in_used_modal.php');?>   
	<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/bom.js?<?php echo time(); ?>"></script>
    
	 
<script>
$(".select2").select2({
	width: '100%'
});	
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});

<?
if($mode!='Add'){
?>	
$('#sales_order_id').select2('readonly',true);
$('#sales_order_pro_id').select2('readonly',true);
<?}
?>
<?phpif($direct_add){?>
load_sales_pro_data(<?=$rel['sales_order_id']?>);
$('#sales_order_id').select2('readonly',true);
<?
$deleteid=delete_record('tbl_bomtrn', "bom_id=0", $dbcon);
}
?>

</script>
<?php
if($mode=="Add"){
	echo "<script>get_series_no()</script>";
} 

if($readonly=='yes'){
	echo "<script>$('#sel_product_id').select2('readonly', true);</script>";
}
?>
</body>
</html>