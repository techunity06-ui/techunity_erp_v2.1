<?php 
session_start();

$path = '../../';
$include = '../../include/';
$include1 = '../include/';
include_once($path."config/config.php");
include_once($path."config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

$form="Price";
$alloted=0;
$multiple=1;
$_SESSION['sitemap']=array();

$p_name=isset($_GET['main_id'])?$_GET['main_id']:'';
$base_qty_read_only='';
$conversion_qty_read_only='';
$id2=0;
$uniq=rand();

$setconf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
$set_conf=mysqli_fetch_assoc($dbcon->query($setconf));
$type_conf = $set_conf['production_pro_type'];
$pro_search = $set_conf['bom_pro_search'];	

$sel_bom_version_id = "";
	// echo "<pre>";
	// print_r($_SESSION);
	// EXIT;

if(strpos($_SERVER['REQUEST_URI'], "cost_detail")==true){

	$mode="Edit";
	$bom_version_id=$dbcon->real_escape_string($_REQUEST['id']);
	$bom_product_id=$dbcon->real_escape_string($_REQUEST['id2']);

}
if(strpos($_SERVER['REQUEST_URI'], "cost_detail_allocate")==true){
	
	$bomtrn_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select mst.*,product.product_name,product.product_type as ptype,product.product_desc,product.product_setting_check,product.product_id,product.product_base_unit,u.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as mst 
	left join product_mst as product on product.product_id=mst.product_id 
	left join unit_mst as u on u.unitid=mst.product_base_unit
	left join unit_mst as cunit on cunit.unitid=mst.product_conv_unit
	where mst.bom_trn_status!=1 and mst.p_bom_id=".$bomtrn_id;

	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));

	$base_qty_read_only='yes';
	$conversion_qty_read_only='yes';
	$id2=$dbcon->real_escape_string($_REQUEST['id2']);
	$id4=$dbcon->real_escape_string($_REQUEST['id4']); // previous selected bom_version_id
	$_SESSION['sel_product_id'] = $dbcon->real_escape_string($_REQUEST['id2']);
	$id3=$dbcon->real_escape_string(base64_decode($_REQUEST['id3']));

	$ii=$id3.",".$bomtrn_id;


	$pro_base=sub_bom_qty($dbcon,$ii,"base");
	$con_base=sub_bom_qty($dbcon,$ii,"conv");

	$rel['product_base_qty']=$pro_base;
	$rel['product_conv_qty']=$con_base;

	$_SESSION['mastersids']=array();

	$alloted=1;

	$multiple=check_multiplication($dbcon,$rel['product_id']);

	if($rel['bom_version_id']==0){
		$sel_bom_version_id = "";
	}else{
		$sel_bom_version_id = $rel['bom_version_id'];
	}
	
}
	?>

	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>Price List Details</title>
		<?php include_once($include.'include_css_file.php');?>
		
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								
								<header class="panel-heading">
									<h3> <?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.FINANCE_ROOT.'price_list'?>"><?=$form?> List</a></li>
										<?php 

										$ids=explode(',', $id3);

										$prev_param_2='';
										$prev_param_1='';

										foreach ($ids as $key=>$bid) {
											$arr[]=$bid;
											$arr_str=implode(',', $arr);
											$str_encode=base64_encode($arr_str);
											if($key>=1){
												$prev_param_1=$ids[$key];
												$prev_param_2=$ids[$key-1];
											}else{
												$prev_param_1=$ids[$key];
												$prev_param_2=$ids[$key];
											}

											$map=generate_sitemap_modify($dbcon,$bid,'tbl_bom','bom_id','0',$prev_param_1,$prev_param_2,$str_encode,$key,$id4);


										}
										if(!empty($rel['product_name'])){
											$map=$map." / ".$rel['product_name'];
											echo $map;
										}else{
											echo $map;
										}

										?>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
							
							<input type="hidden" class="form-control" name="bom_version_id" id="bom_version_id" value="<?=$bom_version_id;?>" />
							<input type="hidden" class="form-control" name="bom_product_id" id="bom_product_id" value="<?=$bom_product_id;?>" />
							
							<div id="bom_productdata"></div>
							
							<div class="col-md-12">
								<?php if($alloted!='1'){ ?>
									<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
									<!--<button type="submit" class="btn btn-success" id="saveprint" name="saveprint" onClick="submit_bom()">Save and Print</button> &nbsp;-->
									<a href="<?=ROOT.PRODUCTION_ROOT.'bom_list'?>" type="button" class="btn btn-danger">Cancel</a>
									<div class="col-md-3"></div>
								<?php } ?>
							</div>		
						</div>

						</form>
					</div>	
				</section>
			</div>
		</div>
	</section>
</section>
<?php include_once($include1.'bom_copy_model.php');?>
<?php include_once($include.'footer.php');?>
</section>
<?php include_once($include.'include_js_file.php');?>   
<?php include_once($include.'allocate_process_model.php');?>  
<?php include_once($include1.'bom_process_add_model.php');?>   
<?php include_once($include1.'qc_model.php');?>   
<!-- Sanat :: Added copy bom model -->
<script src="<?=ROOT.FINANCE_ROOT ?>js/app/price_list_detail.js?<?php echo time(); ?>"></script>

</html>
