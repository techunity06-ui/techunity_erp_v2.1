<?php 
	session_start();
	include('../include/urlfile.php');
		
	$form="balty";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$end = date("d-m-Y");
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	ADMINISTRATOR_CURRENCY_LIST,
        ADMINISTRATOR_CURRENCY_CREATE
    ]);

    // if(!in_array(ADMINISTRATOR_CURRENCY_LIST,$bulkAccessArray)){
    //     header("Location: ".DOMAIN."permission_access");
    // }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>CURRENCY</title>
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
						  <h3>New <?=$form?></h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
							  <li class="active"><?=$form?></li>
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--state overview start-->
		<?php //include_once('../include/Currency_state_city.php');?>	
		  <div class="row">
		  	<?php //if(in_array(ADMINISTRATOR_CURRENCY_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-3">
					<section class="panel">
						<header class="panel-heading">
						  New <?=$form?>
						</header>	
						<div class="panel-body">
							<form role="form" id="balty_add" action="javascript:;" method="post" name="balty_add">
								  
								  <div class="form-group">
									  <label for="balty_name">Balty Name*</label>
									  <input type="text" class="form-control" id="balty_name" name="balty_name" placeholder=" Balty Name">
								  </div>
								<button type="submit" class="btn btn-info">Submit</button>
							  </form>
						</div>
					</section>
				</div>
			<?php //} ?>
			<?php //if(in_array(ADMINISTRATOR_CURRENCY_CREATE,$bulkAccessArray)){ 
				if(1==1){
				?>
				<div class="col-sm-9">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>		
			<section class="panel">
				  <header class="panel-heading">
					  <?=$form?> List
					<?php if($_SESSION['user_type'] == 2){?>					  
					<span class="tools pull-right">		
						<a href="javascript:;" onClick="tableToExcel('currency-table', 'Instalment Collection')" ><button class="btn btn-info btn-flat" >Export Excel</button></a>	
					</span>
					<?php }?>
				  </header>
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="balty-table">
				  
				  <thead>
				  <tr>
					  <th>Sr. NO.</th>
					  <th>Balty Name</th>					  
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
		  <input type="hidden" name="coid" id="coid" value="<?=$end?>">
		  <!--state overview end-->
          </section>
      </section>
      <!--main content end-->
      <!--footer start-->
	<?php include_once($include.'footer.php');?>
      <!--footer end-->
  </section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 style="margin-top:-6px; important!">Edit <?=$form?></h3>
			</div>
			<div class="modal-body form">
			<form id="FormEditCurrency" role="form" method="post" novalidate>
				 				
				<div class="form-group">
					<label class="control-label">Balty Name*</label>
					<input type="text" name="edit_balty_name"  id="edit_balty_name" class="form-control">
				</div>
				
			
			<div class="modal-footer">
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update</button>
			</div>
			</form>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/balty.js?<?=time()?>"></script>
<script>
var tableToExcel = (function() {
	var uri = 'data:application/vnd.ms-excel;base64,'
	, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
	, base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
	, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
	return function(table, name) {
		if (!table.nodeType) table = document.getElementById(table)
		var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
		var coid= $('#coid').val();
	var link = document.createElement("a");
    link.download = "currency-list-# "+coid + ".xls";
    link.href = uri + base64(format(template, ctx));
    link.click();
	}
})()
$(".select2").select2({
	width: '100%'
});
</script>
  </body>
</html>