<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	ADMINISTRATOR_STATE_LIST,
        ADMINISTRATOR_STATE_CREATE
    ]);

    if(!in_array(ADMINISTRATOR_STATE_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
	

	
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
<style>
	.capitalize {
	  text-transform: capitalize;
	}
	.select2-container-multi .select2-choices .select2-search-choice {
		padding: 5px 5px 5px 18px;
	}
</style>
<!--<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />-->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/select2/3.5.4/select2.min.css" />
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-css/1.4.6/select2-bootstrap.min.css" />
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
						  <h3>New State</h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
							  <li class="active">State List</li>
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--state overview start-->
			<?php include_once('../include/country_state_city.php');?>
		  <div class="row">
		  	<?php if(in_array(ADMINISTRATOR_STATE_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-3">
					<section class="panel">
					  <header class="panel-heading">
						  New State
						</header>	
						<div class="panel-body">
							<form role="form" id="state_add" action="javascript:;" method="post" name="state_add">
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
									  <label for="stateid">Country*</label>
									  <select id="countryid" class="select2" name="countryid" required>
										<?=get_country($dbcon,'101')?>
									</select>
									</div>	
								  <div class="form-group">
									  <label for="vendor_name">State Name*</label>
									  <input type="text" class="form-control" id="state_name" name="state_name" placeholder=" State Name">
								  </div>
									<div class="form-group">
									  <label for="vendor_name">GST Code</label>
									  <input type="text" class="form-control" id="gst_state_code" name="gst_state_code" placeholder="GST Code">
								  </div>	
								 
								 <div class="form-group">
									  <label for="vendor_name">Product </label>
									 <input id="product_id" name="product_id" style="width:100%;" placeholder="type a number, scroll for more results" />
								  </div>	
								<input type="button" name="demo1" id="demo1" value="click" onclick="select_product();" />
									<input type='hidden' name='mode' id='mode' value='add' />
								  	<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
								  <button type="submit" class="btn btn-info">Submit</button>
								  
									 
									 
							  </form>

						</div>
					</section>
				</div>
			<?php } ?>
			<?php if(in_array(ADMINISTRATOR_STATE_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-9">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>
			<section class="panel">
				  <header class="panel-heading">
					  State List
				 <span class="tools pull-right">
					<a href="javascript:;" class="fa fa-chevron-down"></a>
					<a href="javascript:;" class="fa fa-times"></a>
				 </span>
				  </header>
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
			  		<div class="col-md-12">
                        <div class="col-md-6">
                            <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_state_mst_datatable()','4','6'); ?>
                        </div>
                    </div>
				  <thead>
				  <tr>
						<th>Sr. NO.</th>
						<th>Country Name</th>
						<th>State Name</th>
						<th>GST Code</th>			  
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
				<h3>Edit State</h3>
				
			</div>
			<div class="modal-body form">
			<form id="FormEditState" role="form" method="post" novalidate>
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
					<label for="stateid">Country*</label>
					<select id="edit_countryid" class="select2" name="countryid" required>
						<?=get_country($dbcon,'');?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">State Name*</label>
					<input type="text" name="state_name"  id="edit_state_name" class="form-control" required>
				</div>
				<div class="form-group">
					  <label for="vendor_name">GST Code</label>
					  <input type="text" class="form-control" id="edit_gst_state_code" name="gst_state_code" placeholder="GST Code">
				  </div>					
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update State</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?> 
	
	<!--<script type="text/javascript" src='//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js'></script>-->
    <!--<![endif]-->
	<script src="//cdnjs.cloudflare.com/ajax/libs/lodash.js/4.15.0/lodash.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/3.5.4/select2.min.js"></script>	
	
	<script src="<?=ROOT?>js/app/droupdown_data_manage_test.js?<?=time();?>"></script>
<script>
$(".select2").select2({
		width: '100%'
	});
</script>
  </body>
</html>
