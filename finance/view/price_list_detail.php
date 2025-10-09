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

if(strpos($_SERVER[REQUEST_URI], "cost_detail")==true){

	$mode="Edit";
	$bom_version_id=$dbcon->real_escape_string($_REQUEST['id']);
	$bom_product_id=$dbcon->real_escape_string($_REQUEST['id2']);
	$eid=$dbcon->real_escape_string($_REQUEST['id3']);

	//echo $bom_version_id;
	
}
if(strpos($_SERVER[REQUEST_URI], "cost_allocate")==true){
	
/*	$bomtrn_id=$dbcon->real_escape_string($_REQUEST['id']);
	
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
	$eid=$dbcon->real_escape_string($_REQUEST['id5']); // previous selected bom_version_id
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
		$sel_bom_version_id = 0;
	}else{
		$sel_bom_version_id = $rel['bom_version_id'];
	}
	
	*/
	
	$main_product_id=$dbcon->real_escape_string($_REQUEST['id']);
		
	$parent_id=$dbcon->real_escape_string($_REQUEST['id2']);
	$bom_product_id=$dbcon->real_escape_string($_REQUEST['id3']);
	$bom_version_id=$dbcon->real_escape_string($_REQUEST['id4']);
	$eid=$dbcon->real_escape_string($_REQUEST['id5']);
	
	$parent_detail=check_parent_price_list($dbcon,$product_id,$eid);
	
	$sel_p = $dbcon->query("select * from tbl_price_list_details where main_product='1' and price_list_id='$eid' and main_product_id='$main_product_id'");
	$r_p = brp_mysqli_fetch_array($sel_p);
	
	//echo $bom_product_id;
	$bom_current_level=bom_current_level_pricelist($dbcon,$bom_product_id);
	$new_level = $bom_current_level+1;
	//echo $bom_current_level;
	
	//get auto id from product 
	
	$sel_auto = $dbcon->query("select * from tbl_price_list_details where price_list_id='$eid' and product_id='$bom_product_id' and main_product_id='$main_product_id' order by price_list_detail_id desc limit 0,1");
	$r_auto = brp_mysqli_fetch_array($sel_auto);
	
	$auto_id=$r_auto['price_list_detail_id'];
	
	//echo $auto_id;
}
	?>

	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>Price List Details</title>
		<?php include_once($include.'include_css_file.php');?>
		<style>
			.text-center
			{
				text-align:center !important;
			}
		</style>
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
										
										<li><a href="<?=ROOT.'finance/price_list'?>"> Price List</a></li>
										
										<?php if(strpos($_SERVER[REQUEST_URI], "cost_allocate")==true){ ?>
											
											<li><a href="<?=ROOT.FINANCE_ROOT.'price_list_product/'.$r_p['product_id']."/".$eid; ?>">Create List</a></li>
											
											<li><a href="<?=ROOT.FINANCE_ROOT.'cost_detail/'.$eid."/".$r_p['product_id']."/".$eid; ?>"><?=get_pro_field($dbcon,$r_p['product_id'],'product_name'); ?></a></li>
											
											
											
											<?php 
												//echo "select * from tbl_price_list_details where price_list_id='$eid' and bom_level = '$bom_current_level+1' and parent_id='$bom_product_id'";
												
												//echo "select * from tbl_price_list_details where price_list_id='$eid' and bom_level < '$new_level' and bom_level > 1 and main_product_id='$r_p[product_id]'  group by parent_id order by parent_id desc"; 
												
												$sel=$dbcon->query("select * from tbl_price_list_details where price_list_id='$eid' and bom_level < '$new_level' and bom_level > 1 and main_product_id='$r_p[product_id]'  group by parent_id order by parent_id desc");
												
												//echo brp_mysqli_num_rows($sel);
												
												while($row=brp_mysqli_fetch_array($sel))
												{
													if($row['bom_level']!=$new_level)
													{
														$parent_id1 = get_parent_price_list($dbcon,$row['parent_id'],$eid);
														$bom_product_id1 = $row['parent_id'];
														$bom_version_id1 = get_bom_verion_price_list($dbcon,$row['parent_id'],$eid);
													}
													else
													{
														$parent_id1 = $parent_id;
														$bom_product_id1 = $bom_product_id;
														$bom_version_id1 = $bom_version_id;
													}
													
													if(get_pro_field($dbcon,$row['parent_id'],'product_name')!='')
													{
													?>
													<li><a href="<?=ROOT.FINANCE_ROOT.'cost_allocate/'.$main_product_id."/".$parent_id1."/".$bom_product_id1."/".$bom_version_id1."/".$eid; ?>"><?=get_pro_field($dbcon,$row['parent_id'],'product_name');?></a></li>
													
													<?php } } ?>
											
											<li>
												<a href="<?=ROOT.FINANCE_ROOT.'cost_allocate/'.$main_product_id."/".$parent_id."/".$bom_product_id."/".$bom_version_id."/".$eid; ?>"><?=get_pro_field($dbcon,$bom_product_id,'product_name');?></a>
											</li>
											
											<!--<li><a href="<?=ROOT.FINANCE_ROOT.'cost_detail/'.$sel_bom_version_id."/".$parent_id."/".$eid; ?>"><?=$bom_product_id;?></a></li> -->
											
										<?php } else { ?>
											
											<li><a href="<?=ROOT.FINANCE_ROOT.'price_list_product/'.$bom_product_id."/".$eid; ?>">Price List Create</a></li>
											
										<?php } ?>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
							
							<input type="hidden" class="form-control" name="bom_version_id" id="bom_version_id" value="<?=$bom_version_id;?>" />
							<input type="hidden" class="form-control" name="bom_product_id" id="bom_product_id" value="<?=$bom_product_id;?>" />
							
							<input type="hidden" name="bom_id" id="bom_id" value="<?=isset($rel['p_bom_id']) ? $rel['p_bom_id']:$rel['bom_id'] ?>" />
							<input type="hidden" name="sel_product_id" id="sel_product_id" value="<?php echo $rel['product_id']; ?>"  class="bom_allocate">
							
							<input type="hidden" name="main_product_id" id="main_product_id" value="<?php echo $main_product_id; ?>"  class="bom_allocate">
							
							<input type="hidden" name="sel_bom_version_id" id="sel_bom_version_id" value="<?=$sel_bom_version_id?>" />
							
							<input type="hidden" name="eid" id="eid" value="<?=$eid?>" />
							
							<div id="bom_productdata"></div>
							
							
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
<?php if(strpos($_SERVER[REQUEST_URI], "cost_detail")==true){ ?>
<script>
	$(document).ready(function() {
	//load_datatable();
	//show_data();
	
	//open checkbox division edit time
	//alert('cost_detail');
	load_price_list_detail();
	//alert("hii");
	
});
</script>
<?php } if(strpos($_SERVER[REQUEST_URI], "cost_allocate")==true) { ?>
<script>
	$(document).ready(function() {
	//alert('cost_detail_all');
	show_alloted_data();
	//alert("hello");
});
</script>
<?php } ?>
</html>
