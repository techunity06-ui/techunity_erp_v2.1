<?php 
	session_start();
	include_once("../config/config.php");
	//error_reporting(E_ALL);
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Party";
	if(strpos($_SERVER[REQUEST_URI], "customeraddedit")==false)
	{
		$mode="Add";
		$countryid="101";
		//$stateid="1";
		//$cityid="1";
		$user_name=$_SESSION['user_name'];
	}
	else
	{
		$countryid="101";
		$mode="Edit";
		$custid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select cust.*,usr.user_name from tbl_customer as cust 
		left join users as usr on usr.user_id=cust.user_id
		where cust.cust_id=$custid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$cst_date='';$st_date='';
		if($rel['cst_date']!="1970-01-01" && $rel['cst_date']!="0000-00-00")
		{
			$cst_date=date('d-m-Y',strtotime($rel['cst_date']));
		}
		if($rel['st_date']!="1970-01-01" && $rel['st_date']!="0000-00-00")
		{
			$st_date=date('d-m-Y',strtotime($rel['st_date']));
		}
		
		$ass_array=explode(",",$rel['cust_assign_user']);
		$user_name=$rel['user_name'];
	
	}
	
$com_sel=$dbcon->query("select * from tbl_company where company_id='$_SESSION[company_id]'");
$r_sel=mysqli_fetch_array($com_sel);

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
		<?php 
			//include_once('../include/quick_link.php');
		?>
			<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
							<!--<span class="pull-right">
								<a href="<?=ROOT.'customer_import'?>" class="btn btn-primary"><i class="fa fa-upload" aria-hidden="true"></i> Import Customer</a>
							</span>-->
							
							<h3><?=$mode.' '.$form?></h3>
							<div class="text-center">Owner : <strong><?=$user_name?></strong></div>
						</header>	
						<div class="">
							<ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.CRM_ROOT.'crm_master'?>">CRM Masters</a></li>
							  <li><a href="<?=ROOT.'customer_list'?>"><?=$form?> List</a></li>
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
					  New <?=$form?>
					</header>	
					<div class="panel-body ">
					<form class="form-horizontal" role="form" id="cust_add" action="javascript:;" method="post" name="cust_add">
						<div class="row">
						
						<div class="col-md-12">
						
							<!--<div class="col-md-6">
							  <div class="form-group">
								  <label class="col-md-4 control-label">Party Type*</label>
									<div class="col-md-8">
										<div class="col-md-3">
											<label>
												<input type="radio" class="form-control" id="party_type_both" name="party_type" value="0" <?=(!$rel['party_type'] || $rel['party_type']=='0')?'checked':''?>> Both
											</label>
										</div>
										<div class="col-md-3">
											<label>
												<input type="radio" class="form-control" id="party_type_cust" name="party_type" value="1" <?=($rel['party_type']=='1')?'checked':''?>> Customer
											</label>
										</div>
										<div class="col-md-3">
											<label>
												<input type="radio" class="form-control" id="party_type_ven" name="party_type" value="2" <?=($rel['party_type']=='2')?'checked':''?>> Vendor
											</label>
										</div>
									</div>
							  </div>							 
							</div>-->
					
							
							<div class="col-md-6">
							  <div class="form-group">
								  <label for="Product Type" class="col-md-4 control-label">Party Code</label>
								  <div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" id="cust_code" name="cust_code" placeholder="Customer Code"  value="<?php if($mode=='Edit'){ echo $rel['cust_code']; } else { echo get_customer_code($dbcon); } ?>" readonly />
									
									<input type="hidden" class="form-control" id="cust_code_series" name="cust_code_series" placeholder="Customer Code Series"  value="<?=get_customer_code_series($dbcon);?>" readonly />
								  </div>
							  </div>
							</div>
							
						</div> 
						
						<div class="col-md-12">
						
							<div class="col-md-6">
							  <div class="form-group">
								  <label for="Product Type" class="col-md-4 control-label">Party Category*</label>
								  <div class="col-md-8 col-xs-11">
									<select class="select2" name="cust_cat" id="cust_cat">
										<option value="">--Select Party Category--</option>
										<?=get_customer_category($dbcon,$rel['cust_cat']);?>
									</select>
								  </div>
							  </div>							 
							</div>
					
							
							<div class="col-md-6">
							  <div class="form-group">
								  <label for="Product Type" class="col-md-4 control-label">Company Name</label>
								  <div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" id="cust_name" name="cust_name" placeholder=""  value="<?=$rel['cust_name'];?>" />
								  </div>
							  </div>
							</div>
							
						</div> 
						
						<div class="col-md-12">
						
							<div class="col-md-12">
							  <div class="form-group">
								  <label for="Product Type" class="col-md-2 control-label">Description / Notes </label>
								  <div class="col-md-10 col-xs-11">
									<textarea class="form-control" name="cust_desc" id="cust_desc" maxlength="300"><?=$rel['cust_desc']?></textarea>
									<span id="rchars">300</span> Character(s) Remaining
								  </div>
							  </div>							 
							</div>
							
						</div> 
						
						<div class="col-md-12" style="margin-top:5px;"> 
							
							<header class="panel-heading breadcrumb text-center">
							   <h3>Additional Information</h3>
							</header>
							
							
							<div class="col-md-12">
								
								<div class="col-md-6">
								  <div class="form-group">
									  <label for="Product Type" class="col-md-4 control-label">Party Industry *</label>
									  <div class="col-md-8 col-xs-11">
										<select class="select2" name="cust_ind" id="cust_ind">
											<option value="">--Select Party Industry--</option>
											<?=get_customer_industries($dbcon,$rel['cust_ind']);?>
										</select>
									  </div>
								  </div>							 
								</div>
								
								
								<div class="col-md-6">
								  <div class="form-group">
									  <label for="Product Type" class="col-md-4 control-label">Customer Type *</label>
									  <div class="col-md-8 col-xs-11">
										<select class="select2" name="cust_type" id="cust_type">
											<option value="">--Select Customer Type--</option>
											<?=get_customer_master_type($dbcon,$rel['cust_type']);?>
										</select>
									  </div>
								  </div>							 
								</div>
						
							</div>
							
							<div class="col-md-12">
								
								<div class="col-md-6">
								  <div class="form-group">
									  <label for="Product Type" class="col-md-4 control-label">Source / Refer By *</label>
									  <div class="col-md-8 col-xs-11">
										<select class="select2" name="cust_source" id="cust_source">
											<?=get_refer_by($dbcon,$rel['cust_source']);?>
										</select>
									  </div>
								  </div>							 
								</div>
								
								
								<div class="col-md-6">
								  <div class="form-group">
									  <label for="Product Type" class="col-md-4 control-label">Gst No </label>
									  <div class="col-md-8 col-xs-11">
										<input type="text" class="form-control" name="cust_gst" id="cust_gst" value="<?=$rel['cust_gst']?>" />
									  </div>
								  </div>							 
								</div>
						
							</div>
							
							<div class="col-md-12">
								
								<div class="col-md-6">
								  <div class="form-group">
									  <label for="Product Type" class="col-md-4 control-label">Mobile *</label>
									  <div class="col-md-8 col-xs-11">
										<input type="text" class="form-control" name="cust_mobile" id="cust_mobile" value="<?=$rel['cust_mobile']?>" />
									  </div>
								  </div>							 
								</div>
								
								
								<div class="col-md-6">
								  <div class="form-group">
									  <label for="Product Type" class="col-md-4 control-label">E-mail *</label>
									  <div class="col-md-8 col-xs-11">
										<input type="text" class="form-control" name="cust_email" id="cust_email" value="<?=$rel['cust_email']?>" />
									  </div>
								  </div>							 
								</div>
						
							</div>
							
						</div>
						
						<div class="col-md-12" style="margin-top:5px;"> 
							
							<header class="panel-heading breadcrumb text-center">
							   <h3>Address Details</h3>
							</header>
							
							<table class="table table-bordered">
								<thead>
									<tr>
										<th>Location</th>
										<th>Street</th>
										<th>Country</th>
										<th>State</th>
										<th>City</th>
										<th>Zip / Postal Code</th>
										<th></th>
									</tr>
									
									<tr>
										<td><input type="text" class="form-control" name="c_add_location" id="c_add_location" /></td>
										<td><input type="text" class="form-control" name="c_add_street" id="c_add_street" /></td>
										<td>
											<select class="select2" name="c_add_country" id="c_add_country" onChange="load_state(this.value,'c_add_state','')">
												<?=get_country($dbcon,$countryid)?>				
											</select>
										</td>
										<td>
											<select class="select2" name="c_add_state" id="c_add_state" onChange="load_city(this.value,'c_add_city','')">
												<option value="">Select State</option>	
												<?//=getstate($dbcon,$rel['stateid'])?>				
											</select>
										</td>
										<td>
											<select class="select2" name="c_add_city" id="c_add_city">
												<option value="">Select City</option>	
											</select>
										</td>
										<td><input type="number" class="form-control" name="c_add_zip" id="c_add_zip" /></td>
										<td><input type="button" class="btn btn-success" value="Add" onclick="add_cust_address()" id="add_ad_btn" />
											<input type="hidden" class="form-control" name="edit_add_id" id="edit_add_id" />
										</td>
									</tr>
								</thead>
								
								<tbody id="cust_address_details">
									
								</tbody>
								
							</table>
							
						</div>
						
						<div class="col-md-12" style="margin-top:5px;"> 
							
							<header class="panel-heading breadcrumb text-center">
							   <h3>Contact Details</h3>
							</header>
							
							<table class="table table-bordered">
								<thead>
									<tr>
										<th>First Name</th>
										<th>Last Name</th>
										<th>Email</th>
										<th>Mobile</th>
										<th>Phone</th>
										<th>Job Title</th>
										<th></th>
									</tr>
									
									<tr>
										<td><input type="text" class="form-control" name="con_first" id="con_first" /></td>
										<td><input type="text" class="form-control" name="con_last" id="con_last" /></td>
										<td><input type="email" class="form-control" name="com_email" id="com_email" /></td>
										<td><input type="number" class="form-control" name="con_mobile" id="con_mobile" /></td>
										<td><input type="text" class="form-control" name="con_phone" id="con_phone" /></td>
										<td><input type="text" class="form-control" name="con_job" id="con_job" /></td>
										<td><input type="button" class="btn btn-success" value="Add" onclick="add_cust_contact()" id="add_btn_contact" />
											<input type="hidden" class="form-control" name="edit_con_id" id="edit_con_id" />
										</td>
									</tr>
								</thead>
								
								<tbody id="cust_contact_details">
									
								</tbody>
							</table>
							
						</div>
						<?php /*
						<div class="col-md-12" style="margin-top:5px;"> 
							
							<header class="panel-heading breadcrumb text-center">
							   <h3>Assign User</h3>
							</header>
							
							<div class="col-md-12">
								
								<div class="col-md-12">
								  <div class="form-group">
									  <label for="Product Type" class="col-md-2 control-label">Assign User </label>
									  <div class="col-md-10 col-xs-11">
										<select class="select2" name="cust_assign_user[]" id="cust_assign_user" multiple>
											<option value="">--Assign User--</option>
											<?php 
												
												if($mode=='Edit')
												{
													$qry="select * from users where active=0 and user_id!='$custid'";
													$user_report_arr=explode(",",$rel['user_report']);
													//print_r($user_report_arr);
												}
												else
												{
													$qry="select * from users where active=0";
												}
												$rs_state=$dbcon->query($qry);		
												while($row=mysqli_fetch_array($rs_state))
												{
											
											?>
											<option value="<?php echo $row['user_id']; ?>" <?php if(in_array($row['user_id'],$ass_array)){ echo "selected"; } ?> ><?php echo $row['user_name']; ?></option>
											<?php } ?>
										</select>
									  </div>
								  </div>							 
								</div>
								
							</div>
							
						</div>
						*/ ?>
						<!--
						<div class="col-md-12" style="margin-top:5px;"> 
							
							<header class="panel-heading breadcrumb text-center">
							   <h3>Party Existing Details</h3>
							</header>
							
							<table class="table table-bordered">
								<thead>
									<tr>
										<th>Type</th>
										<th>Products</th>
										<th>Remark</th>
										
										<th></th>
									</tr>
									
									<tr>
										<td><input type="text" class="form-control" name="ext_type" id="ext_type" /></td>
										<td>
											<select class="select2" name="ext_product" id="ext_product">
												<?//=getproduct($dbcon,0,'')?>
											</select>
										</td>
										<td><input type="text" class="form-control" name="ext_remark" id="ext_remark" /></td>
										
										<td><input type="button" class="btn btn-success" value="Add" onclick="add_exist()" id="add_exist_btn" />
											<input type="hidden" class="form-control" name="edit_exist_id" id="edit_exist_id" />
										</td>
									</tr>
								</thead>
								
								<tbody id="cust_exist_details">
									
								</tbody>
							</table>
							
						</div>
						-->
						
						</div><!--Vendor row end-->	
							<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
							<input type='hidden' name='eid' id='eid' value='<?php if($mode=='Edit'){ echo $rel['cust_id']; } else { echo "0"; }?>' />				  
								<div class="col-md-12" style="margin-top:5px;"> 
									<div class="col-md-2"></div>
									<button type="submit" class="btn btn-success">Submit</button> &nbsp;
									<a href="<?=ROOT.'customer_list'?>" type="button" class="btn btn-danger">Cancel</a><div class="col-md-3"></div>					
								
								</div>
						  </form>
