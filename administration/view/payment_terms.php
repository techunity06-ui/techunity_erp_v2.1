<?php 
	session_start();
	include('../include/urlfile.php');
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		ADMINISTRATOR_PAYMENTTERM_VIEW,
		ADMINISTRATOR_PAYMENTTERMS_ADD
	]);
	if(!in_array(ADMINISTRATOR_PAYMENTTERM_VIEW,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$form="Payment Terms"
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
							  <li class="active"><?=$form?></li>
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
             </div>
              <!--state overview start-->
		  <div class="row">
			<div class="col-md-3">
				<section class="panel">
				  <header class="panel-heading">
					  New <?=$form?>
					</header>	
					<div class="panel-body">
						<form role="form" id="payment" action="javascript:;" method="post" name="payment">
							  <div class="form-group">
								  <label for="payment_terms">Payment Terms</label>
								  <input type="text" class="form-control" id="payment_terms1" name="payment_terms1" placeholder="Payment Tearms">
							  </div>
								<div class="form-group">
								  <label for="payment Days">Payment Days</label>
								  <input type="number" class="form-control" id="payment_days" name="payment_days" placeholder="Payment Days">
							  </div>
								<input type='hidden' name='mode' id='mode' value='add' />
							  	<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
							  <button type="submit" class="btn btn-info">Submit</button>
						  </form>

					</div>
				</section>
			</div>
			<div class="col-md-9">
			<section class="panel">
				  <header class="panel-heading">
					  <?=$form?> List				 
				  </header>
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  <thead>
				  <tr>
					  <th>Sr. NO.</th>
					  <th>Payment Terms</th>
					  <th>Payment Days</th>
					  <th class="hidden-phone">Action</th>					  
				  </tr>
				  </thead>
				  <tbody>
				  </tbody>				 
				  </table>
				   <style>
				  @media screen and (max-width:992px){
					#dynamic-table td:before{
							color:red
						}
					
					#dynamic-table td:nth-of-type(1):before { content: "Sr. NO.:"; }
					#dynamic-table td:nth-of-type(2):before { content: "Payment Terms:"; }
					#dynamic-table td:nth-of-type(3):before { content: "Payment Days:"; }
					#dynamic-table td:nth-of-type(4):before { content: "Action:"; }
				
				}
				  </style>
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
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Edit <?=$form?></h3>
				
			</div>
			<div class="modal-body form">
			<form id="FormEditpayment" role="form" method="post" novalidate>				
				<div class="form-group">
					<label class="control-label">Payment Terms</label>
					<input type="text" name="payment_terms"  id="edit_Patment_terms" class="form-control" required>
				</div>	
				<div class="form-group">
								  <label for="payment Days">Payment Days</label>
								  <input type="Number" class="form-control" id="edit_payment_days" name="edit_payment_days" placeholder="Payment Days">
							  </div>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update Payment Terms</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/payment_terms.js"></script>
  </body>
</html>
