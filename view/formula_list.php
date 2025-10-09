<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$form="Formula";
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	ADMINISTRATOR_FORMULA_LIST,
        ADMINISTRATOR_FORMULA_CREATE
    ]);

    if(!in_array(ADMINISTRATOR_FORMULA_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
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
						  <h3>New Formula</h3>
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
              <!--formula overview start-->
			<?php include_once('../include/country_formula_city.php');?>
		  <div class="row">
		  	<?php if(in_array(ADMINISTRATOR_FORMULA_CREATE,$bulkAccessArray)){ ?>
			<div class="col-sm-3">
				<section class="panel">
				  <header class="panel-heading">
					  New formula
					</header>	
					<div class="panel-body">
						<form role="form" id="formula_add" action="javascript:;" method="post" name="formula_add">
								<?php if($branch_id=='0'){ ?>
									<div class="form-group">
										<label>Branch *</label>
										<?php
											$str='';
											$query="SELECT branch_id, branch_name FROM branch_mst WHERE branch_status IN ('0','1') AND company_id =".$_SESSION['company_id'];
											$rs_dispatch=$dbcon->query($query);	
										?>
										<select class="select2" name="branch_id" id="abranch_id" required >
	                    					<option value="">Select Branch</option>
	                    					<option value="10000">All Branch</option>
	                    					<?php 
	                    						while($rel=mysqli_fetch_assoc($rs_dispatch))
												{	
													$sel=''; 
													if($rel['branch_id']==$branchid){
														$sel ="selected='selected'";
													}
													$str .= '<option '.$sel.' value="'.$rel['branch_id'].'">'.$rel['branch_name'].'</option>';
												}
												echo $str;
	                    					?>
	                					</select>
						            </div>
						        <?php } ?>
								<div class="form-group">
									  <label>Choose Tax Percentage</label>
									  <select id="tax_per_id" class="select2" name="tax_per_id" required>
										<option value="">--select Tax Percentage--</option>
										<?php 
											
											$query = $dbcon->query("SELECT `tp_id`,`tp_per` FROM `tbl_tax_per_master` WHERE `tp_status` = 0  order by tp_per ");
											while($r = $query -> fetch_assoc()) {
												echo '<option value="'.$r['tp_id'].'">'.$r['tp_per'].'</option>';
											}
											
										?>
									  </select>
								</div>
								
								<div class="form-group">
									  <label>Choose Tax Category</label>
									  <select id="tax_cat" class="select2" name="tax_cat" required>
										<option value="">--select Tax Category--</option>
										<option value="INTRA"> INTRA TAX </option>
										<option value="INTER"> INTER TAX </option>
									  </select>
								</div>
								
								<div class="form-group">
									  <label>Choose Tax Name</label>
									  <select id="tax_id" class="select2" name="tax_id" required multiple="multiple">
										 <?php
											$query = $dbcon->query("SELECT `tax_id`,`tax_name` FROM `tbl_tax` WHERE `tax_status` = 0 and company_id=".$_SESSION['company_id']." order by tax_value DESC ");
											while($r = $query -> fetch_assoc()) {
												echo '<option value="'.$r['tax_id'].'">'.$r['tax_name'].'</option>';
											}
										?>
							
									</select>
								</div>
                                                                <div class="form-group">
                                                                    <label class="control-label">Tax Type</label>
                                                                    <select id="tax_type" class="select2" name="tax_type" required>
                                                                        <option value="0">On Product</option>
                                                                        <option value="1">On Total</option>
                                                                    </select>
                                                                </div>
							  							  
								<input type='hidden' name='mode' id='mode' value='add' />
							  	<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
							  <button type="submit" class="btn btn-info">Submit</button>
						  </form>

					</div>
				</section>
			</div>
			<?php } ?>
			<?php if(in_array(ADMINISTRATOR_FORMULA_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-9">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>		
			<section class="panel">
				  <header class="panel-heading">
					  Formula List
				 <span class="tools pull-right">
					<a href="javascript:;" class="fa fa-chevron-down"></a>
					
				 </span>
				  </header>
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  	<div class="col-md-12">
                        <div class="col-md-6">
                            <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_formula_datatable()','4','6'); ?>
                        </div>
                    </div>
				  <thead>
				  <tr>
						<th>Sr. NO.</th>
						<th>Percentage </th>
						<th>Category</th>
						<th>Formula Name</th>
						<th>Formula Type</th>
						<th class="hidden-phone">Action</th>					  
				  </tr>
				  </thead>
				  <tbody>
				  </tbody>
				 <!-- <tfoot>
				  <tr>
					  <th>Rendering engine</th>
					  <th>Browser</th>
					  <th>Platform(s)</th>
					  <th class="hidden-phone">Engine version</th>
					  <th class="hidden-phone">CSS grade</th>
				  </tr>
				  </tfoot>-->
				  </table>
				  </div>
				  </div>
				  </section>
			</div>
		  </div>
		  
		  <!--formula overview end-->
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
				<h3>Edit Formula</h3>
				
			</div>
			<div class="modal-body form">
			<form id="FormEditformula" role="form" method="post" novalidate>
				<?php if($branch_id=='0'){ ?>
					<div class="form-group">
						<label>Branch *</label>
						<?php
							$str='';
							$query="SELECT branch_id, branch_name FROM branch_mst WHERE branch_status IN ('0','1') AND company_id =".$_SESSION['company_id'];
							$rs_dispatch=$dbcon->query($query);	
						?>
						<select class="select2" name="branch_id" id="e_branch_id" required>
	    					<option value="">Select Branch</option>
	    					<option value="10000">All Branch</option>
	    					<?php 
	    						while($rel=mysqli_fetch_assoc($rs_dispatch))
								{	
									$str .= '<option '.$sel.' value="'.$rel['branch_id'].'">'.$rel['branch_name'].'</option>';
								}
								echo $str;
	    					?>
						</select>
		            </div>
		        <?php } ?>
	        
				<div class="form-group">
					  <label>Choose Tax Percentage</label>
					  <select id="edit_tax_per_id" class="select2" name="edit_tax_per_id" required>
						<option value="">--select Tax Percentage--</option>
						<?php 
							
							$query = $dbcon->query("SELECT `tp_id`,`tp_per` FROM `tbl_tax_per_master` WHERE `tp_status` = 0  order by tp_per ");
							while($r = $query -> fetch_assoc()) {
								echo '<option value="'.$r['tp_id'].'">'.$r['tp_per'].'</option>';
							}
							
						?>
					  </select>
				</div>
				
				<div class="form-group">
					  <label>Choose Tax Category</label>
					  <select id="edit_tax_cat" class="select2" name="edit_tax_cat" required>
						<option value="">--select Tax Category--</option>
						<option value="INTRA"> INTRA TAX </option>
						<option value="INTER"> INTER TAX </option>
					  </select>
				</div>
				
				<div class="form-group">
				  <label for="formulaid">Choose Tax</label>
				  <select id="edit_tax_id" class="select2" name="edit_tax_id" required multiple="multiple">
					 	<?php
						$query = $dbcon->query("SELECT `tax_id`,`tax_name` FROM `tbl_tax` WHERE `tax_status` = 0 order by tax_name ");
						while($r = $query -> fetch_assoc()) {
								
							echo '<option value="'.$r['tax_id'].'">'.$r['tax_name'].'</option>';
						}
					?>	</select>
				</div>	
                                <div class="form-group">
                                    <label class="control-label">Tax Type</label>
                                    <select id="edit_tax_type" class="select2" name="tax_type" required>
                                        <option value="0">On Product</option>
                                        <option value="1">On Total</option>
                                    </select>
                                </div>
								
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update formula</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/formula_mst.js?<?=time()?>"></script>
<script>
$(".select2").select2({
		width: '100%'
	});
</script>
  </body>
</html>
