<?php 
session_start();

$path = '../../';
$include = '../../include/';
$include1 = '../include/';
include_once($path."config/config.php");
include_once($path."config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

$form="Price List View";

if(strpos($_SERVER['REQUEST_URI'], "price_list_view")==true){

	$id=$dbcon->real_escape_string($_REQUEST['id']);

	//echo $id;
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
										
										<li><a href="<?=ROOT.FINANCE_ROOT.'price_list'?>"> Price List</a></li>
										
										<li>Price List View</li>
											
											
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						
						<?php 

							$sel1 = $dbcon->query("select * from tbl_price_list where price_list_id='$id'");
							$row1 = brp_mysqli_fetch_array($sel1);

						//	echo "select * from tbl_price_list where price_list_id='$id'";
						 ?>

						<table class="table table-bordered table-hover table-striped">
							
							<tr>
								<th>Version</th>
								<td colspan="3"><?=$row1['price_list_version']?></td>
							</tr>

							<tr>
								<th>Effective Date</th>
								<td><?=date("d/m/y",strtotime($row1['price_list_effective_date']))?></td>
								<th>Expire Date</th>
								<td><?=date("d/m/Y",strtotime($row1['price_list_expire_date']))?></td>
							</tr>

							<tr>
								<th>Relase To</th>
								<td>
									
									<?php 

										if($row1['price_list_allocate_type']==0)
										{
											echo "To Group";
										}
										else
										{
											echo "To Indivuduals";
										}
									?>

								</td>
								<th>Relase For</th>
								<td>
									
									<?php 

										if($row1['price_list_allocate_type']==0)
										{	

											$gr_array = explode(",",$row1['price_list_allocate_to']);
											//print_r($gr_array);
											for($i=0;$i<=count($gr_array)-1;$i++)
											{
												echo get_id_detail($dbcon,"tbl_group","g_id",$gr_array[$i],"g_name").",<br>";
											}
										}

									?>

								</td>
							</tr>

							<tr>
								<th colspan="2">
									<?php if($row1['version_relase']==0) { ?>
									<a class="btn btn-warning relase_btn" style="color:white !important" onclick="relase_version(<?=$id;?>)"> Click Here To Relase This Version</a>
									<?php } else { ?>
									<a class="btn btn-success relase_btn_done" style="color:white !important;" > This Version Is Relased</a>
									<?php } ?>
								</th>
							</tr>

						</table>

						<table class="table table-bordered table-hover table-striped">
							
							<tr>
								
								<th>#</th>
								<th>Product Type</th>
								<th>Product Name</th>
								<th>Sale Price</th>
							</tr>

							<?php 

								$sel = $dbcon->query("select pd.*,pro.product_name,pro.product_id,pro.product_type,pt.product_type_name from tbl_price_list_details as pd left join product_mst as pro on pro.product_id=pd.product_id left join pro_ms_product_type as pt on pt.product_type_id=pro.product_type where price_list_id='$id'");
								$cnt=1;
								while($row = brp_mysqli_fetch_array($sel))
								{
							?>

								<tr>
									<th><?=$cnt;?></th>
									<th><?=$row['product_type_name'];?></th>
									<th><?=$row['product_name'];?></th>
									<th><?=$row['product_sale_price'];?></th>
								</tr>

							<?php

								$cnt++;
								}
							?>

						</table>


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
<script src="<?=ROOT.FINANCE_ROOT?>js/app/price_list.js?<?php echo time(); ?>"></script>
<?php if(strpos($_SERVER['REQUEST_URI'], "cost_detail")==true){ ?>
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
<?php } if(strpos($_SERVER['REQUEST_URI'], "cost_allocate")==true) { ?>
<script>
	$(document).ready(function() {
	//alert('cost_detail_all');
	show_alloted_data();
	//alert("hello");
});
</script>
<?php } ?>
</html>
