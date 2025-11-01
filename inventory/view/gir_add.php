<?php 


	session_start();
	include('../include/urlfile.php');	
	$form="G.I.R";
	$countryid='101';
	$stateid='1';
	$cityid='1';

	$branch_id = $_SESSION['branch_id'];
	if(strpos($_SERVER['REQUEST_URI'], "gir_edit")==true){
		$mode="Edit";
		$gir_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from pro_gir where pro_gir_id=$gir_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$back="gir_list";
	}
	else{
		$mode="Add";
		$grn_date=date('d-m-Y');
		$back="gir_list";
	}
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	/* START jayesh for auto generate number with auto number */
	$q="SELECT MAX(pro_gir_id) as gir_id FROM pro_gir";
	$res=mysqli_fetch_assoc($dbcon->query($q));
	$auto_gir_id = $res['gir_id'];
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>GIR</title>
		<?php include_once($include.'include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<?php //include_once('../include/equick_link.php');?>
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.INVENTORY_ROOT.'gir_list'?>"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									New <?=$form?>
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="gir_add" action="javascript:;" method="post" name="gir_add" enctype="multipart/form-data">
										<div class="row"> 
											<div class="col-md-12" style="margin-top:10px;">
												<div class="col-md-4">
													<label class="col-md-4 control-label" style="">GIR Type*</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<select class="select2" name="gir_type" id="gir_type" title="Select GIR TYPE" onchange="get_bill_type(this.value);"  required>
														
															<option value="">--Select GIR Type--</option>
															<option value="I" <?=($rel['gir_type']=='I')?'selected':''?>> Inward </option>
															<option value="O" <?=($rel['gir_type']=='O')?'selected':''?>> Outward </option>
														</select>
													</div>  
												</div>
												<div class="col-md-4">
													<label class="col-md-4 control-label" style="">GIR Bill Type*</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<select class="select2" name="gir_bill_type" id="gir_bill_type" title="Select GIR BILL TYPE" onchange="get_vender_by_bill_type(this.value);"  required>
															
														</select>
													</div>  
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">G.I.R. No.*</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="gir_no" name="gir_no" class="form-control" title="GIR No." value="<?php if($mode=="Edit"){echo $rel['gir_no']; }else {echo $auto_gir_id; }?>" placeholder="GIR No" readonly>
														</div>
													</div>
												</div>	
											</div>
											<div class="col-md-12" style="margin-top:10px;">
												
												<div class="col-md-4">
													<label class="col-md-4 control-label" style="">Vendor*</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<select class="select2" name="vender_id" id="vender_id" title="Select Vender"  >														
														</select>
													</div>  
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Challan No *</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="gir_chalan_no" name="gir_chalan_no" class="form-control" title="Challan No." value="<?=$rel['gir_chalan_no']?>" placeholder="Challan No">
														</div>
													</div>
												</div>
												
												<?php if($mode=="Add"){ 
													$ttrt="required";
												}else{
													$ttrt="";
												}
												?>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Upload Receipt *</label>
														<div class="col-md-8 col-xs-11">
																<input type="file" class="form-control" id="gir_file" name="gir_file[]" multiple="multiple" accept="image/*" <?=$ttrt?> />
														</div>
													</div>
												</div>
												
											</div>
											
											<div class="col-md-12" style="margin-top:10px;"></div>	
											
												<div class="col-md-6">
													<div class="form-group">
														
														<div class="col-md-2">
														<?php if($mode=='Edit'){
															$get_attch_qry="select * from pro_gir_attch where  gir_id=".$rel['pro_gir_id'];
															$attch_rs=$dbcon->query($get_attch_qry);
															while($attch_rel=mysqli_fetch_assoc($attch_rs)){
														?>
															<a href="<?=ROOT.INVENTORY_ROOT.RECEIPT_FILE_VWING.$attch_rel['gir_file']?>" class="btn btn-xs btn-primary" target="_blank" style="margin-bottom: 2px;"><i class="fa fa-eye"></i>  </a> 
															<button type="button" onClick="delete_attch(<?=$attch_rel['gir_attch_id']?>)" class="btn btn-xs btn-danger" target="_blank" style="margin-bottom: 2px;"><i class="fa fa-trash-o"></i></button>
															<br/>
														<?php } }?>
														</div>
													</div> 
												</div>
												<div class="clearfix"></div>	
											</div>
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='eid' id='eid' value='<?=$rel['pro_gir_id']?>' />
										
											<input type='hidden' name='back' id='back' value='<?=$back?>' />
											<input type='hidden' name='pmode' id='pmode' value='<?=$pmode?>' />
											<div class="clearfix"></div>	
											<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
											<a href="<?=ROOT.'gir_list'?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-4"></div>
										</div>
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/gir.js?<?=time()?>"></script>
		<script>
			//$('#container').addClass('sidebar-closed');
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			$(".form_datetime").datetimepicker({
				format: 'dd-mm-yyyy hh:ii',
				autoclose: true,
				todayBtn: true,
				pickerPosition: "bottom-left"
			}); 
			
			
		</script> 
		<?php
		 if($mode=="Edit"){
			echo "<script>get_bill_type('".$rel['gir_type']."','".$rel['gir_bill_type']."');</script>";
			echo "<script>get_vender_by_bill_type('".$rel['gir_bill_type']."','".$rel['vender_id']."');</script>";
		 }
         ?>
	</body>
</html>