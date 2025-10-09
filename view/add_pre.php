<?php 
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		PRE_VIEW
    ]);
    if(!in_array(PRE_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
	
	if(strpos($_SERVER['REQUEST_URI'], "pre_edit") == true) {
       if(!in_array(PRE_VIEW,$bulkAccessArray)){
           header("Location: ".DOMAIN."permission_access");
       }
       
       $mode = "Edit";
       $pre_id = $dbcon->real_escape_string($_REQUEST['id']);
   
       $query = "select * from tbl_pre where pre_id=".$pre_id;
	   $rel = mysqli_fetch_assoc($dbcon->query($query));
	   $pre_no = $rel['pre_no'];
	   $date = date('d-m-Y',strtotime($rel['pre_date']));
	   $branch_id = $rel['branch_id'];
    }else{
		$mode = 'Add';
		$pre_no=load_common_no($dbcon,21);
		$date = date("d-m-Y");
		$branch_id = $_SESSION['branch_id'];
	}	
	$form="Indent";
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
			<?php include_once('../include/include_top_menu.php');?>
			<?php include_once('../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li class="active"><?=$form?> Add</li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									
								</header>	
								
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="pre_add" action="javascript:;" method="post" name="pre_add">
									<div class="row">
										<div class="col-md-12">	
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Pre No*</label>
													<div class="col-md-6">
													  <input id="pre_no" name="pre_no" type="text" class="form-control" title="Enter Pre No" value="<?=$pre_no?>" placeholder="Pre No" readonly >
													</div>
												</div>
											</div>
											
											<div class="col-md-6">
												<div class="form-group">
												   <label class="col-md-3 control-label">Date*</label>
												   <div class="col-md-6">
													  <input id="pre_date" name="pre_date" type="text" class="form-control default-date-picker required valid" title="Enter Pre Date" value="<?=$date?>" placeholder="Pre Date" >
													</div>
												</div>
											</div>
											
											<div class="col-md-6">
												 <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'','3','6'); ?>
											</div>
										</div>
										
										<div class="col-md-12" style="margin-top:30px">
											<div class="form-group">
												<table cellspacing="10" style="border-collapse:inherit; " id="product_list" class="display table table-bordered table-striped">
													<tr>
														<th width="30%">Choose Item</th>
														<th width="10%">Qty</th>
														<th width="10%">Rate</th>
														<th width="20%">Vender</th>
														<th width="20%">Attached Document</th>
														<th width="10%">Action</th>
													</tr>
													
													<tr>
														<td>
															<select class="select2 selproduct"  title="Select product" name="product_id" id="product_id" onchange="product_detail(this.value)">
																<?=getproduct($dbcon,"");?>
															</select>
														</td>
														<td>
															<input type="number"  title="Enter Qty" min="0" id="product_qty" name="product_qty"  class="form-control" />
															<br>
															<span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs" id="unit_show"></span>
														</td>
														
														<td>
															<input type="number"  title="Enter Rate" min="0" id="rate" name="rate"  class="form-control" />
														</td>
														<td>
															<select class="select2"  title="Select Vender" name="vender_id" id="vender_id" onchange="new_vendor(this.value)">
																<?=get_vendor($dbcon,$id)?>
															</select>
															<br></br>
															<input type="text" name="vendor_name" id="vendor_name" title="Select Vender Name" style="display:none" class="form-control">
														</td>
														<td>
															<input type="file" title="Attach Document" name="att_doc" id="att_doc" class="form-control">
															<br><br>
															<span id="uploaded_image"></span>
														</td>
														<td>
															<input type="hidden" id="img_name" name="img_name" value="">
															<input type="hidden" id="edit_id" name="edit_id" value="">
															<input type="button"  name="addrow" id="addrow" onClick="add_field()"  class="btn btn-primary" value="Add"/>
														</td>
													</tr>
												</table>
											</div>
										</div>
										
										<div class="col-md-12" id="show_prod_data" style="margin-top:40px;margin-bottom:30px">
											
										</div>
										
										<div class="col-md-12" style="text-align:center;">
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='eid' id='eid' value='<?=$pre_id?>' />
											<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
											<a href="<?=ROOT.'pre_list'?>" type="button" class="btn btn-danger">Cancel</a>
										</div>
									</div>
									</form>
								</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once('../include/footer.php');?>
		</section>
		<?php include_once('../include/include_js_file.php');?>   
		<script src="<?=ROOT?>js/app/pre.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			
			$("select.selproduct").select2({
				width: '100%',
				minimumInputLength: 2,

			});	
			
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			function cb(start, end) {
				$('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
			}
			cb(moment().subtract(29, 'days'), moment());


			$('.datepikerdemo').daterangepicker({       
				locale: {
					format: 'DD-MM-YYYY'
				},
				"autoApply": true,	
				"startDate": $('#from_date').val(),
				"endDate": $('#to_date').val(),	
				ranges: {
				   'Today': [moment(), moment()],
				   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
				   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
				   'This Month': [moment().startOf('month'), moment().endOf('month')],
				   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
				}
			}, cb);
			$('.date-set').click(function(){
				$('.datepikerdemo').trigger('click')
			});
		</script>
	</body>
</html>