<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/coman_function.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
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
						  <h3>New Invoice</h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active">Invoice Type</li>
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--Customer overview start-->
		<?php include_once('../include/country_Customer_po.php');?>
		  <div class="row">
			<div class="col-sm-3">
				<section class="panel">
				  <header class="panel-heading">
					  New Invoice Type
					</header>	
					<div class="panel-body">
						<form role="form" id="invoicetype_add" action="javascript:;" method="post" name="invoicetype_add">
							  
							  <div class="form-group">
								  <label for="invoice Type">Invoice Type</label>
								  <input type="text"  class="form-control" id="invoice_type" name="invoice_type" placeholder="Invoice Type" />
							  </div>
							  <div class="form-group">
								  <label for="invoice Type">Invoice Start Series</label>
								  <input type="number" min="0" class="form-control" id="taxinvoice_start" name="taxinvoice_start" placeholder="Invoice Start Series" />
							  </div>
							<!--  <div class="form-group">
								  <label for="invoice Type">Excise Invoice Start Series</label>
								  <input type="number" min="0" class="form-control" id="exciseinvoice_start" name="exciseinvoice_start" placeholder="Invoice Start Series" />
							  </div>-->
							  <div class="form-group">
								  <label for="invoice Type">Invoice Format</label>
									<select class="form-control" id="invoice_format" name="invoice_format"  onchange="format_valuechange(this.value);">
										<option value="0">None</option>
										<option value="1">Prefix</option>
										<option value="2">Suffix</option>
										<option value="3">Both</option>
									</select>								  
							  </div>
															  
							  <div class="hidden form-group" id="format_value_div">
								  <label for="invoice Type">Format Value</label>
								  <input type="text" class="form-control" id="format_value" name="format_value" placeholder="eg.EXP, RS" onKeyUp="view_format(this.value)"/>
							  </div>
							  
							  <div class="hidden form-group" id="end_format_value_div">
								  <label for="invoice Type">End Format Value</label>
								  <input type="text" class="form-control" id="end_format_value" name="end_format_value" placeholder="eg.EXP, RS" onKeyUp="view_format(this.value)"/>
							  </div>
							  <div class="hidden form-group" id="ex_format_div">
								  <label for="invoice Type">Example Format : </label>
								  <span id="ex_format" style="font-size:17px;"></span>							  
							  </div> 
								<input type='hidden' name='mode' id='mode' value='add' />
							  	<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
							  <button type="submit" class="btn btn-info">Submit</button>
						  </form>

					</div>
				</section>
			</div>
			<div class="col-sm-9">
			<section class="panel">
				  <header class="panel-heading">
					   Invoice Type List
				 <span class="tools pull-right">
					<a href="javascript:;" class="fa fa-chevron-down"></a>
					<a href="javascript:;" class="fa fa-times"></a>
				 </span>
				 <span class="tools pull-right">
				<!--<button class="btn btn-primary" data-original-title="Invoice Series Same" data-toggle="tooltip" data-placement="top" onClick="invoice_series_same()">Series Same</button>	-->	
			</span>
				  </header>
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  <thead>
				  <tr>
					  <th>Sr. NO.</th>
					  <th>Invoice Type</th>
					  <th>Invoice Stating Series</th>
					   <th>Format</th>
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
		  
		  <!--Customer overview end-->
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
				<h3>Edit Invoice Type</h3>				
			</div>
			<div class="modal-body form">
			<form id="FormEditinvoicetype" role="form" method="post" novalidate>				
				<div class="form-group">
				  <label for="invoice Type">Invoice Type</label>
				  <input type="text" class="form-control" id="edit_invoice_type" name="edit_invoice_type" placeholder="Invoice Type" />
			  </div>
			  <div class="form-group">
								  <label for="invoice Type">Invoice Start Series</label>
								  <input type="number" min="0" class="form-control" id="edit_taxinvoice_start" name="edit_taxinvoice_start" placeholder="Invoice Type" />
							  </div>
			<!--  <div class="form-group">
								  <label for="invoice Type">Excise Invoice Start Series</label>
								  <input type="number" min="0" class="form-control" id="edit_exciseinvoice_start" name="edit_exciseinvoice_start" placeholder="Invoice Start Series" />
							  </div>	-->						  
				<div class="form-group">
								  <label for="invoice Type">Invoice Format</label>
								  <select class="form-control" id="edit_invoice_format" name="invoice_format"  onchange="edit_format_valuechange(this.value);">
									<option value="0">None</option>
									<option value="1">Prefix</option>
									<option value="2">Suffix</option>
									<option value="3">Both</option>
								  </select>								  
							  </div>
															  
							  <div class="hidden form-group" id="edit_format_value_div">
								  <label for="invoice Type">Format Value</label>
								  <input type="text" class="form-control" id="edit_format_value" name="format_value" placeholder="eg.EXP, RS"/>
							  </div>
							  <div class="hidden form-group" id="edit_end_format_value_div">
								  <label for="invoice Type">Format Value</label>
								  <input type="text" class="form-control" id="edit_end_format_value" name="edit_end_format_value" placeholder="eg.EXP, RS"/>
							  </div>							  										  
											
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/invoicetype_mst.js?<?=time()?>"></script>
<script>
$(".select2").select2({
		width: '100%'
	});
$('.default-date-picker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        });
</script>
  </body>
</html>
