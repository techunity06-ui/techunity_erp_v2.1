<?php 
session_start();

include('../include/urlfile.php');	
include('../include/common_functions/common_functions.php');
$include = '../../include/';
$form="BOM";
$alloted=0;
$multiple=1;
$_SESSION['sitemap']=array();
$getspecialConfiguration=getspecialConfiguration($dbcon);
// error_reporting(E_ALL);

$p_name=isset($_GET['main_id'])?$_GET['main_id']:'';
$base_qty_read_only='';
$conversion_qty_read_only='';
$id2=0;
$uniq=rand();
$companyConfiguration=getCompanyConfiguration($dbcon);
$setconf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
$set_conf=mysqli_fetch_assoc($dbcon->query($setconf));
// echo "<pre>"; print_r($set_conf); exit;
$type_conf = $set_conf['production_pro_type'];
// 0 to 12
$pro_search = $set_conf['bom_pro_search'];	
// item,drawing,alias
$back_link = ROOT.PRODUCTION_ROOT.'bom_list';

$sel_bom_version_id = "";
	// echo "<pre>";
	// print_r($_SESSION);
	// EXIT;
$pro_readonly = "";
if(strpos($_SERVER['REQUEST_URI'], "bom_edit")==true){

	$mode="Edit";
	$bom_id=$dbcon->real_escape_string($_REQUEST['id']);
	// 1413
	$query="select bom.*,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name,(select bom_version_id from pro_ms_bom_version where pro_ms_bom_version.product_id = bom.bom_product and is_default_bom = 1 and bom_active_status = 0 and bom_version_status=0) as default_bom_version from tbl_bom as bom
	left join unit_mst as bunit on bunit.unitid=bom.product_base_unit
	left join unit_mst as cunit on cunit.unitid=bom.product_conv_unit
	left join pro_ms_bom_version as pv on pv.product_id=bom.bom_product
	where bom.bom_id=".$bom_id;

	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
	// echo "<pre>"; print_r($rel); exit;
	$bom_no=$rel['bom_no'];
	// var_dump($bom_no);
	$r_bom_id=$rel['bom_id'];
	
	$sel_bom_version_id = $rel['bom_version_id'];
	// sel_bom_version_id
	$date=date('d-m-Y',strtotime($rel['bom_date']));
	// 25-02-2025
	$_SESSION['bom_edit_id']=$rel['bom_id'];
	$_SESSION['bom_product_name']=$rel['bom_product'];
	// 4803
	$rel['product_id'] = $rel['bom_product'];
	//$bom_version_id=$dbcon->real_escape_string($_REQUEST['bom_version']);

	//$sel_bom_version_id = $bom_version_id;
	$pro_readonly = "readonly";

}
else{
	if(strpos($_SERVER['REQUEST_URI'], "bom_allocate")==true){
		
	}else{
		unset($_SESSION['bom_edit_id']);
	}

		//$_SESSION[$uniq]=[];
	$mode="Add";
	$date=date('d-m-Y');  //06-05-2025
	$r_bom_id=0;
}

