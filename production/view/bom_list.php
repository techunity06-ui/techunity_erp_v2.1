<?php 
	session_start();
	include('../include/urlfile.php');	
	$form="BOM";
	if(empty($_SESSION['start'])){
		$start = date('1-m-Y');
		$end = date("d-m-Y");
	}
	else{
		$start = $_SESSION['start'];
		$end = $_SESSION['end'];
	}
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    PRODUCTION_BOM_LIST_SLUG_VIEW,PRODUCTION_BOM_LIST_SLUG_CREATE
	]);

	if(!in_array(PRODUCTION_BOM_LIST_SLUG_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
//var_dump($_SESSION);
	//echo $_SESSION['branch_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>BOM LIST</title>
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
							  <li class="active"><?=$form?> List</li>
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
					<!--
					 <div class='col-lg-5 col-md-7 col-xs-9'>
					<div class="form-group">
                                  <label class="control-label col-lg-4 col-md-4 col-xs-3">Choose Date</label>
                                  <div class=" col-lg-8 col-md-8 col-xs-9">
                                       <div class="input-group date form_datetime-component">
									<?php 
									  //$start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
									?>
                                        <input type="hidden" id="from_date"  value="<?=$start?>">
										 <input type="hidden" id="to_date"  value="<?=$end?>">
         					 		        <input type="text" id="rep_date"  onChange="reload_data();;" class="form-control datepikerdemo" value="">
											<span class="input-group-btn">
											<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
											</span>
                                      </div>
                                  </div>
                              </div>
						</div>
						-->
						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Choose Product Type</label>
								<div class="col-md-7">
									<select class="select2" name="child_usr_id" id="child_usr_id" onChange="load_bom_datatable();">
										<!-- <option value="">--ALL--</option>
											<option value="0" selected="">FINISH PRODUCT</option>
											<option value="1" >ASSEMBLY PRODUCT</option>
											<option value="2">SUB ASSEMBLY</option>
											<option value="3">RAW MATERIAL</option>
											<option value="4">FINISH PART</option>
											<option value="5">BOI</option>-->
											<!--<option value="6">CAPITAL GOODS</option>-->
											<!--<option value="7">CONSUMABLE</option>-->
											<!--<option value="8">Service</option>-->
										<?=get_product_type_company($dbcon,'','',1);?>	
									</select>
								</div>
							</div>
						</div>

				<span class="tools pull-right">
				<!-- 	<a href="<?=ROOT.'import_product_opening_stock'?>" target="_blank" title="Import BOM"><button class="btn btn-info btn-flat" data-original-title="Import BOM" data-toggle="tooltip" data-placement="top">Import BOM</button></a> -->

					<a href="<?=ROOT.'bom_upload_list'?>"  title="Import BOM"><button class="btn btn-info btn-flat" data-original-title="Import BOM List" data-toggle="tooltip" data-placement="top">Import BOM List</button></a>
				
					<?php if(in_array(PRODUCTION_BOM_LIST_SLUG_CREATE,$bulkAccessArray)){	?>
					<a href="<?=ROOT.PRODUCTION_ROOT.'bom_add'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>	
					<?php } ?>				
				</span>
				 <div class="col-md-12"	style="height:10px;" ></div>
			
					</header>	
				<div class="panel-body">
				  <div class="adv-table">
				  <table class="display table table-bordered table-striped" id="dynamic-table">
					  <thead>
						  <tr>
							  <th>#</th>
							  <!-- <th>BOM No</th> -->  <!-- SANAT  hide BOM No  -  29-07-2021 -->
							  <th>Product Image</th>
							  <!-- <th>BOM Date</th> -->  <!-- SANAT  hide BOM Date  -  29-07-2021 -->
							  <th>Product</th>
							  <th>Product Itemcode</th>

							  <!-- <th>Quantity</th> -->
							  <th>Status</th>
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
	<?php include_once($include.'bom_copy_model.php');?>
	<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/bom.js?<?php echo time(); ?>"></script>
<!--<script src="js/count.js"></script>-->
<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
function cb(start, end) {
	$('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
}
cb(moment().subtract(29, 'days'), moment());

$('.datepikerdemo').daterangepicker({       
		locale: {
			format: 'DD-MM-YYYY'
		},
	 "autoApply": true,	
	"startDate": $('#from_date').val(),
	"endDate": $('#to_date').val(),	
	ranges: {
	   'Today': [moment(), moment()],
	   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
	   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
	   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
	   'This Month': [moment().startOf('month'), moment().endOf('month')],
	   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
	}
}, cb);
$('.date-set').click(function(){
       $('.datepikerdemo').trigger('click')
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
