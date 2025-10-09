<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$form="Currency";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$end = date("d-m-Y");
$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    // $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    // 	ADMINISTRATOR_NEW_CURRENCY_VIEW,
    //     ADMINISTRATOR_NEW_CURRENCY_CREATE
    // ]);

    // if(!in_array(ADMINISTRATOR_NEW_CURRENCY_VIEW,$bulkAccessArray)){
    //     header("Location: ".DOMAIN."permission_access");
    // }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>CURRENCY MST</title>
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
					<?php //if(in_array(ADMINISTRATOR_NEW_CURRENCY_CREATE,$bulkAccessArray)){ ?>
						<div class="col-sm-3">
							<section class="panel">
								<header class="panel-heading">
									New <?=$form?>
								</header>	
								<div class="panel-body">
									<form role="form" id="currency_add" action="javascript:;" method="post" name="currency_add">
										<div class="form-group">
											<label for="vendor_name">Currency Name*</label>
											<input type="text" class="form-control" id="currency_name" name="currency_name" placeholder=" Currency Name">
										</div>
										<div class="form-group">
											<label for="vendor_name">Currency Code*</label>
											<input type="text" class="form-control" id="currency_code" name="currency_code" placeholder=" Currency Code">
										</div>
										<div class="form-group">
											<label for="vendor_name">Currency Symbol*</label>
											<input type="text" class="form-control" id="currency_symbol" name="currency_symbol" placeholder=" Currency Symbol">
										</div>
										<div class="form-group">
											<label for="currency_rate">Currency Rate*</label>
											<input type="text" class="form-control numbersOnly" id="currency_rate" name="currency_rate" placeholder="Currency Rate">
										</div>
										<div class="form-group">
											<label for="vendor_name">Currency In Word (Rupees)*</label>
											<input type="text" class="form-control" id="currency_in_word" name="currency_in_word" placeholder="Currency In Word">
										</div>
										<div class="form-group">
											<label for="vendor_name">Currency In Word (Paisa)*</label>
											<input type="text" class="form-control" id="currency_in_word_end" name="currency_in_word_end" placeholder="Currency In Word">
										</div>
										<button type="submit" class="btn btn-info">Submit</button>
									</form>
								</div>
							</section>
						</div>
					<?php //} ?>
						<div class="col-sm-9">
					<?php if(in_array(ADMINISTRATOR_NEW_CURRENCY_CREATE,$bulkAccessArray)){ ?>
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
											<table  class="display table table-bordered table-striped" id="currency-table">
												<thead>
													<tr>
														<th>Sr. NO.</th>
														<th>Currency Name</th>					  
														<th>Currency Code</th>
														<th>Currency Symbol</th>
														<th>Currency Rate</th>
														<th>Currency In Word</th>
														<th>Currency In End</th>
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
							<form id="FormEditCurrency" role="form" method="post" novalidate>				
								<div class="form-group">
									<label class="control-label">Currency Name*</label>
									<input type="text" name="edit_currency_name"  id="edit_currency_name" class="form-control">
								</div>
								<div class="form-group">
									<label class="control-label">Currency Code*</label>
									<input type="text" name="edit_currency_code"  id="edit_currency_code" class="form-control">
								</div>	
								<div class="form-group">
									<label class="control-label">Currency Symbol*</label>
									<input type="text" name="edit_currency_symbol"  id="edit_currency_symbol" class="form-control">
								</div>
								<div class="form-group">
									<label for="currency_rate">Currency Rate*</label>
									<input type="text" class="form-control numbersOnly" id="edit_currency_rate" name="edit_currency_rate" placeholder="Currency Rate">
								</div>	
								<div class="form-group">
									<label class="control-label">Currency In Word (Rupees)*</label>
									<input type="text" name="edit_currency_in_word"  id="edit_currency_in_word" class="form-control">
								</div>
								<div class="form-group">
									<label class="control-label">Currency In Word (Paisa)*</label>
									<input type="text" name="edit_currency_in_word_end"  id="edit_currency_in_word_end" class="form-control">
								</div>
							</div>
							<div class="modal-footer">
								<input type="hidden" name="edit_id" id="edit_id" value="" />
								<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
								<button class="btn btn-info btn-flat" type="submit">Update Currency</button>
							</div>
						</form>
					</div><!-- /.modal-content -->
				</div><!-- /.modal-dialog -->
			</div><!-- /.modal -->
			<!-- js placed at the end of the document so the pages load faster -->
			<?php include_once('../include/include_js_file.php');?>   
			<script src="<?=ROOT?>js/app/currency_new_mst.js?<?=time()?>"></script>
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