if(strpos($_SERVER['REQUEST_URI'], "bom_allocate")!= false){
	// var_dump($_REQUEST['id']);
	$bomtrn_id=$dbcon->real_escape_string($_REQUEST['id']);
	// echo $bomtrn_id;
	$query="select mst.*,product.product_name,product.product_type as ptype,product.product_desc,product.product_setting_check,product.product_id,product.product_base_unit,u.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as mst 
	left join product_mst as product on product.product_id=mst.product_id 
	left join unit_mst as u on u.unitid=mst.product_base_unit
	left join unit_mst as cunit on cunit.unitid=mst.product_conv_unit
	where mst.bom_trn_status!=1 and mst.p_bom_id=".$bomtrn_id;

	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
	// echo "<pre>"; print_r($rel); exit;
	$base_qty_read_only='yes';
	$conversion_qty_read_only='yes';
	$id2=$dbcon->real_escape_string($_REQUEST['id2']);
	$id4=$dbcon->real_escape_string($_REQUEST['id4']); // previous selected bom_version_id
	$_SESSION['sel_product_id'] = $dbcon->real_escape_string($_REQUEST['id2']);
	$id3=$dbcon->real_escape_string(base64_decode($_REQUEST['id3']));


		// var_dump($id4);
		//$id4=$dbcon->real_escape_string(base64_decode($_REQUEST['id4']));
		//var_dump($id3);
	$ii=$id3.",".$bomtrn_id;


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

	    $multiple=check_multiplication($dbcon,$rel['product_id'],"");

	    if($rel['bom_version_id']==0){
	    	$sel_bom_version_id = "";
	    }else{
	    	$sel_bom_version_id = $rel['bom_version_id'];
	    }


	}
	
	if(strpos($_SERVER['REQUEST_URI'], "bom_assign")==true){

		$prd_id=$dbcon->real_escape_string($_REQUEST['id']);
		// echo $prd_id;
		$so_id=$dbcon->real_escape_string($_REQUEST['id2']); 
		$so_trans_id=$dbcon->real_escape_string($_REQUEST['id3']); 
		$so_bom_id=$dbcon->real_escape_string($_REQUEST['id4']); 
		$mode="Edit";
		$bom_id=$dbcon->real_escape_string($_REQUEST['id4']);
		$query="select bom.*,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name from tbl_bom as bom
		left join unit_mst as bunit on bunit.unitid=bom.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom.product_conv_unit
		where bom.bom_id=$bom_id";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
		$bom_no=$rel['bom_no'];
		$r_bom_id=$rel['bom_id'];
		$sel_bom_version_id = $rel['bom_version_id'];
		$date=date('d-m-Y',strtotime($rel['bom_date']));
	//$_SESSION['bom_edit_id']=$rel['bom_id'];

		$_SESSION['bom_product_name']=$rel['bom_product'];

		$prd_id=$dbcon->real_escape_string($_REQUEST['id']);
		$so_id=$dbcon->real_escape_string($_REQUEST['id2']); 
		$so_trans_id=$dbcon->real_escape_string($_REQUEST['id3']); 
		$so_bom_id=$dbcon->real_escape_string($_REQUEST['id4']); 
		$mode="Assign";
		$rel['product_id'] = $prd_id;
		$rel['bom_product'] = $prd_id;

		$back_link = ROOT.PRODUCTION_ROOT.'design_department_get_sales_order_details';

	}
	

	if(strpos($_SERVER['REQUEST_URI'], "bom_assign_store_order")==true){

		$prd_id=$dbcon->real_escape_string($_REQUEST['id']);
		$store_order_id=$dbcon->real_escape_string($_REQUEST['id2']); 
		$so_bom_id=$dbcon->real_escape_string($_REQUEST['id3']); 
	
		$mode="Edit";
		$bom_id=$dbcon->real_escape_string($_REQUEST['id3']);
		$query="select bom.*,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name from tbl_bom as bom
		left join unit_mst as bunit on bunit.unitid=bom.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom.product_conv_unit
		where bom.bom_id=$bom_id";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
		$bom_no=$rel['bom_no'];
		$r_bom_id=$rel['bom_id'];
		$sel_bom_version_id = $rel['bom_version_id'];
		$date=date('d-m-Y',strtotime($rel['bom_date']));
	//$_SESSION['bom_edit_id']=$rel['bom_id'];

		$_SESSION['bom_product_name']=$rel['bom_product'];

		$mode="Assign";
		$rel['product_id'] = $prd_id;
		$rel['bom_product'] = $prd_id;

		$back_link = ROOT.PRODUCTION_ROOT.'store_order_design_department';

	}
	
	
	$sel_bom="select product_base_qty from tbl_bomtrn where bom_id='$r_bom_id' and product_id='$p_name'";
	$r_bom=brp_mysqli_fetch_assoc($dbcon->query($sel_bom));	
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=brp_mysqli_fetch_assoc($dbcon->query($set));
	

	?>

	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>BOM</title>
		<?php include_once('../../include/include_css_file.php');?>
		<style type="text/css">
		.sel_bom_version{
			
			color: #fff !important;
			background-color: #7ba9d0 !important;
			border-color: #2e6da4 !important;

		}

		
		<?phpif(!$bom_to_po_req){ ?>
			.po_req_mode{
				display:none;
			}
			<?}?>

			<?php
			if($bom_actual_add){
				echo ".hide_act_add{display:none;}";
			}
			else{
				echo ".show_act_add{display:none;}";
			}
			?>

		.currency_icon{
			color:green;
			font-size:12px;
		}
	
		label{
			font-size: 15px;
		}
		.row_margin
		{
			margin-top:10px;
		}
		.btn-group-vertical>.btn.active, .btn-group-vertical>.btn:active, .btn-group-vertical>.btn:focus, .btn-group-vertical>.btn:hover, .btn-group>.btn.active, .btn-group>.btn:active, .btn-group>.btn:focus, .btn-group>.btn:hover{
			z-index:2;
			background-color: #bbdce6;
		}
		.control-label{
			font-weight: bold;
		}


		.error {
				font-weight: bold;
				color: #ef1717;
				
				font-size: 16px;
			}
	 #process_left,#process_right{
    margin: 5px;
    border: 1px solid #cccccc;
    list-style: none;
    padding-left: 0;
    height: 200px;
    overflow: auto;
    /* width: 250px; */
    border-radius: 5px;
  }
