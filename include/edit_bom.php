<?php 
session_start();

$path = '../../';
$include1 = '../include/';
$include = '../../include/';
include_once($path."config/config.php");
include_once($path."config/session.php");
include_once($include."common_functions.php");

include_once($include."function_database_query.php");
$form="BOM";
$alloted=0;
$multiple=1;
$_SESSION['sitemap']=array();

$p_name=isset($_GET['main_id'])?$_GET['main_id']:'';
$base_qty_read_only='';
$conversion_qty_read_only='';
$id2=0;
$uniq=rand();

	// echo "<pre>";
	// print_r($_SESSION);
	// EXIT;

if(strpos($_SERVER[REQUEST_URI], "bom_edit")==true){

	$mode="Edit";
	$bom_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select bom.*,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name from tbl_bom as bom
	left join unit_mst as bunit on bunit.unitid=bom.product_base_unit
	left join unit_mst as cunit on cunit.unitid=bom.product_conv_unit
	where bom.bom_id=$bom_id";
	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
	$bom_no=$rel['bom_no'];
	$r_bom_id=$rel['bom_id'];
	$sel_bom_version_id = $rel['bom_version_id'];
	$date=date('d-m-Y',strtotime($rel['bom_date']));
	$_SESSION['bom_edit_id']=$rel['bom_id'];
	$_SESSION['bom_product_name']=$rel['bom_product'];

}
else{
	if(strpos($_SERVER[REQUEST_URI], "bom_allocate")==true){

	}else{
		unset($_SESSION['bom_edit_id']);
	}

		//$_SESSION[$uniq]=[];
	$mode="Add";
	$date=date('d-m-Y');
	$r_bom_id=0;
}

if(strpos($_SERVER[REQUEST_URI], "bom_allocate")==true){

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

	$id3=$dbcon->real_escape_string(base64_decode($_REQUEST['id3']));
		//var_dump($id3);
		//$id4=$dbcon->real_escape_string(base64_decode($_REQUEST['id4']));
		//var_dump($id3);
	$ii=$id3.",".$bomtrn_id;
		//var_dump($ii);

	$pro_base=sub_bom_qty($dbcon,$ii,"base");
	$con_base=sub_bom_qty($dbcon,$ii,"conv");

	$rel['product_base_qty']=$pro_base;
	$rel['product_conv_qty']=$con_base;

	$_SESSION['mastersids']=array();

		/* if($id2>0){
			$set="select * from tbl_bomtrn where bom_trn_status!=2 and bom_id=".$id2." and p_bom_id=".$bomtrn_id;
	    	$set_head=brp_mysqli_fetch_assoc($dbcon->query($set));
			$rel['product_base_qty']=$set_head['product_base_qty'];
	    	$rel['product_conv_qty']=$set_head['product_conv_qty'];
	    	$rel['product_base_unit']=$set_head['product_base_unit'];
	    	$rel['product_conv_unit']=$set_head['product_conv_unit'];
			
	    } */

	    $alloted=1;

	    $multiple=check_multiplication($dbcon,$rel['product_id']);
	}
	$sel_bom="select product_base_qty from tbl_bomtrn where bom_id='$r_bom_id' and  sale_product_id='$p_name'";
	$r_bom=brp_mysqli_fetch_assoc($dbcon->query($sel_bom));	
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=brp_mysqli_fetch_assoc($dbcon->query($set));
	
	

	?>

	<!DOCTYPE html>
	<html lang="en">
	<head>
		<?php include_once($include.'include_css_file.php');?>
		<style>
		.sel_bom_version{
			
			    color: #fff !important;
    background-color: #337ab7 !important;
    border-color: #2e6da4 !important;
		}
		<?phpif(!$bom_to_po_req){ ?>
			.po_req_mode{
				display:none;
			}
			<?php }?>

			<?php
			if($bom_actual_add){
				echo ".hide_act_add{display:none;}";
			}
			else{
				echo ".show_act_add{display:none;}";
			}
			?>
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
										<li><a href="<?=ROOT.PRODUCTION_ROOT.'bom_list'?>"><?=$form?> List</a></li>
										<?php 

									//get_breadcum($dbcon,$id3,$bomtrn_id,$id2);
										$ids=explode(',', $id3);
