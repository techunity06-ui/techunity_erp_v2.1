<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Merge Field";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$end = date("d-m-Y");

	//Amish Soni 06-01-2021
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		MERGE_FIELD_SLUG_CREATE,
		MERGE_FIELD_SLUG_EDIT
	]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>MERGE FIELD</title>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
  <section id="container" >
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
						  <h3>New <?=$form?></h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
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
			<div class="col-sm-3">
				<section class="panel">
					<header class="panel-heading">
					  New <?=$form?>
					</header>	
					<div class="panel-body">
						<form role="form" id="field_add" action="javascript:;" method="post" name="field_add">
						   	<div class="form-group">
								<label>Field Name *</label>
								<input type="text" class="form-control" id="field_name" name="field_name" placeholder="Field Name">
							</div>
							<!-- Amish Soni Start 12-01-2021 -->
							<div class="form-group">
								<label>Table Name *</label>
								<select class="select2" id="table_name" name="table_name" onchange="getColumns(this.value)">
									<option value="">Please Select</option>
									<?php echo getAllTables($dbcon);?>
									<option value="other">Other</option>
								</select>
						  	</div>
						  	<div class="form-group replaceBox" style="display: none;">
								<label>Replace With *</label>
								<div class="replace_select">
									<select class="select2" name="replace_with_select" id="replace_with_select">
									</select>
								</div>
								<input type="text" class="form-control" name="replace_with" id="replace_with_text" />
						  	</div>
						  	<!-- Amish Soni End 12-01-2021 -->
						  	<div class="form-group">
								<label>Module Name *</label>
								<select class="select2" id="module_id" name="module_id">
									<?php echo get_email_module_list($dbcon, '');?>
								</select>
						  	</div>
							<?php //Amish Soni 06-01-2021
							if(in_array(MERGE_FIELD_SLUG_CREATE, $bulkAccessArray)) { ?>
								<button type="submit" class="btn btn-info">Submit</button>
							<?php } ?>
						  </form>
					</div>
				</section>
			</div>
			<div class="col-sm-9">
			<section class="panel">
				  <header class="panel-heading">
					  <?=$form?> List
					<?if($_SESSION['user_type'] == 2){?>					  
					<span class="tools pull-right">		
						<a href="javascript:;" onClick="tableToExcel('field-table', 'Instalment Collection')" ><button class="btn btn-info btn-flat" >Export Excel</button></a>	
					</span>
					<?}?>
				  </header>
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="field-table">
				  <thead>
				  <tr>
					  <th>Sr. NO.</th>
					  <th>Field Name</th>
					  <th>Table Name</th>
					  <th>Replace With</th>
					  <th>Module Name</th>
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
	<?php include_once('../include/footer.php');?>
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
			<form id="FormEditField" role="form" method="post" novalidate>				
				<div class="form-group">
					<label class="control-label">Field Name</label>
					<input type="text" name="edit_field_name"  id="edit_field_name" class="form-control">
				</div>
				<!-- Amish Soni Start 13-01-2021 -->
				<div class="form-group">
					<label>Table Name *</label>
					<select class="select2" id="edit_table_name" name="edit_table_name" onchange="getColumns(this.value, 'edit')">
						<option value="">Please Select</option>
						<?php echo getAllTables($dbcon);?>
						<option value="other">Other</option>
					</select>
			  	</div>
			  	<div class="form-group edit_replaceBox">
					<label>Replace With *</label>
					<div class="replace_select">
						<select class="select2" name="edit_replace_with_select" id="edit_replace_with_select">
						</select>
					</div>
					<input type="text" class="form-control" name="edit_replace_with" id="edit_replace_with_text" />
			  	</div>
			  	<!-- Amish Soni End 13-01-2021 -->
				<div class="form-group">
					<label>Module Name *</label>
					<select class="select2" id="edit_module_id" name="edit_module_id">
						<?php echo get_email_module_list($dbcon, '');?>
					</select>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<?php //Amish Soni 06-01-2021
				if(in_array(MERGE_FIELD_SLUG_EDIT, $bulkAccessArray)) { ?>
					<button class="btn btn-info btn-flat" id="updateBtn" type="submit">Update Field</button>
				<?php } ?>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/merge_field_mst.js"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});
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
    link.download = "revision-list-# "+coid + ".xls";
    link.href = uri + base64(format(template, ctx));
    link.click();
	}
})()
</script>
  </body>
</html>