.mb-5{
	margin-bottom: 5px;
}
  ul li{
    cursor: pointer;
    padding: 5px 10px;
  }


  .selected{
    background-color: blue;
    color: white;
    margin: 2px;
  }

  .bigBtn{
    height: 50px;
    width: 55px;
    margin-top: 35px;
    margin-left: -5px;
    font-size: 20px;
    font-weight: 900;
  }
	
	
		</style>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once('../../include/include_top_menu.php');?>
			<?php include_once('../../include/left_menu.php');?>
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
										// $id3=$dbcon->real_escape_string(base64_decode($_REQUEST['id3']));
										//get_breadcum($dbcon,$id3,$bomtrn_id,$id2);
										// $ids=explode(',', $id3);

										// // echo "<pre>";
										// // print_r($ids);
										// // exit;

										// $prev_param_2='';
										// $prev_param_1='';

										// foreach ($ids as $key=>$bid) {
										// 	$arr[]=$bid;
										// 	$arr_str=implode(',', $arr);
										// 	$str_encode=base64_encode($arr_str);
										// 	if($key>=1){
										// 		$prev_param_1=$ids[$key];
										// 		$prev_param_2=$ids[$key-1];
										// 	}else{
										// 		$prev_param_1=$ids[$key];
										// 		$prev_param_2=$ids[$key];
										// 	}

										// 	$map=generate_sitemap_modify($dbcon,$bid,'tbl_bom','bom_id','0',$prev_param_1,$prev_param_2,$str_encode,$key);


										// }
										// if(is_array($rel) && !empty($rel['product_name'])){
										// 	$map.=" / ".$rel['product_name'];
										// 	echo $map;
										// }else{
										// 	echo $map;
										// }

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
											<input type="hidden" name="inquiry_type" id="inquiry_type" value="1">

											<div class="col-md-12">
												<?php if(strpos($_SERVER['REQUEST_URI'], "bom_allocate")==true){ ?>
													<div class="col-md-12">
														<div class="form-group">
															<label class="col-md-4 control-label"> Product *</label>
															<div class="col-md-4 col-xs-11">
																<input type="text" class="form-control" name="prod_name" id="prod_name" value="<?php echo $rel['product_name']; ?>" readonly>
																<input type="hidden" name="sel_product_id" id="sel_product_id" value="<?php echo $rel['product_id']; ?>" onChange="load_bom_version_datatable();" class="bom_allocate">

																<strong id="bom_duplicate" style='color:red;display:none'>BOM For This Product Already Exist</strong>
															</div>
														</div>
													</div>

												<?php } else { ?>
													<div class="col-md-12">
														<div class="form-group">
															<label class="col-md-4 control-label">Select Product *</label>
															<div class="col-md-5">
																<!-- <select class="select2 mprdct" title="Select product" name="sel_product_id" id="sel_product_id" onchange="get_main_product(this.value);load_bom_version_datatable();load_product_data();hide_bom_version_form()"  >
																	//<=get_bom_product($dbcon,$rel['bom_product'],'0')?>
																	<=get_bom_product_typewise($dbcon,$rel['bom_product'],$type_conf,$pro_search);?>
																</select> -->
																<input id="sel_product_id" name="sel_product_id" <?=$pro_readonly?> style="width:100%;" placeholder="Select product" onChange="get_main_product(this.value);load_bom_version_datatable();load_product_data();hide_bom_version_form()" value="<?=$rel['bom_product']?>" class="bom_edit"/>

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
				<?php if($companyConfiguration['outside_jobwork']){ ?>
				<div class=" container row ">
								<div class="col-md-6 mtop20">
										<div class="form-group">
											<div class="col-md-3">
												<label>
													<input id="status_all" name="bom_type_opt"  checked="checked"  type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_bom_version_datatable();" class="" title="All" value="">
													<div class='external-event label label-primary ui-draggable' style='position: relative;width:70px;'>All</div>					
													
												</label>
											</div>
											<div class="col-md-3">
												<label>
													<input id="status_pend" name="bom_type_opt" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_bom_version_datatable();" class="" title="Pending" value="0">
													<div class='external-event label label-success ui-draggable' style='position: relative;width:70px;'>Normal</div>					
													
												</label>
											</div>
											<div class="col-md-6">
												<label>
													<input id="status_comp" name="bom_type_opt" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_bom_version_datatable();" class="" title="Pending" value="1">
													<div class='external-event label label-danger ui-draggable' style='position: relative;width:100px;'>Outside Jobwork</div>					
													
												</label>
											</div>
										</div>
									</div>
							</div>
						<?php } ?>
				<div id="bom_versiondata"></div>

				<input type="hidden" name="sel_bom_version_id" id="sel_bom_version_id" value="<?=$sel_bom_version_id?>" />
				<input type="hidden" name="bom_version_id" id="bom_version_id" value="" />
				<div class="col-md-12" id="row_bom_version" style="display: none;">
					<?php if($companyConfiguration['outside_jobwork']){ ?>
					<div class="row">
					<div class="col-md-4">
									<div class="form-group">
										<label class="col-md-4 control-label">BOM Type *</label>
									
										<div class="col-md-8 col-xs-11">
											<select class="select2" name="bom_type" id="bom_type">
												<option value="0">Normal</option>
												<option value="1">Outside Jobwork</option>
											</select>
										</div>
										</div>
									</div>
										</div>
									<?php } ?>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-4 control-label">Serial No *</label>
								<div class="col-md-8 col-xs-11">
									<input id="bom_srno" name="bom_srno" type="text" class="form-control" title="Serial No" value="<?=$bom_srno?>" placeholder="Serial No" readonly required />

								</div>
							</div>
						</div>



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
									<input id="bom_date" name="bom_date" type="text" class="form-control default-date-picker required valid" title="BOM Date" value="<?=$date?>" <?=($readonly=='yes') ? 'readonly':'';?> placeholder="BOM Date">
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-4 control-label">BOM  QTY</label>
								<div class="col-md-5 col-xs-11">
									<input id="bom_unit_qty" name="bom_unit_qty" onkeyup="bom_convert_qty(1)" type="text" class="form-control numbersOnly" title="BOM Unit QTY" value="1" placeholder="Enter BOM Unit QTY">
								</div>
								<div class="col-md-3 col-xs-11">
											<input type="text"  title="Base Unit"  id="bom_unit_name" name="bom_unit_name"  class="form-control" value="<?=$rel['base_unit_name']?>" readonly />

											<input type="hidden" name="bom_unit" id="bom_unit" value="" />
										</div>
							</div>

						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-4 control-label">BOM Conv QTY</label>
								<div class="col-md-5 col-xs-11">
									<input id="bom_conv_qty" name="bom_conv_qty" type="text" class="form-control numbersOnly" title="BOM Unit QTY" value="1" onkeyup="bom_convert_qty(2)" placeholder="Enter BOM Unit QTY">
								</div>
								<div class="col-md-3 col-xs-11">
											<input type="text"  title="Base Unit"  id="bom_conv_unit_name" name="bom_conv_unit_name"  readonly class="form-control" value="<?=$rel['conv_unit_name']?>"  />

											<input type="hidden" name="bom_conv_unit" id="bom_conv_unit" value="<?=$rel['product_conv_unit']?>" />
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
						<div class="col-md-2">
							<div class="form-group">
								<label class="col-md-6 control-label">Active BOM</label>
								<div class="col-md-3 col-xs-11">
									<input type="checkbox"  class="form-control" checked name="bom_active_status" id="bom_active_status" disabled value="1" />
								</div>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label class="col-md-6 control-label">Default BOM</label>
								<div class="col-md-3 col-xs-11">
									<input type="checkbox" name="is_default_bom"  class="form-control"  id="is_default_bom" value="1" />
								</div>
							</div>
						</div>

					</div>

				</div>
				<div class="col-md-12 mbot30">
					<button type="button" class="btn btn-primary" id="add_version" name="add_version" onclick="show_bom_version_form()">Add Version</button>
					<button type="button" class="btn btn-success" style="display:none" id="save_version" onclick="save_bom_version()" name="save_version">Save</button>
					<button type="button" class="btn btn-danger" onclick="hide_bom_version_form()" style="display:none" id="cancel_version" name="cancel_version">Cancel</button>

					<?php if(strpos($_SERVER['REQUEST_URI'], "bom_assign_store_order")==true){ ?>
					<button type="button" class="btn btn-primary"  onclick="bom_version_assign_store_order('<?php echo $prd_id ;?>','<?php echo $store_order_id;?>','<?php echo $so_bom_id;?>');" id="assign_version" name="assign_version">Assign BOM</button>
					<?php } else if(strpos($_SERVER['REQUEST_URI'], "bom_assign")==true){ ?>
					<button type="button" class="btn btn-primary"  onclick="bom_version_assign('<?php echo $prd_id ;?>','<?php echo $so_id;?>','<?php echo $so_trans_id;?>','<?php echo $so_bom_id;?>');" id="assign_version" name="assign_version">Assign BOM</button>
					<?php } ?>

					

					
					
					<!-- <button type="button" class="btn btn-primary" onclick="open_copy_bom_model()" id="copy_version" name="copy_version">Copy BOM</button> -->
					<?php /*if(strpos($_SERVER['REQUEST_URI'], "bom_allocate")==true):?>
								
									<button  type="button" class="btn btn-danger" onclick="history.back();">Back</button>
								
								<?php endif; */?>
							</div>
							<input type="hidden" name="bom_no" id="bom_no" value="<?=$bom_no?>" />
							<input type="hidden" name="bom_to_po_req" id="bom_to_po_req" value="<?php echo $bom_to_po_req; ?>" />
							<input type="hidden" name="bom_id" id="bom_id" value="<?=isset($rel['p_bom_id']) ? $rel['p_bom_id']:$rel['bom_id'] ?>" />

							<input type="hidden" name="main_bom_id" id="main_bom_id" value="<?=$rel['bom_id']?>" />
							<div class="col-md-12" style="display:none;">
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
											<input type="number"  title="Enter Qty" min="0" id="conv_qty" name="conv_qty"  class="form-control" value="<?= @$rel['product_conv_qty'] ? $rel['product_conv_qty'] : 1 ?>" onkeyup="convert_qty(2);" <?=($conversion_qty_read_only=='yes') ? 'readonly':'';?>  />
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
											<th width="20%" class="text-center hide_product_version">Version</th>
											<th width="5%" class="text-center hide_act_add">Unit</th>
											<th width="10%" class="text-center hide_act_add">Quantity</th>
											<th width="5%" class="text-center hide_act_add">UOM</th>
											<th width="15%" class="text-center hide_act_add">ACtual Qty.</th>
											<?phpif($getspecialConfiguration['jet_technologies_permission'] == 1){ ?>
													<th width="8%" class="text-center hide_act_add">Enable Multiplication</th>
											<?php}	?>	
											<th width="7%" class="text-center"></th>
										</tr>
										<tr id="field1">
											<td style="vertical-align:top;" width="20%">
												<select class="select2 prtype" name="product_type" id="product_type" onChange="check_product_process_required(this.value);" title="Select Product Type" style="width: 100%;">
													<?=get_product_type_company($dbcon);?>
												</select>
												<input type="hidden" id="is_process_required" value="">
											</td>
											
											<td style="vertical-align:top;" width="25%">
												<!-- <select class="select2 selproduct" title="Select product" name="product_id" id="product_id" onchange="load_product_types();load_product_version(this.value,'');load_product_detail(this.value);" >
													<=getproduct_typewise($dbcon,'',$type_conf,$pro_search);?>
												</select> -->
												<input id="product_id" name="product_id" style="width:100%;" placeholder="Select product" onChange="load_product_types();load_product_version(this.value,'');load_product_detail(this.value);"/>
												<br/><br/>
												<button accesskey="n" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" onclick="showproduct()"><i class="fa fa-plus"></i> Add Product</button>
												<div id="get_spec_div" style="display:none">
													<!--Width : <input type="text" class="form-control" name="product_width" id="product_width" value="<=$mode=='Edit'?$rel['product_width']:0?>" onkeyup="get_ms_kg()" />
													Height : <input type="text" class="form-control" name="product_height" id="product_height" value="<=$mode=='Edit'?$rel['product_height']:0?>" onkeyup="get_ms_kg()" />
													
													Thickness : <input type="text" class="form-control" name="product_thickness" id="product_thickness" value="<=$mode=='Edit'?$rel['product_thickness']:0?>" onkeyup="get_ms_kg()" />
													
													<input type="hidden" class="form-control" name="product_density" id="product_density" value="<=$mode=='Edit'?$rel['product_density']:0?>" onkeyup="get_ms_kg()" /> -->
													
													<!-- <input type="text" class="form-control" name="product_kg" id="product_kg" value="<=$mode=='Edit'?$rel['product_kg']:0?>" readonly />
														<input type="checkbox" name="set_kg" id="set_kg" value="0" onclick="set_kg_to_qty(this.value)" />SET -->
													</div>
												</td>	
												<td class="hide_product_version">
													<!-- <select class="select2 productversion" title="Select Version" name="pro_version_id" id="pro_version_id" onchange="get_product_version_qty(this.value)"> -->
														<select class="select2 productversion" title="Select Version" name="pro_version_id" onChange="get_p_bom_id(this.value)"  id="pro_version_id">
															<option value="">Choose Product Version</option>

														</select>
													</td>
													<td style="vertical-align:top;" class="hide_act_add">
											<!--<select class="form-control" id="product_base_unit" name="product_base_unit" >
													<option value="">--select Unit--</option>
													<=getunit($dbcon);?>
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
													<=getunit($dbcon);?>
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

											<?phpif($getspecialConfiguration['jet_technologies_permission'] == 1){ ?>
													<td>
															<!-- <input class="form-control" type="number" id="conversation_factor" name="conversation_factor" value="1" /> -->
															<select class="form-control select2" id="conversation_factor" name="conversation_factor">
																<option value="1">Yes</option>
																<option value="0">No</option>
															</select>
													</td>
											<?php}	?>		
											<td style="vertical-align:top;">
												<!-- Sanat :: comment below button :: 03-03-2021 -->
												<!-- <input type="button"  name="addrow" id="addrow" onClick="return add_field();" class="btn btn-primary" value="Add"/> -->
												<input type="button" id="addprocess" class="btn btn-primary" data-original-title="Add Process" data-toggle="tooltip" data-placement="top" onclick="check_process();" value="Add"/>
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
									<a href="<?=$back_link?>" type="button" class="btn btn-danger">Cancel</a>
									<div class="col-md-3"></div>
								<?php } ?>
							</div>		
						</div>
						<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />

						<!--<input type="hidden" name="eid" id="eid" value="<=isset($_GET['eid'])&& $_GET['eid']!=''?$bom_id:$r_bom_id ?>" />-->

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
							<?phpif(strpos($_SERVER['REQUEST_URI'], "bom_assign")==true || strpos($_SERVER['REQUEST_URI'], "bom_assign_store_order")==true){  ?>
									<input type='hidden' name='bom_assign' id='bom_assign' value='yes' />
									<?phpif(strpos($_SERVER['REQUEST_URI'], "bom_assign_store_order")==true){  ?>
										<input type='hidden' name='bom_assign_from' id='bom_assign_from' value='store_order' />		<?	} ?>										
							<?	}else{ ?>
									<input type='hidden' name='bom_assign' id='bom_assign' value='no'/>	
							<?php} ?>
						</form>
					</div>	
				</section>
			</div>
		</div>
	</section>