// 									echo "<pre>";
// print_r($ids);
// exit;

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
											$map=generate_sitemap_modify($dbcon,$bid,'tbl_bom','bom_id','0',$prev_param_1,$prev_param_2,$str_encode,$key);

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
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									New <?=$form?>
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="bom_add" action="javascript:;" method="post" name="bom_add">
										<div class="row">
											<div class="col-md-12">
												<?php if(strpos($_SERVER[REQUEST_URI], "bom_allocate")==true){ ?>
													<div class="col-md-12">
														<div class="form-group">
															<label class="col-md-4 control-label"> Product *</label>
															<div class="col-md-4 col-xs-11">
																<input type="text" class="form-control" name="prod_name" id="prod_name" value="<?php echo $rel['product_name']; ?>" readonly>
																<input type="hidden" name="sel_product_id" id="sel_product_id" value="<?php echo $rel['product_id']; ?>" onChange="load_bom_version_datatable();">
																<strong id="bom_duplicate" style='color:red;display:none'>BOM For This Product Already Exist</strong>
															</div>
														</div>
													</div>

												<?php } else { ?>
													<div class="col-md-12">
														<div class="form-group">
															<label class="col-md-4 control-label">Select Product *</label>
															<div class="col-md-4 col-xs-11">
																<select class="select2 mprdct" title="Select product" name="sel_product_id" id="sel_product_id" onchange="get_main_product(this.value);load_bom_version_datatable();load_product_data();hide_bom_version_form()"  >
																	<?=get_bom_product($dbcon,$rel['bom_product'],'0')?>
																</select>
																<strong id="bom_duplicate" style='color:red;display:none'>BOM For This Product Already Exist</strong>

															</div>
														</div>
													</div>
												<?php } ?>
											</div>
											<!-- <div class="panel-body">
				  <div class="adv-table">
				  <table class="display table table-bordered table-striped" id="bom_versiondata">
					  <thead>
						  <tr>
							  <th>#</th>
							  <th>BOM Version</th>
							  <th>BOM No</th>
							  <th>Drawing Revision</th>
							  <th>BOM Unit</th>
							  <th class="hidden-phone">Action</th>					  
						  </tr>
					  </thead>
					  <tbody>
					  </tbody>				 
				  </table>
				  </div>
				</div> -->
				<div id="bom_versiondata"></div>

				<div class="col-md-12 mbot30">
					<button type="button" class="btn btn-primary" id="add_version" name="add_version" onclick="show_bom_version_form()">Add Version</button>
					<button type="button" class="btn btn-success" style="display:none" id="save_version" onclick="save_bom_version()" name="save_version">Save</button>
					<button type="button" class="btn btn-danger" onclick="hide_bom_version_form()" style="display:none" id="cancel_version" name="cancel_version">Cancel</button>
					<button type="button" class="btn btn-primary" disabled id="assign_version" name="assign_version">Assign BOM</button>
					<button type="button" class="btn btn-primary" onclick="open_copy_bom_model()" id="copy_version" name="copy_version">Copy BOM</button>
				</div>
				<div class="col-md-12" id="row_bom_version" style="display: none;">
					<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">Serial No *</label>
							<div class="col-md-8 col-xs-11">
								<input id="bom_srno" name="bom_srno" type="text" class="form-control" title="Serial No" value="<?=$bom_srno?>" placeholder="Serial No" readonly required />

							</div>
						</div>
					</div>
					<input type="hidden" name="bom_version_id" id="bom_version_id" value="" />
					<input type="hidden" name="sel_bom_version_id" id="sel_bom_version_id" value="<?=@$sel_bom_version_id?>" />
					
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">BOM Version Name *</label>
							<div class="col-md-8 col-xs-11">
								<input id="version_name" name="version_name" type="text" class="form-control" title="BOM version name" value="" placeholder="Enter BOM version name"/>
								<strong id="version_name_req" style='color:red;display:none'>Enter BOM version name
									</strong>
							</div>
						</div>
					</div>	
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">BOM No *</label>
							<div class="col-md-8 col-xs-11">
								<input id="bom_version_no" name="bom_version_no" type="text" class="form-control" title="Enter BOM No" value="" placeholder="BOM No"  readonly  />

								
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">BOM Date*</label>
							<div class="col-md-8 col-xs-11">
								<input id="bom_date" name="bom_date" type="text" class="form-control default-date-picker required valid" title="BOM Date" value="<?=$date?>" <?=($readonly=='yes') ? readonly:'';?> placeholder="BOM Date">
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">BOM Unit QTY</label>
							<div class="col-md-8 col-xs-11">
								<input id="bom_unit_qty" name="bom_unit_qty" type="text" class="form-control" title="BOM Unit QTY" value="1" placeholder="Enter BOM Unit QTY">
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">Active BOM</label>
							<div class="col-md-2 col-xs-11">
								<input type="checkbox"  class="form-control" checked name="bom_active_status" id="bom_active_status" disabled value="1" />
							</div>
						</div>
					</div>
					</div>
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">Drawing No</label>
							<div class="col-md-8 col-xs-11">
								<input id="drawing_id" name="drawing_id" type="text" class="form-control" title="Drawing No" readonly value="" placeholder="Enter Drawing No">
								<input type="hidden" name="product_drawing_id"  onChange="get_revision_data(this.value)"  id="product_drawing_id" value="" />
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">Drawing Revision</label>
							<div class="col-md-8 col-xs-11">
								<select class="select2 drawing_revision" name="revision_id" id="revision_id" title="Select Drawing Revision" >
									
								</select>
								<input type="hidden" name="product_revision_id"  id="product_revision_id" value="" />
							</div>
						</div>
					</div>
					
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">Default BOM</label>
							<div class="col-md-2 col-xs-11">
								<input type="checkbox" name="is_default_bom"  class="form-control"  id="is_default_bom" value="1" />
							</div>
						</div>
					</div>
					
				</div>
				</div>
				<input type="hidden" name="bom_no" id="bom_no" value="<?=$bom_no?>" />
				<input type="hidden" name="bom_to_po_req" id="bom_to_po_req" value="<?php echo $bom_to_po_req; ?>" />
				<input type="hidden" name="bom_id" id="bom_id" value="<?=isset($rel['p_bom_id']) ? $rel['p_bom_id']:$rel['bom_id'] ?>" />

				<input type="hidden" name="main_bom_id" id="main_bom_id" value="<?=$rel['bom_id']?>" />
				<div class="col-md-12">
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">Base Quantity *</label>
							<div class="col-md-5 col-xs-11">
								<input type="number"  title="Enter Qty" min="0" id="base_qty" name="base_qty"  class="form-control" value="<?=
								@$rel['product_base_qty'] ? $rel['product_base_qty'] : 1?>" required onkeyup="convert_qty(1);" <?=($base_qty_read_only=='yes') ? 'readonly':'';?>  />
							</div>
							<div class="col-md-3 col-xs-11">
								<input type="text"  title="Base Unit"  id="base_unit_name" name="base_unit_name"  class="form-control" value="<?=$rel['base_unit_name']?>" readonly />

								<input type="hidden" name="base_unit" id="base_unit" value="<?=$rel['product_base_unit']?>" />
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">Conv Quantity*</label>
							<div class="col-md-5 col-xs-11">
								<input type="number"  title="Enter Qty" min="0" id="conv_qty" name="conv_qty"  class="form-control" value="<?= @$rel['product_conv_qty'] ? $rel['product_conv_qty'] : 1 ?>" onkeyup="convert_qty(2);" <?=($conversion_qty_read_only=='yes') ? readonly:'';?>  />
							</div>
							<div class="col-md-3 col-xs-11">
								<input type="text"  title="Convert Unit" id="conv_unit_name" name="conv_unit_name"  class="form-control" value="<?=$rel['conv_unit_name']?>" readonly />

								<input type="hidden" name="conv_unit" id="conv_unit" value="<?=$rel['product_conv_unit']?>" />
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="form-group">
						<table cellspacing="10" style="border-collapse:inherit;table-layout: fixed;" id="product_list" class="display table table-bordered table-striped">
							<tr id="field">
								<th width="20%" class="text-center">Type</th>
								<th width="25%" class="text-center">Product Detail</th>
								<th width="20%" class="text-center">Version</th>
								<th width="5%" class="text-center hide_act_add">Unit</th>
								<th width="10%" class="text-center hide_act_add">Quantity</th>
								<th width="5%" class="text-center hide_act_add">UOM</th>
								<th width="15%" class="text-center hide_act_add">ACtual Qty.</th>

								<th width="10%" class="text-center"></th>
							</tr>
							<tr id="field1">
								<td style="vertical-align:top;">
									<select class="select2 prtype" name="product_type" id="product_type" onChange="load_product(this.value);" title="Select Product Type" >
										<?=get_bom_producttype($dbcon,'');?>
									</select>
								</td>
								<td style="vertical-align:top;">
									<select class="select2 selproduct" title="Select product" name="product_id" id="product_id" onchange="load_product_types();load_product_version(this.value,'');load_product_detail(this.value);" >
										<option value="">Choose Product</option>
										<?=getproduct($dbcon,'');?>
									</select>
									<br/><br/>
									<div id="get_spec_div" style="display:none">


																	<!-- 
																	Width : <input type="text" class="form-control" name="product_width" id="product_width" value="<?=$mode=='Edit'?$rel['product_width']:0?>" onkeyup="get_ms_kg()" />

																	Height : <input type="text" class="form-control" name="product_height" id="product_height" value="<?=$mode=='Edit'?$rel['product_height']:0?>" onkeyup="get_ms_kg()" />
																	
																	Thickness : <input type="text" class="form-control" name="product_thickness" id="product_thickness" value="<?=$mode=='Edit'?$rel['product_thickness']:0?>" onkeyup="get_ms_kg()" />
																	
																	<input type="hidden" class="form-control" name="product_density" id="product_density" value="<?=$mode=='Edit'?$rel['product_density']:0?>" onkeyup="get_ms_kg()" /> -->
																	
																	<!-- <input type="text" class="form-control" name="product_kg" id="product_kg" value="<?=$mode=='Edit'?$rel['product_kg']:0?>" readonly /> 
																	
																		<input type="checkbox" name="set_kg" id="set_kg" value="0" onclick="set_kg_to_qty(this.value)" />SET -->
																	</div>
																</td>	
																<td>
																	<select class="select2 productversion" title="Select Version" name="pro_version_id" id="pro_version_id" onchange="get_product_version_qty(this.value)">
																		<option value="">Choose Product Version</option>

																	</select>
																</td>
																<td style="vertical-align:top;" class="hide_act_add">
																<!--<select class="form-control" id="product_base_unit" name="product_base_unit" >
																	<option value="">--select Unit--</option>
																	<?php //=getunit($dbcon);?>
																</select>-->
																<input class="form-control" type="text" name="product_base_unit_name" id="product_base_unit_name" value="" readonly />
																<input type="hidden" name="product_base_unit" id="product_base_unit"value="" />	

															</td>	
															<td style="vertical-align:top;" class="hide_act_add">
																<input type="number"  title="Enter Qty" min="0" id="product_base_qty" name="product_base_qty" onkeyup="product_convert_qty(1);" value="1"  class="form-control" />
																
																<input type="hidden" id="product_base_qty_hide" name="product_base_qty_hide" value="" />
																
															</td>
															<td style="vertical-align:top;" class="hide_act_add">
																<!--<select class="form-control" id="product_uom" name="product_uom" >
																	<option value="">--select UOM--</option>
																	<?php //=getunit($dbcon);?>
																</select>-->
																<input class="form-control" type="text" id="product_conv_unit_name" name="product_conv_unit_name" value="" readonly />
																
																<input type="hidden" name="product_conv_unit" id="product_conv_unit"value="" />
															</td>
															<td style="vertical-align:top;" class="hide_act_add">
																<input type="number"  title="Enter Qty" min="0" id="product_conv_qty" 
																
																name="product_conv_qty"  class="form-control" onkeyup="product_convert_qty(2);"  value="1"  />
																<!--onkeyup="product_convert_qty(2);"-->
																<input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />
																
																<input type="hidden"  title="" id="product_spec_hid" name="product_spec_hid"  class="form-control" />
																<input type="hidden"  title="" id="product_spec_hid_qty" name="product_spec_hid_qty"  class="form-control" />
																<input type="hidden"  title="" id="product_spec_act_qty" name="product_spec_act_qty"  class="form-control" />
															</td>		
															<td style="vertical-align:top;">
																<!-- Sanat :: comment below button :: 03-03-2021 -->
																<!-- <input type="button"  name="addrow" id="addrow" onClick="return add_field();" class="btn btn-primary" value="Add"/> -->
																<input type="button" id="addprocess" class="btn btn-primary" data-original-title="Add Process" data-toggle="tooltip" data-placement="top" onclick="open_add_bom_process_model();" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value="" />
															<input type='hidden' name='mode_edit' id='mode_edit' value="<?=$mode?>" />
															<input type='hidden' name='mode_edit_id' id='mode_edit_id' value="<?=$rel['bom_id']?>" />
															<input type='hidden' name='actual_qty' id='actual_qty' value="<?=$rel['bom_qty'];?>" />
															<input type='hidden' name='thread' id='thread' value="1" />
															<input type='hidden' name='level' id='level' value="1" />
															<input type='hidden' name='parent_id' id='parent_id' value="0" />
															<input type="hidden" name="" id="main_product" value="<?php if(isset($_GET['main_product'])){ echo $main_product; } else { echo $rel['bom_product']; } ?>" />
															<input type="hidden" name="p_bom_id" id="p_bom_id" value="" />
														</tr>
													</table>			
												</div>
											</div>
											<div class="col-md-8 col-md-offset-3">
												<div class="form-group">
													<div class="col-md-4">
														<input type="text" class="form-control" id="fil_product_search" placeholder="Search Product Name" value="" />
													</div>
												</div>
											</div>
											<div id="bom_productdata"></div>
											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-2 control-label">Remarks</label>
													<div class="col-md-6 col-xs-11">
														<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
													</div>
												</div>
											</div>	
											<div class="col-md-12">
												<?php if($alloted!='1'){ ?>
													<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
													<!--<button type="submit" class="btn btn-success" id="saveprint" name="saveprint" onClick="submit_bom()">Save and Print</button> &nbsp;-->
													<a href="<?=ROOT.PRODUCTION_ROOT.'bom_list'?>" type="button" class="btn btn-danger">Cancel</a>
													<div class="col-md-3"></div>
												<?php } ?>
											</div>		
										</div>
										<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
										
										<!--<input type="hidden" name="eid" id="eid" value="<?php //=isset($_GET['eid'])&& $_GET['eid']!=''?$bom_id:$r_bom_id ?>" />-->
										
										<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
										
										<input type="hidden" name="save_print" id="save_print" value="" />			
										<?phpif($bom_actual_add){
											//Entry Same Actual Quantity if first time 
											$upd_act_qty=$dbcon->query("update tbl_bomtrn as trn
												inner join tbl_bom as mst on mst.bom_id=trn.bom_id
												set trn.product_actual_qty=trn.product_qty  where mst.bom_id=".$rel['bom_id']." and bom_actual_add_status=0");
												?>	
												<input type='hidden' name='bom_actual_add' id='bom_actual_add' value='1' />		
												
											<?php} ?>
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
			<?php include_once($include.'allocate_process_model.php');?>  
			<?php include_once($include1.'bom_process_add_model.php');?>   

			<!-- Sanat :: Added copy bom model -->
			<?php include_once($include1.'bom_copy_model.php');?>
			
			<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/bom.js?<?php echo time(); ?>"></script>
			<script>
				$(".prtype,.mprdct").select2({
					width: '100%',
				});
				$(".selectoption").select2({
					width: '100%',
				});	
				$("#pro_version_id").select2({
		         	width: '100%'
		        });
				
				$(".drawing_revision").select2({
					width: '100%',
				});	

				$(".selproduct").select2({
					width: '100%',
					minimumInputLength: 2,

				});	
				$('.default-date-picker').datepicker({
					format: 'dd-mm-yyyy',
					autoclose: true
				});
				<?php 
				if($mode!='Add'){
					?>	
					$('#sales_order_id').select2('readonly',true);
					$('#sales_order_pro_id').select2('readonly',true);
					<?php }
					?>
					<?phpif($direct_add){?>
						load_sales_pro_data(<?=$rel['sales_order_id']?>);
						$('#sales_order_id').select2('readonly',true);
						<?php 
						$deleteid=delete_record('tbl_bomtrn', "bom_id=0", $dbcon);
					}
					?>
		<?php/*if($bom_clone){?>
			clone_bom_trn_data(<?=$bom_id?>);
			<?php }*/?>	
		</script>
		<?php
		if($mode=="Add"){
			echo "<script>get_series_no()</script>";
		} 
		
		if($readonly=='yes'){
			echo "<script>$('#sel_product_id').select2('readonly', true);</script>";
			// echo "<script></script>"
		}
		?>
	</body>
	<script type="text/javascript">
		var alloted="<?php echo $alloted; ?>";
		var id2="<?php echo $id2; ?>";
		var id3="<?php echo $id3; ?>";
		var id4="<?php echo $id4; ?>";
		var multiple_qty="<?php echo $multiple; ?>";
	</script>
	</html>