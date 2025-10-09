<?php 
session_start();
include('../include/urlfile.php');	
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$_SESSION['contents']='';
$form="BOM";
$mode="Print";
$bom_id = $dbcon->real_escape_string($_REQUEST['id']);
$query="select bom.*,product.product_name from tbl_bom as bom 
left join product_mst as product on product.product_id=bom.bom_product
where bom_id=$bom_id";
$rel=mysqli_fetch_assoc($dbcon->query($query));	

$query="select bom.*,product.product_name,product.product_icode,product.image_name,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name, product.product_type, dwg.drawing_number, bomv.version_name, product.product_desc, product.product_alias_name,bomv.bom_no from tbl_bom as bom
left join product_mst as product on product.product_id=bom.bom_product
left join unit_mst as bunit on bunit.unitid=bom.product_base_unit
left join unit_mst as cunit on cunit.unitid=bom.product_conv_unit
left join tbl_drawing as dwg on dwg.drawing_id=product.drawing_id
left join pro_ms_bom_version as bomv on bomv.bom_version_id=bom.bom_version_id
where bom.bom_status !=2 and bom.bom_id=$bom_id";

$rel=mysqli_fetch_assoc($dbcon->query($query));

$main_bom_id = $bom_id;
	//exit;
$set="select * from tbl_company where company_id=".$rel['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));

$sel1=$dbcon->query("select sum(product_qty)as sqty from tbl_bomtrn where bom_id='$bom_id'");
$row1=mysqli_fetch_assoc($sel1);

$totalqty=$row1['sqty'];
	//echo $row1['sqty'];

if($rel['image_name']!=null){
	$image_name = '<img src="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel['image_name'].'" style="width: 60px;height: 50px;">';
		//$image_name = '<a href="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel["image_name"].'" target="_blank"><img src="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel['image_name'].'" style="width: 60px;height: 50px;"></a>';
}else{
	$image_name = '';
}
$companyConfiguration=getCompanyConfiguration($dbcon);
$bom_pro_print=explode(",", $companyConfiguration['bom_pro_print']);

$alias_name1 = '';
if(in_array('alias',$bom_pro_print)){
	$alias_name1 = " -- (".$rel['product_alias_name'].")";
}
$drawing_number1 = '';
if(in_array('drawing',$bom_pro_print)){
	$drawing_number1 = " -- (".$rel['drawing_number'].")";
}
$item_code1 = '';
if(in_array('item',$bom_pro_print)){
	$item_code1 = " -- (".$rel['product_icode'].")";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>BOM Print</title>
	<?php include_once($include.'include_css_file.php');?>
	<style>
		body {
			color: #000000;
		}
		.con ul 
		{
			padding-left:0px;
		}
		.con ul li 
		{
			margin-left:22px;
			list-style: disc !important;
		}

		.td1
		{
			text-align:center;
			vertical-align:top;
			border-right:1px solid;
			border-left:1px solid;
			border-bottom:1px solid;
		}
		.td2
		{
			text-align:center;
			border-bottom-color:#FFFFFF; 
			border-right:1px solid;
			vertical-align:top;
			border-bottom:1px solid;
		}
		.td3
		{
			text-align:center;
			vertical-align:top;
			border-bottom-color:#FFFFFF; 
			border-right:1px solid;
			border-bottom:1px solid;
		}
	</style>
</head>
<body>
	<section id="container" >
		<?php include_once($include.'include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($include.'left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3><?=$mode.' '.$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.PRODUCTION_ROOT.'bom_list'?>"><?=$form?> List</a></li>
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
								<?=$form?> <?=$mode?>
							</header>	
							<div class="panel-body">
								<center>
									<div class="col-md-1"> </div>With Logo
									<br/>
									<label class="col-md-2 control-label"> Print</label>
									<div class="col-md-4 col-xs-11">
										<form class="form-horizontal" role="form" id="print_form" action="javascript:;" method="post" name="print_form">
											<select class="form-control" name="print_status" id="print_status" <?php if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
												<option value="">Select Print</option>
												<option value="1">ORIGINAL</option>
												<option value="2">DUPLICATE</option>
												<option value="3">TRIPLICATE</option>
												<option value="4">EXTRA</option>
											</select>
										</form>
									</div>
									<div class="col-md-1">
										<input type="checkbox" class="form-control"  name="logo" id="logo" value="1">
									</div>
									<div class="col-md-4">
										<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
										<a href="<?=ROOT.PRODUCTION_ROOT.'bom_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
										<!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to Pdf" onClick="make_pdf()" />-->
									</div>
								</center>	
								<div class="col-md-12"></div>
								<label class="col-md-3 control-label"></label>
								<div class="col-lg-4"></div>
								<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
								<?php ob_start(); ?>
								<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="col-md-12" style=" margin-top:10px;" id="print1">
<!--<table width="100%" class="maintable" border="1" id="table_head" style="border-radius:6px;border-collapse: separate; border-width: 2px;border-color: black;" >
	<thead>
		<tr>
			<th style="border: none;padding:5px !important;" width="50%"> 
				<img src="<?=ROOT.LOGO.'fixed_logo.png'?>" style="width:100%;padding: 2px;"/>
				<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>" style="width:100%"/>--
			</th>
			<th style="text-align:left;border: none;"> 
				<?=$set_head['address']?> 
				<?phpif($set_head['contact_no']){?><br/>Contact No. <?=$set_head['contact_no']?><?php }?>
				<?phpif($set_head['website']){?><br/>E-Mail: <?=$set_head['website']?><?php }?>
			</th>
		</tr>
	</thead>
</table>-->
<table width="100%" class="maintable" border="0" style="" id="table_head">
	<tr style="border:none;">
		<td width="100%" style="border:none;"> 
			<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>-->

			<h2 align="center"><?=$set_head['company_name']?></h2>
			<h5 align="center" style="padding:top:8px;"><?=$set_head['logo_content']?></h5>
			<h5 align="center"><?=$set_head['address']?></h5>
			<h5 align="center"><?php if($set_head['website']){?>Email: <?=$set_head['website']?><?php }?> 
			<?php if($set_head['contact_no']){?>(M) <?=$set_head['contact_no']?><?php }?></h5>

		</td>
	</tr>
</table>
<table width="100%" border="0" style="" id="">
	<tr>
		<td width="90%" style="text-align:center"> 
			<strong style="font-size:16px">
				<?=$form?> 
			</strong>
		</td>
		<td width="10%" style="text-align:center"> 
			<strong style="font-size:12px">
				<b class="data_title">ORIGINAL</b>
			</strong>
		</td>
	</tr>
</table>
<!-- Multi Page Challan Start -->				
<table width="100%" class="maintable" style="font-size: 12px;" id="invoice_type" >
	<thead>
		<tr>
			<th colspan="8" style="padding:0px !important;">
				<table  border="0" style="font-size:12px;border-collapse:collapse;" cellpadding="0"  cellspacing="0" width="100%" id="">
					<!--<thead>-->
						<tr>
							<td width="10%" style="vertical-align:top;border:1px solid;border-right:none;"><strong>BOM No </strong>
							</td>
							<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:1px solid;">: <strong><?=$rel['bom_no']?></strong>
							</td>
							<td width="10%" style="vertical-align:top;border:1px solid;border-right:none;"><strong>BOM Version Name</strong>
							</td>
							<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:1px solid;">: <strong><?=$rel['version_name']?></strong>
							</td>
							<td width="10%" style="vertical-align:top;border:1px solid;border-right:none;"><strong>BOM Date </strong>
							</td>
							<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:1px solid;">: <strong><?=date('d/m/Y',strtotime($rel['bom_date']))?></strong>
							</td>
						</tr>
						<tr>
							<td style="vertical-align:top;border:1px solid;border-right:none;white-space:nowrap;">
								<strong>Product</strong>
							</td>
							<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
								: <?=$rel['product_name'] . ' - ('. $rel['product_icode'] . ') ' ?><?=$alias_name?>
							</td>
							<td style="vertical-align:top;border:1px solid;border-right:none;white-space:nowrap;">
								<strong>Product Qty</strong>
							</td>
							<td colspan="4" style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
								: <?=$rel['product_base_qty']?>							
							</td>
						</tr>
						<!--</thead>-->	
					</table>
				</th>
			</tr>
			<tr height="30px">					
				<th  width="5%" style="text-align:center;border:1px solid;border-top:none;">
					<strong>SR. NO.</strong>
				</th>
				<?php if($companyConfiguration['enable_item_image']==1){ ?>
					<th width="15%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Item Image</strong></th>
				<?php} ?>
				<th width="25%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Item Description</strong></th>
				<th width="15%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Item Type</strong></th>
				<th width="5%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Qty</strong></th>
				<!--<th width="5%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Unit</strong></th>-->
				<!--<th width="15%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Last Purchase Price</strong></th>-->
				<th width="20%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>Process</strong></th>
				<th width="20%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>ADD Extra No</strong></th>
			</tr>
		</thead>
		<tbody style="border: 1px solid;">

			<tr>
				<td style="border:1px #444 solid;" >0</td>
				<?php if($companyConfiguration['enable_item_image']==1){ ?>
					<td style="border:1px #444 solid;" ><?=$image_name?></td>
				<?php} ?>
				<td style="border:1px #444 solid;" ><?=$rel['product_name'].''.$item_code;?><?=$drawing_number?><?=$alias_name?><?php if($companyConfiguration['enable_item_description']==1){ ?><br><?=$rel['product_desc']?><?php} ?></td>
				<td style="border:1px #444 solid;" ><?=get_product_type_by_id($dbcon,$rel['product_type'])?></td>
				<td style="border:1px #444 solid;" >
					<?php 
					echo $rel['product_base_qty'];  ?> <?=$rel['base_unit_name']?>
				<!-- <?phpif($rel['product_base_unit']!=$rel['product_conv_unit']){ ?>
				<?php
				 
			
				echo  $rel['product_base_qty']?>  <?=$rel['base_unit_name']?><br/>
				<?=$rel['product_conv_qty']?>  <?=$rel['conv_unit_name']?>
				<?php }else{?>
				<?php
				$SESSION['tot_bom']=$SESSION['tot_bom']+$rel['product_base_qty']; 
				//$total=$total+$rel1['product_base_qty'].'kl';
				$rel['product_base_qty']?>  <?=$rel['base_unit_name']?>
				<?php }?> -->
			</td>
			<!--<td style="border:1px #444 solid;" >
				<?php //=$rel['base_unit_name'] ?>
			</td>-->
			<!--<td style="border:1px #444 solid;"><?=get_last_purchase($dbcon,$rel['bom_product']) ?></td>-->

			<td style="border:1px #444 solid;" >
				<?php $query3="select pbom.*, mst.resource_id,mst.process_id ,p.process_name,reso.resource_name,mst.process_type from pro_bom_process as pbom 
				left join tbl_product_process as mst on mst.pr_process_id=pbom.pr_process_id
				left join tbl_resource as reso on reso.resource_id=mst.resource_id
				left join process_mst as p on p.process_id=mst.process_id where mst.status=0 and  pbom.product_id=".$rel['bom_product']." and pbom.bom_version_id = ". $rel['bom_version_id'] ." and pbom.process_status = 0 order by pbom.priority";
				// echo $query3;
				$process_type="";

				$result3=$dbcon->query($query3);
				$cnt3=mysqli_num_rows($result3);
				if($cnt3>0){ ?>
					<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
						<tr>
							<th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Resource Name</th>
						</tr>
						<?phpwhile($rel3=mysqli_fetch_assoc($result3)){ 
							if($rel3['process_type']==1){
								$process_type="Inhouse";
							}else{
								$process_type="Outside";
							}
							?>

							<tr>
								<td style="border:0.5px #444 solid;text-align:center;" ><?=$rel3['priority']?></td>

								<td style="border:0.5px #444 solid;text-align:center;" ><?=$process_type?></td>
								<td style="border:0.5px #444 solid;text-align:center;" ><?=$rel3['process_name']?></td>
								<td style="border:0.5px #444 solid;text-align:center;" ><?=$rel3['resource_name']?></td>
							</tr>
						<?php} ?>
					</table>
				<?php} ?>

			</td>
			<?php
					$chk_data = check_extra_bom_no($dbcon,$rel['bom_product'],$main_bom_id,$rel['bom_id'],0,$rel['bom_version_id']);

					$edit_id = $chk_data['ext_id'];
					$ext_no =  $chk_data['ext_no'];
				?>
			<td style="border:1px #444 solid;" >
				<div class="div_extra_bom_no" style="display:flex;margin:10px;">
					<input width="125" type="text" class="form-control extra_bom_no" data-main_bom_id="<?=$main_bom_id?>" data-bom_id="<?=$rel['bom_id']?>" data-bom_version="<?=$rel['bom_version_id']?>" data-parent_bom_id="0" data-edit_id="<?=$edit_id?>" data-product_id="<?=$rel['bom_product']?>" value="<?=$ext_no?>">
					<button style="margin-left:15px" class="btn btn-primary btn_save_extra_bom_no">
						<?php echo ($edit_id > 0) ? "UPDATE" :	"ADD" ?>
					</button>
				</div>
			</td>
		</tr>
		
		<?php
			// $qry="select *,per.unit_name FROM `tbl_bomtrn` as trn 
			// left join product_mst as product on product.product_id=trn.product_id 
			// left join unit_mst as per on per.unitid=trn.product_uom
			// where bom_trn_status!=1 and bom_id='$rel[bom_id]' and parent_id='0' order by bom_trn_id Desc";

		$qry="select bom_trn.*, pro.product_name, pro.product_icode, pro.image_name, pro.product_type,bunit.unit_name as base_unit_name, cunit.unit_name as conv_unit_name, dwg.drawing_number, pro.product_desc, pro.product_alias_name from tbl_bomtrn as bom_trn 
		left join product_mst as pro on pro.product_id=bom_trn.product_id
		left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
		left join tbl_drawing as dwg on dwg.drawing_id=pro.drawing_id
		where bom_trn_status=0 and bom_id=".$rel[bom_id];	
	//$result1=$dbcon->query($query1);
			// echo $qry;
		$result1=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;
		$_SESSION['bom_tot']=0;
		$cnt1=mysqli_num_rows($result);
		$cnt=1;
		while($rel1=mysqli_fetch_assoc($result1))
		{
			if($rel1['image_name']!=null){
					//$image_name1 = '<a href="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel1["image_name"].'" target="_blank"><img src="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel1['image_name'].'" style="width: 60px;height: 50px;"></a>';
				$image_name1 = '<img src="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel1['image_name'].'" style="width: 60px;height: 50px;">';
			}else{
				$image_name1 = '';
			}
			$alias_name1 = '';
			if(in_array('alias',$bom_pro_print)){
				$alias_name1 = " -- (".$rel1['product_alias_name'].")";
			}
			$drawing_number1 = '';
			if(in_array('drawing',$bom_pro_print)){
				$drawing_number1 = " -- (".$rel1['drawing_number'].")";
			}
			$item_code1 = '';
            if(in_array('item',$bom_pro_print)){
            	$item_code1 = " -- (".$rel1['product_icode'].")";
            }
			?>
			<tr>
				<td style="border:1px #444 solid;" ><?=$i?></td>
				<?php if($companyConfiguration['enable_item_image']==1){ ?>
					<td style="border:1px #444 solid;" ><?=$image_name1?></td>
				<?php} ?>
				<td style="border:1px #444 solid;" ><?=$rel1['product_name'].''.$item_code1;?><?=$drawing_number1?><?=$alias_name1?><br>
					<?php $chkMaterial = $dbcon->query("SELECT bmt.*, mp.material_parameter_name FROM tbl_bom_material_trn as bmt LEFT JOIN tbl_material_parameter as mp ON mp.material_parameter_id = bmt.material_parameter_id WHERE bmt.bom_material_trn_status = 0 AND bmt.bom_trn_id='".$rel1['bom_trn_id']."'");
					while($getMaterial=brp_mysqli_fetch_assoc($chkMaterial)){
						echo $getMaterial['material_parameter_name'].' - '.$getMaterial['material_parameter_value'].'<br>';
					}
					if(brp_mysqli_num_rows($chkMaterial) > 0){
						echo "Calculation: ".$rel1['product_kg'];	
					}

				?></td>

				<td style="border:1px #444 solid;" ><?=get_product_type_by_id($dbcon,$rel1['product_type'])?></td>
				<td style="border:1px #444 solid;" >
					<?phpif($rel1['product_base_unit']!=$rel1['product_conv_unit']){ ?>
						<?php 
						echo  $rel1['product_base_qty'];  echo $rel1['base_unit_name']; ?>
					</br>
					<?php 	echo $rel1['product_conv_qty'];  echo $rel1['conv_unit_name'];
				}else{
					echo $rel1['product_base_qty']; ?>  <?=$rel1['base_unit_name']?>
					<?php }?> 
				</td>
			<!--<td style="border:1px #444 solid;" >
				<?php //=$rel1['base_unit_name'] ?>
			</td>-->
			<!--<td style="border:1px #444 solid;"><?php //=get_last_purchase($dbcon,$rel1['product_id']) ?></td>-->

			<td style="border:1px #444 solid;" >
				<?php 
				// Sanat :: comment below query and add new query -  10-08-2021

				/*$query="select mst.*,p.process_name,reso.resource_name from tbl_product_process as mst 
					left join tbl_resource as reso on reso.resource_id=mst.resource_id
					left join process_mst as p on p.process_id=mst.process_id where mst.product_id=".$rel1['product_id']." order by process_priority";*/

					$query = "select prb.priority, mst.*,p.process_name,reso.resource_name,mst.process_type from pro_bom_process as prb left join tbl_product_process as mst ON mst.pr_process_id= prb.pr_process_id left join tbl_resource as reso on reso.resource_id=mst.resource_id left join process_mst as p on p.process_id=mst.process_id where mst.status=0 and  prb.product_id=".$rel1['product_id']." and prb.bom_version_id = ".$rel1['bom_version_id']." and prb.process_status = 0 order by prb.priority"; 

					$result=$dbcon->query($query);
					$cnt=mysqli_num_rows($result);
				// echo $query;
					if($cnt>0){ ?>
						<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
							<tr>
								<th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
								<th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
								<th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
								<th style="border:0.5px #444 solid;text-align:center;" >Resource Name</th>
							</tr>
							<?phpwhile($rel=mysqli_fetch_assoc($result)){ 
								if($rel['process_type']==1){
									$process_type="Inhouse";
								}else{
									$process_type="Outside";
								}
								?>

								<tr>
									<td style="border:0.5px #444 solid;text-align:center;" ><?=$rel['priority']?></td>

									<td style="border:0.5px #444 solid;text-align:center;" ><?=$process_type?></td>
									<td style="border:0.5px #444 solid;text-align:center;" ><?=$rel['process_name']?></td>
									<td style="border:0.5px #444 solid;text-align:center;" ><?=$rel['resource_name']?></td>
								</tr>
							<?php} ?>
						</table>
					<?php} ?>

				</td>
				<?php
					$chk_data = check_extra_bom_no($dbcon,$rel1['product_id'],$main_bom_id,$rel1['p_bom_id'],$rel1['bom_id'],$rel1['bom_version_id']);

					$edit_id1 = $chk_data['ext_id'];
					$ext_no =  $chk_data['ext_no'];
				?>
				<td style="border:1px #444 solid;" >
					<div class="div_extra_bom_no" style="display:flex;margin:10px;">
						<input width="125" type="text" class="form-control extra_bom_no" data-main_bom_id="<?=$main_bom_id?>" data-bom_id="<?=$rel1['p_bom_id']?>" data-bom_version="<?=$rel1['bom_version_id']?>" data-edit_id="<?=$edit_id1?>" data-parent_bom_id="<?=$rel1['bom_id']?>" value="<?=$ext_no?>" data-product_id="<?=$rel1['product_id']?>" >
						<button style="margin-left:15px" class="btn btn-primary btn_save_extra_bom_no">
						<?php echo ($edit_id > 0) ? "UPDATE" :	"ADD" ?>
					</button>
					</div>
				</td>
			</tr>

			<?=bom_show_extra_no_print($dbcon,$rel1['p_bom_id'],$rel1['product_base_qty'],$i,$call,$space,$main_bom_id);?>

			<?php  $i++;  }	?>

		<!--<tr height="24px">
			<td colspan="4" style="border-top:1px solid;border-bottom:1px solid;border-right:1px solid;border-left:1px solid;font-size:14px;text-align:right;">TOTAL</td>
			<td style="text-align:center;border-bottom:1px solid; border-top:1px solid;font-size:14px;border-right:1px solid; "><?=number_format($_SESSION['bom_tot'],5,".","")?></td>
			<td colspan="3" style="text-align:center;border-bottom:1px solid; border-top:1px solid;font-size:14px;border-right:1px solid; "></td>
		
		
		</tr>-->	
		
		
	</tbody>	
</table>
				<!--<td colspan="4" style="padding: 0px !important;border:1px solid">
			<table class="footer-table" width="100%" border="0" style="margin-top: 5px;" id="table_foot">
					<tr>
						<td style="border:none;padding:0px 0px !important;width:100%;"> 
							<img src="<?=ROOT.LOGO.$set_head['f_logo']?>"  style="width:100%"/>
						</td>
					</tr>
				</table>-->
				
				<!--<table class="footer-table" width="100%">
					<tr style="border-bottom:none;">
						<td colspan="2" style="border-top:1px solid;">
						<?php if(!empty($set_head['vatno'])){ ?>
							<strong>COMPANY GST No. : <?=$set_head['vatno']?> 
						<?php} ?>
						</td>
						<td style="border-top:1px solid;">
							<span style="font-size:12px;float:right;">For, <strong><?=$set_head['company_name']?></strong></span>
						</td>
					</tr>
					
					<tr height="50px" style="border-bottom:none;">
					<td colspan="2"  style="">
							<?phpif(!empty($set_head['challan_condition'])){ ?>
								<strong>Terms and Conditions:</strong><br> <?=$set_head['challan_condition']?>
							<?php} ?><br/>
					</td>
					<td style="vertical-align:top;text-align:left;border-right:1px solid;">
					
					</td>
					</tr>
					<tr height="20px">
						<td colspan="2" style="vertical-align:bottom;border-bottom:1px solid;"> 
								<br/>Receiver's Signature	
						</td>
						 
						<td style="text-align:right;vertical-align:bottom;border:1px solid;border-left:none;border-top:none;border-left:none;">
							Authorised Signature
						</td>
					</tr>-->

					<!-- Multi Page Challan End -->				


				</div>
				<div id="print2"></div>
				<div id="print3"></div>

			</div>
			<?php  
			$contents = ob_get_contents();
			$_SESSION['contents']=$contents;
			$_SESSION['file_name']='Challan-#';
			$_SESSION['page_size']='A5';
			echo "<script> function make_pdf()
			{ window.open('".ROOT."export/print','_blank');
		}</script>";  
		?>
	</div>	
</section>
</div>
</div>
<!--state overview end-->
</section>
</section>
<!--main content end-->
<!--footer start-->
<?php include_once($include.'footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/bom_extra_no_add.js"></script>
<!--<script src="js/count.js"></script>-->
<script>
	$(".select2").select2({
		width: '100%'
	});
	$('.default-date-picker').datepicker({
		format: 'dd-mm-yyyy',
		autoclose: true
	});
	function paymentmode(id)
	{
		if(id=="2")
		{	
			$('#cheque_dtl').val('');
			$('#cheque_data').show();
		}
		else
			$('#cheque_data').hide();
	}

</script>
<script type="text/javascript"> 
	function print_receipt()
	{
		var originalContents = document.body.innerHTML;
	//var duplicate = $("#invoiceprint").clone().prepend("<hr style='border-color:#000; border-style:dashed; margin:10px 0' />").appendTo("#invoiceprint");
	var printContents = document.getElementById('receipt_print').innerHTML;     
	document.body.innerHTML = printContents;
	window.print();
	document.body.innerHTML = originalContents;
}

function PrintMe(DivID) {

	if($('#print_status').val()=='')
	{
		alert('Select PrintType');
	}
	else
	{


		if($('#print_status').val()<=3)
		{	
			for(var i=1;i<$('#print_status').val();i++)
			{	
				if($("#invoice").val()==2)
				{
					$("#print"+i+" .data_title").html('Performance');
					$("#type").html("Performance Invoice");
				}
				if($("#invoice").val()==1)
				{
					$("#print"+i+" .data_title").html('ORIGINAL');
					$("#type").html($("#typename").val());
				}
				if(i<$('#print_status').val())
				{
					$("#print"+i).after('<div class="page"></div>');
				}
				$("#print"+(i+1)).html($("#print1").clone());
				if((i+1)==2)
				{
					$("#print"+(i+1)+" .data_title").html('DUPLICATE');
				}
				if((i+1)==3)
				{
					$("#print"+(i+1)+" .data_title").html('TRIPLICATE');
				}

			}
		}
		else
		{
			$("#print1 .data_title").html('EXTRA');
		}
  //var duplicate = $("#receipt_data").clone().appendTo("#receipt_duplicate");
  var disp_setting="toolbar=yes,location=no,";
  disp_setting+="directories=yes,menubar=yes,";
  disp_setting+="scrollbars=yes,width=800, height=600, left=100, top=25";
  var content_vlue = document.getElementById(DivID).innerHTML;
  var docprint=window.open("","",disp_setting);
  docprint.document.open();
  docprint.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"');
  docprint.document.write('"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
  docprint.document.write('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">');
  docprint.document.write('<head><title><?phpecho TITLE;?></title>');
  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/style.css" media="all"/>');
  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/bootstrap.min.css" media="all"/>');
  docprint.document.write('<style type="text/css">');
  if ($('input[name=logo]:Checked').val() == "1") {

  	$('#table_head').show();
  	$('#table_foot').show();
  	docprint.document.write(' @media print{ @page { size:A4; margin: 0.2in <?=$set_head['letter_head_right_margin']?>in 0.2in <?=$set_head['letter_head_left_margin']?>in; } }   ');

  }
  else{
  	docprint.document.write(' @media print{ @page { size:A4; margin: <?=$set_head['letter_head_top_margin']?>in <?=$set_head['letter_head_right_margin']?>in <?=$set_head['letter_head_bottom_margin']?>in <?=$set_head['letter_head_left_margin']?>in; } }  #table_head, #table_foot { display:none }');
		//$('#invoice_type').css('margin-top','1.7in');	
		
	}

	docprint.document.write('body { font-family:Tahoma;color:#000;');
	docprint.document.write('font-family:Tahoma,Verdana; font-size:10px;} .dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
	docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } tr { page-break-inside: avoid } .maintable tbody tr { border-bottom:0.5px #ccc solid; }');
	docprint.document.write(' .maintable table { page-break-inside:auto } .maintable tr{ page-break-inside:avoid; page-break-after:auto } .maintable thead { display:table-header-group }  .maintable tfoot tr{ /*display:table-footer-group;*/ page-break-inside:avoid; page-break-before:always; } footer-table{ page-break-inside:avoid; page-break-before:always;  } #table_foot{position:fixed;bottom:0}</style>');
	docprint.document.write('</head><body onLoad="self.print()">');
	docprint.document.write(content_vlue);
	docprint.document.write('</body></html>');
	docprint.document.close();
	docprint.focus();
	$('#table_head').show();
	//$('#invoice_type').css('margin-top','0px');

}
location.reload();
}


</script>


</body>
</html>
