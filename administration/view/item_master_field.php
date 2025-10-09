<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Item Master Field Name";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$end = date("d-m-Y");
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	ADMINISTRATOR_ITEM_MASTER_FIELD_LIST,
        ADMINISTRATOR_ITEM_MASTER_FIELD_CREATE,
        ADMINISTRATOR_ITEM_MASTER_FIELD_EXCEL
    ]);

    if(!in_array(ADMINISTRATOR_ITEM_MASTER_FIELD_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
	$companyConfiguration=getCompanyConfiguration($dbcon);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Item Master Field Name</title>
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
		  <div class="row">
		  	<?php if(in_array(ADMINISTRATOR_ITEM_MASTER_FIELD_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-3">
					<section class="panel">
						<header class="panel-heading">
						  New <?=$form?>
						</header>	
						<div class="panel-body">
							<form role="form" id="item_master_field" action="javascript:;" method="post" name="make_add">
								<?php //if($branch_id=='0'){ ?>
									<?php if($companyConfiguration['branch_wise_manage']=='1'){ ?>
									<div class="form-group">
										<label>Branch *</label>
										<select class="branch_validate" name="branch_id" id="abranch_id" required >
	                    					<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
																<?=getBranchBox_new($dbcon, $branch,'all');?>
	                					</select>
						            </div>
						        <?php } ?>
							   <div class="form-group">
								  <label>Item Master Field Name *</label>
								  <input type="text" class="form-control" id="item_master_fieldd" name="item_master_fieldd" placeholder="Item Master Field">
							  </div>
							   <div class="form-group">
								  <label>Item Master Field DB name *</label>
								  <input type="text" class="form-control" id="item_master_field_db_name" name="item_master_field_db_name" placeholder="Item Master Field Db Name">
							  </div>
							  <div class="form-group">
								  <label>Priority *</label>
								  <input type="text" class="form-control" id="priority" name="priority" placeholder="Priority">
							  </div>
								<button type="submit" class="btn btn-info">Submit</button>
							  </form>
						</div>
					</section>
				</div>
			<?php } ?>
			<?php if(in_array(ADMINISTRATOR_ITEM_MASTER_FIELD_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-9">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>		
			<section class="panel">
				  <header class="panel-heading">
					  <?=$form?> List
					<?php if($_SESSION['user_type'] == 2 && in_array(ADMINISTRATOR_ITEM_MASTER_FIELD_EXCEL,$bulkAccessArray)){?>					  
					<span class="tools pull-right">		
						<a href="javascript:;" onClick="tableToExcel('make-table', 'Instalment Collection')" ><button class="btn btn-info btn-flat" >Export Excel</button></a>	
					</span>
					<?php }?>
				  </header>
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="make-table">
				  	<div class="col-md-12">
                        <div class="col-md-6">
						
                        <select class="select2" name="branch_id" id="branch_id" onchange="load_make_datatable()" required <?php if($companyConfiguration['branch_wise_manage']=='0'){ ?>disabled<?php } ?>   >
	                    								<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
														<?=getBranchBox_new($dbcon, $branch,'all');?>
	                							</select>
                        
                        </div>
                    </div>
				  <thead>
				  <tr>
					  <th>Sr. NO.</th>
					  <th>Item Master Field Name</th>
				   	<th>Item Master Field Db Name</th>
				   	<th>Priority</th>
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
			<form id="FormEditItemMasterField" role="form" method="post" novalidate>
				<?php //if($branch_id=='0'){ ?>
					<?php if($companyConfiguration['branch_wise_manage']=='1'){ ?>
					<div class="form-group">
						<label>Branch *</label>
						<select class="branch_validate" name="branch_id" id="e_branch_id" required>
	    					<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
						<?=getBranchBox_new($dbcon, $branch,'all');?>
						</select>
		            </div>
		        <?php } ?> 				
				<div class="form-group">
								  <label>Item Master Field Name *</label>
								  <input type="text" class="form-control" id="edit_item_master_field" name="edit_item_master_field" placeholder="Item Master Field">
							  </div>
							   <div class="form-group">
								  <label>Item Master Field DB name *</label>
								  <input type="text" class="form-control" id="edit_item_master_field_db_name" name="edit_item_master_field_db_name" placeholder="Item Master Field Db Name">
							  </div>


							  <div class="form-group">
								  <label>Priority *</label>
								  <input type="text" class="form-control" id="edit_priority" name="edit_priority" placeholder="Priority">
							  </div>	
			</div>
			<div class="modal-footer">
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update Make</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/item_master_field.js?<?=time()?>"></script>
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
    link.download = "make-number-list-# "+coid + ".xls";
    link.href = uri + base64(format(template, ctx));
    link.click();
	}
})()
$(".select2").select2({
	width: '100%'
});
$(".branch_validate").select2({
width: '100%'
}).on('change', function() {
$(this).valid();
});
</script>
  </body>
</html>