</section>
<?php include_once($include1.'bom_copy_model.php');?>
<?php include_once($include1.'bom_document_upload_model.php');?>
<?php include_once($include1.'allocate_bom_show_model.php');?>
<?php include_once($include1.'default_bom_set_model.php');?>
<?php include_once($include.'footer.php');?>
<?php include_once('../../administration/include/add_product.php'); ?>
</section>
<?php include_once($include.'include_js_file.php');?>   
<?php include_once($include.'allocate_process_model.php');?>  
<?php include_once($include1.'bom_process_add_model.php');?>   
<?php include_once($include1.'qc_model.php');?>   
<?php include_once($include1.'bom_in_used_modal.php');?>   

<!-- Sanat :: Added copy bom model -->

<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/bom.js?<?php echo time(); ?>"></script>
<script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/product_mst.js?<?php echo time(); ?>"></script>
<script src="<?=ROOT?>js/advanced-form-components.js"></script>
<script>
	$(".prtype,.mprdct").select2({
		width: '100%',
	});
	$(".selectoption").select2({
		width: '100%',
	});	
	$("#bom_type").select2({
		width: '100%',
	});	
	$("#pro_version_id,#conversation_factor").select2({
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
	<?
	if($mode!='Add'){
		?>	
		$('#sales_order_id').select2('readonly',true);
		$('#sales_order_pro_id').select2('readonly',true);
		<?}
		?>
		<?phpif($direct_add){?>
			load_sales_pro_data(<?=$rel['sales_order_id']?>);
			$('#sales_order_id').select2('readonly',true);
			<?
			$deleteid=delete_record('tbl_bomtrn', "bom_id=0", $dbcon);
		}
		?>

		<?php/*if($bom_clone){?>
			clone_bom_trn_data(<?=$bom_id?>);
			<?}*/?>	
		</script>
		<?php
		if($mode=="Add"){
			echo "<script>get_series_no()</script>";
		} 
		
		if($readonly=='yes'){
			echo "<script>$('#sel_product_id').select2('readonly', true);</script>";
			// echo "<script></script>"
		}
		if(strpos($_SERVER['REQUEST_URI'], "bom_assign")==true){
			echo "<script>
			$('#sel_product_id').val($prd_id).trigger('change');
			$('#sel_product_id').select2('enable',false);
			</script>";
		}
		?>
	</body>
	<script type="text/javascript">
		// if(sessionStorage.getItem("selected_version_id")){
		// 	$("#selected_version_id").val(sessionStorage.getItem("selected_version_id"));
		// 	load_version_bom_data(sessionStorage.getItem("selected_version_id"));

		// }
		var alloted="<?php echo $alloted; ?>";
		var id2="<?php echo $id2; ?>";
		var id3="<?php echo $id3; ?>";
		var id4="<?php echo $id4; ?>";
		var multiple_qty="<?php echo $multiple; ?>";
	</script>
	</html>
