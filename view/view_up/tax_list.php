<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="TAX";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
		   <div class="row">
			<div class="col-sm-3">
				<section class="panel">
				  <header class="panel-heading">
					  New <?=$form?>
					 	 
					</header>
					
					<div class="panel-body">
						<form role="form" id="tax_add" action="javascript:;" method="post" name="tax_add">
								<div class="form-group">
									<label>Ledger *</label>
									<select class="select2" name="ledger_id" id="ledger_id">
										<?php //=get_ledger($dbcon);?>
										<?=get_ledger($dbcon,''," and l_group in (31)");?>
									</select>
								</div>
							  <div class="form-group" style="display:none;">
								<label>Income Group*</label>
								
								<select class="select2" name="tax_group" id="tax_group">
									<?=get_all_group($dbcon,$rel['tax_group']);?>
								</select>
								</div>
							  <div class="form-group">
								  <label for="vendor_name">Tax Name</label>
								  <input type="text" class="form-control" id="tax_name" name="tax_name" placeholder="Tax Name">
							  </div>							  
							<div class="form-group">
									<label class="control-label">Tax Value(in %)</label>
								<input type="text"  name="tax_value"  id="tax_value" class="form-control"  placeholder="Tax Value(in %)" >
							</div>				
								<input type='hidden' name='mode' id='mode' value='add' />
							  	<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  								<button type="submit" class="btn btn-info">Submit</button>
						  </form>

					</div>
				</section>
			</div>
			<div class="col-sm-9">
			<section class="panel">
				  <header class="panel-heading">
					  <?=$form?> List	
						
						 	 <span class="pull-right">
							<a href="<?=ROOT.'formula_list'?>" type="button" class="btn btn-success">Formula List</a> </span>				  
				  </header>
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  <thead>
				  <tr>
					  <th>Sr. NO.</th>
					  <th>Tax Name</th>					  
					  <th>Tax Value</th>					  
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
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Edit <?=$form?></h3>
				
			</div>
			
			<div class="modal-body form">
			<form id="FormEdittax" role="form" method="post" novalidate>
				<div class="form-group">
					<label>Ledger *</label>
					<select class="select2" name="edit_ledger_id" id="edit_ledger_id">
						<?php //=get_ledger($dbcon);?>
						<?=get_ledger($dbcon,''," and l_group in (31)");?>
					</select>
				</div>
				<div class="form-group" style="display:none;">
					<label>Income Group*</label>
					
					<select class="select2" name="edit_tax_group" id="edit_tax_group">
						<?=get_all_group($dbcon,'');?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Tax Name</label>
					<input type="text" name="tax_name"  id="edit_tax_name" class="form-control" required>
				</div>				
				<div class="form-group">
					<label class="control-label">Tax Value</label>
					<input type="text" name="tax_value"  id="edit_tax_value" class="form-control" required>
				</div>				
			
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update tax</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
    <!-- js placed at the end of the document so the pages load faster -->
		<?php include_once('../include/include_js_file.php');?>   
		<script src="<?=ROOT?>js/app/tax.js?<?=time()?>"></script>
  </body>
</html>
<script>
$(".select2").select2({
	width: '100%'
});
</script>
