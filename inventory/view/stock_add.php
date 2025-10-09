<?php 
	session_start();
	include('../include/urlfile.php');	
	$form="Opening Stock";
	if(empty($_SESSION['start'])){
		$start = date('1-m-Y');
		$end = date("d-m-Y");
	}
	else{
		$start = $_SESSION['start'];
		$end = $_SESSION['end'];
	}
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    OPENING_STOCK_LIST_SLUG_VIEW,OPENING_STOCK_LIST_SLUG_CREATE
	]);

	if(!in_array(OPENING_STOCK_LIST_SLUG_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }

 $branch_id = $_SESSION['branch_id'];
//var_dump($_SESSION);
	//echo $_SESSION['branch_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>OPENING STOCK</title>
<?php include_once($include.'include_css_file.php');?>
</head>
<body>
  <section id="container" >
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
							<h3><?=$form?> List</h3>
						</header>
						<div class="">
							<ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.INVENTORY_ROOT.'stock_list'?>"><i class="fa fa-home"></i> <?=$form?> List</a></li>
							  <li class="active">Opening Stock Add</li>
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
					<header class="panel-heading mtop20">
				
						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label text-right col-md-4">Choose Product Type</label>
								<div class="col-md-8">
									<select class="select2" name="product_type" id="product_type" onChange="load_product(this.value);load_product_datatable();">
									<!-- 	<option value="">--ALL--</option>
											<option value="0" selected="">FINISH PRODUCT</option>
											<option value="1" >ASSEMBLY PRODUCT</option>
											<option value="2">SUB ASSEMBLY</option>
											<option value="3">RAW MATERIAL</option>
											<option value="4">FINISH PART</option>
											<option value="5">BOI</option>
											<option value="6">CAPITAL GOODS</option>
											<option value="7">CONSUMABLE</option>
											<option value="8">Service</option> -->
										 <?=get_bom_producttype($dbcon,"");?> 
									</select>
								</div>
							</div>
						</div>
<div class="col-md-7">
							<div class="form-group">
								<label class="control-label col-md-3 text-right">Choose Product</label>
								<div class="col-md-9">
					
							<!-- <select class="select2 selproduct" title="Select product" name="product_ids[]" id="product_ids" multiple="multiple" onchange="load_product_datatable()">

								<?=getproduct($dbcon,'');?>
									</select> -->

								<input id="product_ids" multiple="multiple"  class="select2 selproduct" name="product_ids" style="width:100%;" placeholder="Select product" onchange="load_product_datatable();" value=""/>
									
									
</div>
							</div>
						</div>
				<span class="tools pull-right">
					<!-- <a href="javascript:;" onClick="tableToExcel('dynamic-table', 'Opening Stock')" ><button class="btn btn-info btn-flat" >Export Excel</button></a> -->
	
				</span>
				 <div class="col-md-12"	style="height:10px;" ></div>
			
					</header>	
				<div class="panel-body">
				  <div class="adv-table">
				  <table class="display table table-bordered table-striped" id="dynamic-table">
					  <thead>
						  <tr>
							  <th>#</th>
							  <th>Item Nmae</th>
							  <th>Opening Stock</th>
							 <!-- <th>Process Stock</th>-->
							  <th>Closing Stock</th>
							  <th class="hidden-phone">Action</th>					  
						  </tr>
					  </thead>
					  <tbody>
					  </tbody>				 
				  </table>
				  </div>
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
	<?php include_once($include1.'stock_add_model.php');?>
	<?php include_once($include1.'stock_edit_model.php');?>
	<?php include_once($include1.'stock_view_model.php');?>
	<?php include_once($include1.'stock_status_history_model.php');?>
	<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.INVENTORY_ROOT?>js/app/stock.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});

var tableToExcel = (function() {
 var uri = 'data:application/vnd.ms-excel;base64,'
   , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head></head><body><table>{table}</table></body></html>'
   , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
   , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
 return function(table, name) {
   if (!table.nodeType) table = document.getElementById(table)
   var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
   window.location.href = uri + base64(format(template, ctx))
 }
})()
</script>
</body>
</html>
<script type="text/javascript">
	var alloted="";
</script>