</div>	
					</section>
				</div>
			  </div>
			  <!--state overview end-->
          </section>
      </section>
      <!--main content end-->
      <!--footer start-->
	<?php //include_once('../include/add_city.php');?>
	<?php //include_once('../include/add_state.php');?>
	
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
   <script src="<?=ROOT?>js/app/customer.js?<?=time()?>"></script>
   <script src="<?=ROOT?>js/app/state_mst.js?<?=time()?>"></script>
   <script src="<?=ROOT?>js/app/city_mst.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
var maxLength = 300;
<?php if($mode=="Edit"){	?>
	var str_len = "<?= strlen($rel['cust_desc']); ?>";
	var textlen = maxLength - str_len;
	$('#rchars').text(textlen);
<?php } ?>
$('#cust_desc').keyup(function() {
  var textlen = maxLength - $(this).val().length;
  $('#rchars').text(textlen);
});
$('#cust_gst').on('change', function () {
    var statecode = $(this).val().substring(0, 2);
    var pancarno = $(this).val().substring(2, 12);
    var entityNumber = $(this).val().substring(12, 13);
    var defaultZvalue = $(this).val().substring(13, 14);
    var checksumdigit = $(this).val().substring(14, 15);
    if ($(this).val().length != 15) {
        alert('GST Number is invalid');
        $(this).focus();
        return false;
    }
    if (pancarno.length != 10) {
        alert('GST number is invalid ');
        $(this).focus();
        return false;
    }
    if (defaultZvalue !== 'Z') {
        alert('GST Number is invalid Z not in Entered Gst Number');
        $(this).focus();
    }

    if ($.isNumeric(statecode)) {
        $('#gst_state_code').val(statecode).trigger('change');
    } else {
        alert('Please Enter Valid State Code');
        $(this).focus();
    }

});
</script>
	<?php
	if($mode=="Edit"){
		echo "<script>load_state(".$countryid.",'c_add_state',".$stateid.")</script>";
		//echo "<script>load_city(".$stateid.",'c_add_city',".$cityid.")</script>";
	}
	else{
		echo "<script>load_state(".$countryid.",'c_add_state',".$stateid.")</script>";
		// echo "<script>load_city(1,'c_add_city',".$cityid.")</script>";
	}
	?>


  </body>
</html>
