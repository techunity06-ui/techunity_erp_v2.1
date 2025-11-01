<?php 
   session_start();
	include('../include/urlfile.php');
   $token = md5(rand(1000,9999));
   $_SESSION['token'] = $token;
   $form="Item";
  
   // check permission for item list
   $bulkAccessArray = canCheckPermissionAccess($dbcon, [
          ADMINISTRATOR_PRODUCT_CLONE
   ]);
   $branch_id = $_SESSION['branch_id'];

   $readonly='';
   $disabled = '';
   $com="select * from tbl_company where company_id=".$_SESSION['company_id'];
   $comty=mysqli_fetch_assoc($dbcon->query($com));	
   if(strpos($_SERVER['REQUEST_URI'], "product_clone")==false) {
   
      if(!in_array(ADMINISTRATOR_PRODUCT_CLONE,$bulkAccessArray)){
         header("Location: ".DOMAIN."permission_access");
      }
   }
   else
   {
  
   	$mode="Clone";
      if(!in_array(ADMINISTRATOR_PRODUCT_CLONE,$bulkAccessArray)){
         header("Location: ".DOMAIN."permission_access");
      }
   	$pro_id=$dbcon->real_escape_string($_REQUEST['id']);
   	$query="select * from product_mst where product_id=$pro_id";
   	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	
   
   	$check_array=explode(",",$rel['product_check']);
   	$check_array_setting=explode(",",$rel['product_setting_check']);
    
   }
   
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <?php include_once($include.'include_css_file.php');?>
      <style>
         .margin_row{
         margin-top:10px !important;
         }
         .margin_span{
         margin-left:10px !important;
         font-size:16px;
         vertical-align:middle;
         }
         .container input {
         position: absolute;
         opacity: 0;
         cursor: pointer;
         height: 0;
         width: 0;
         }
         .checkmark {
         position: absolute;
         top: 0;
         left: 0;
         height: 25px;
         width: 25px;
         background-color: #eee;
         }
         /* On mouse-over, add a grey background color */
         .container:hover input ~ .checkmark {
         background-color: #ccc;
         }
         /* When the checkbox is checked, add a blue background */
         .container input:checked ~ .checkmark {
         background-color: #2196F3;
         }
         /* Create the checkmark/indicator (hidden when not checked) */
         .checkmark:after {
         content: "";
         position: absolute;
         display: none;
         }
         /* Show the checkmark when checked */
         .container input:checked ~ .checkmark:after {
         display: block;
         }
         /* Style the checkmark/indicator */
         .container .checkmark:after {
         left: 9px;
         top: 5px;
         width: 5px;
         height: 10px;
         border: solid white;
         border-width: 0 3px 3px 0;
         -webkit-transform: rotate(45deg);
         -ms-transform: rotate(45deg);
         transform: rotate(45deg);
         }
         .img-wrap {
         position: relative;
         }
         .img-wrap .close {
         position: absolute;
         top: 2px;
         right: 2px;
         z-index: 100;
         }
         .head_margin
         {
         margin-bottom:10px;
         }
      </style>
      <script type="text/javascript" src="<?php echo ROOT; ?>js/jquery.form.min.js"></script>
   </head>
   <body>
      <section id="container" class="sidebar-closed">
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
                           <h3>
                              New <?=$form?>
                              <!--<a href="<?=ROOT.'import_product'?>" >
                                 <button class="btn btn-primary btn-flat pull-right">Import <?=$form?></button></a>-->
                           </h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
                              <li class="active"><a href="<?=ROOT.'product_list'?>"><?=$form?> List </a></li>
                           </ul>
                        </div>
                     </section>
                     <!--breadcrumbs end -->
                  </div>
               </div>
               <!--Customer overview start-->
               <div class="row">
                  <div class="col-sm-12">
                     <section class="panel">
                        <header class="panel-heading">
                           New <?=$form?> 
                           <span class="tools pull-right">
                           <a href="javascript:;" class="fa fa-chevron-down"></a>
                           </span>
                        </header>
                        <div class="panel-body">
                           <form role="form" id="product_clone" action="javascript:;" method="post" name="product_clone">
                              <div class="col-md-12" style="padding-top: 25px;">
                                 <div class="col-md-12 margin_row">
                                 
                                   
                                    <div class="col-md-4 typeled">
                                       <!-- add pathik -->
                                       <div class="form-group">
                                          <!--<div class="col-md-4" style="white-space:nowrap;"><strong>Select Ledger*</strong></div>-->
                                          <label for="Product Type" class="col-md-4 control-label">Select Ledger*</label>
                                          <div class="col-md-8">
                                             <select class="select2" name="ledger_id" id="ledger_id"  title="Select Ledger">
                                             <?=get_ledger($dbcon,$rel['ledger_id']," and l_group in (16,17)");?>
                                             </select>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-12 margin_row">
                                   
                                 <!--  Start jayesh  15-7-2021 dynamic data from database  -->
                                      <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Product Type*</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2" id="product_type" name="product_type" onchange="pro_status(this.value);get_product_code(this.value);">
                                             <?php echo get_product_type_company($dbcon,$rel['product_type'],''); ?>
                                             </select>
                                          </div>
                                       </div>
                                    </div>
                                     <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="opening stock" class="col-md-4 control-label">Select Category</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2" name="product_category" id="product_category">
                                             <?=get_all_category($dbcon,$rel['product_category']);?>
                                             </select>
                                          </div>
                                       </div>
                                    </div>
                                     <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Item Code</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text" class="form-control" id="product_icode" name="product_icode" placeholder="Item Code" value="<?=$rel['product_icode'];?>" readonly />
                                             <input type="hidden" class="form-control" id="product_icode_code" name="product_icode_code"  value="" readonly />
                                          </div>
                                       </div>
                                    </div>
								</div>
								 <div class="col-md-12 margin_row">
                                     <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Product Name*</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text"  class="form-control" id="product_name" name="product_name" placeholder="Product Name"  value="<?=htmlspecialchars(stripcslashes($rel['product_name']))?>" />
                                          </div>
                                       </div>
                                    </div>
                                     <div class="col-md-4">
                                       <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'','4','8','',''); ?>
                                    </div>
                                    <div class="col-md-4">                                      
                                       <div class="form-group">
                                          <label for="Product Image" class="col-md-4 control-label">Product Image</label>
                                          <div class="col-md-8 col-xs-11">
                                              <input type="file" name="image_name" id="image_name"  accept="image/*" />
                                              <?php if($rel['image_name']!=null){ ?>
                                                <a class="btn btn-xs btn-primary" title="View Image" data-toggle="tooltip" data-id="2" data-placement="top" href="javascript:void(0)" onclick="view_product_image('<?=$pro_id?>')"><i class="fa fa-eye"></i></a>
                                              <?php } ?>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                  <div class="col-md-12 margin_row">
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label" >Drawing Number </label>
                                          <div class="col-md-6 col-xs-10">
                                             <select class="select2" name="drawing_id" id="drawing_id" onChange="get_revision_data(this.value)" title="SO No.">
                                             <?=getdrawingnumber($dbcon,$rel['drawing_id']);?> 
                                             </select>
                                          </div>   
                                          <div class="col-md-2 col-xs-1">   
                                             <a class="btn btn-primary" title="View Image" data-toggle="tooltip" data-id="2" data-placement="top" href="javascript:void(0)" onclick="add_drawing()"><i class="fa fa-plus"></i></a>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label" >Revision </label>
                                          <div class="col-md-6 col-xs-11">
                                             <select class="select2" name="revision_id" id="revision_id"  title="SO No." onchange="load_revision_image(this.value)">
                                             <?=getrevision_validate($dbcon,$rel['revision_id'], $rel['drawing_id']);?> 
                                             </select>
                                          </div>
										  <div class="col-md-1" id="r_image"></div>
                                       </div>
                                    </div>
									
                                  
                                 </div>
                                  <div class="col-md-12 margin_row">
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">HSN Code</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2" name="product_hsn" id="product_hsn"  title="Select HSN Code" onchange="getGst(this.value);">
												<?=get_hsn($dbcon,$rel['product_hsn']);?>
                                             </select>
                                          </div>
                                       </div>
                                    </div>
                                     <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Sale GST</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text" class="form-control" id="product_sale_gst" name="product_sale_gst" placeholder="Sale GST" value="<?=$rel['product_sale_gst']?>" readonly required />
                                             <!-- <select class="select2" name="product_sale_gst" id="product_sale_gst"  title="Select Unit" required>
                                             /*get_tax_percentage($dbcon,$rel['product_sale_gst']);*/
                                             </select> -->
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Purchase GST</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text" class="form-control" id="product_purchase_gst" name="product_purchase_gst" placeholder="Purchase GST" value="<?=$rel['product_purchase_gst']?>" readonly required />
                                             <?php /*<select class="select2" name="product_purchase_gst" id="product_purchase_gst"  title="Select Unit" required>
                                             <?=get_tax_percentage($dbcon,$rel['product_purchase_gst']);?>
                                             </select>*/?>
                                          </div>
                                       </div>
                                    </div>
                                 
                                 </div>
                                 <div class="col-md-12 margin_row" style="margin-top:25px !important;">
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Production Unit</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2" name="product_base_unit" id="product_base_unit"  title="Select Unit" onchange="get_product_unit(this.value)" required <?=$disabled?>>
                                             <?php if($mode=='Clone') { echo getunit($dbcon,$rel['product_base_unit']); } else { echo getunit($dbcon,3); } ?>
                                             </select>
                                             <?php if($mode=='Clone') { ?>
                                                <input type="hidden" name="product_base_unit" value="<?=$rel['product_base_unit']?>">
                                              <?php } ?>  
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Qty</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text" class="form-control" name="product_base_qty" id="product_base_qty" value="<?php if($mode=='Clone'){ echo $rel['product_base_qty'];  } else { ?> 1 <?php } ?>" onkeypress="return isNumberKey(event)" required  <?=$readonly?>  />
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Purchase Unit</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2" name="product_conv_unit" id="product_conv_unit"  title="Select Unit"  required <?=$disabled?>>
                                             <?php if($mode=='Clone') { echo getunit($dbcon,$rel['product_conv_unit']); } else { echo getunit($dbcon,3); } ?>
                                             </select>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Qty</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text" class="form-control" name="product_conv_qty" id="product_conv_qty" value="<?php if($mode=='Clone'){ echo $rel['product_conv_qty'];  } else { ?> 1 <?php } ?>" onkeypress="return isNumberKey(event)" required  <?=$readonly?> />
                                          </div>
                                       </div>
                                    </div>
                                    <input type="hidden" name="mode" id="mode" value="<?php echo "clone";  ?>" />
                                    <input type="hidden" name="eid_main" id="eid_main" value="<?php if($mode=='Clone'){ echo $rel['product_id']; } ?>" />
                                 </div>
                                 <div class="clearfix" style="margin-bottom:10px;">		
                                 </div>
                                 <div class="col-md-5"></div>
                              </div>
                        </div>
                     </section>
                  </div>
               </div>
               <!--- Tab View -->
               <div class="row " style="background-color:white !important;padding:10px;">
               <div class="col-xs-2"> <!-- required for floating -->
               <!-- Nav tabs -->
               <ul class="nav nav-tabs tabs-left">
               <!--<li class="active"><a href="#tunit" data-toggle="tab" id="ltunit" >Unit Converter</a></li>-->
				<li class="active"><a href="#tbopen" data-toggle="tab" id="ltbopen" >Godown Opening</a></li>
				<li><a href="#timages" data-toggle="tab" id="ltimages">Images</a></li>
				<li><a href="#tdescription" data-toggle="tab" id="ltdescription">Description</a></li>
				<li><a href="#tpurchase" data-toggle="tab" id="ltpurchase">Purchase Party</a></li>
				<li><a href="#talternative" data-toggle="tab" id="ltalternative">Alternative Product</a></li>
				<li><a href="#tjobpurchase" data-toggle="tab" id="ltjobwork"> Jobwork Party</a></li>
				<li><a href="#tprocess" data-toggle="tab" id="ltprocess">Process List</a></li>
				<li><a href="#tparam" data-toggle="tab"  id="ltparam">QC Parameter</a></li>
				<li><a href="#tsetting" data-toggle="tab" id="ltreq">Product Setting</a></li>
				<li><a href="#tscrap" data-toggle="tab" id="ltscrap">Scrap Details</a></li>
				<li><a href="#tmake" data-toggle="tab" id="ltmake">Make</a></li>
               <!-- <li class="stagelist"><a href="#stageprocess" data-toggle="tab" id="ltprocess">Stage List</a></li> -->
               </ul>
               </div>
               <div class="col-xs-9">
               <!-- Tab panes -->
               <div class="tab-content">
               <!--
                  <div class="tab-pane active" id="tunit" >
                  	
                  	<div class="row">
                  		<div class="col-md-12">
                  			<h3 style="text-align:center;"  class="head_margin"><a style="border-bottom:dotted blue thin">Unit Converter</a></h3>
                  		</div>
                  	</div>
                  	
                  	<div class="row">
                  	
                  	
                  	
                  	<div class="col-md-12 margin_row">
                  				
                  			<table class="table table-bordered">
                  				
                  				<tr>
                  					
                  					<th>Alt.Qty</th>
                  					<th>Alt.Unit</th>
                  					<th>Base Qty</th>
                  					<th>Base Unit</th>
                  					<td></td>
                  				</tr>
                  				
                  				<tr>
                  					
                  					<td>
                  						<input type="text" class="form-control" name="utab_alt_qty" id="utab_alt_qty" onkeypress="return isNumberKey(event)"  />
                  					</td>
                  					<td>
                  						<select class="form-control" name="utab_alt_unit" id="utab_alt_unit">
                  							<option value="">--Select Unit--</option>
                  							<?=getunit($dbcon,0);?>
                  						</select>
                  					</td>
                  					<td><input type="text" class="form-control" name="utab_basic_qty" value="1" id="utab_basic_qty" onkeypress="return isNumberKey(event)"  /></td>
                  					<td>
                  						<select class="form-control" name="utab_basic_unit" id="utab_basic_unit">
                  							<option value="">--Select Unit--</option>
                  							<?=getunit($dbcon,0);?>
                  						</select>
                  					</td>
                  					<td><input type="button" class="btn btn-primary" value="ADD"  style="box-shadow: 3px 3px #61a642;" onclick="add_unit_converter()" id="add_unit" /></td>
                  					
                  					<input type="hidden" id="edit_id" value=""  />
                  					<input type="hidden" id="eid" value=""  />
                  				</tr>
                  				
                  			</table>
                  			
                  			<div class="table table-bordered" id="table_unit_converter"></div>
                  			
                  		</div>
                  		
                  	</div>
                  	
                  </div>
                  -->
               

					<div class="tab-pane active" id="tbopen">
					   <div class="row">
					      <div class="col-md-12">
					         <h3 style="text-align:center;"  class="head_margin"><a style="border-bottom:dotted blue thin">Godown Opening Stock</a></h3>
					      </div>
					      <div class="col-md-12">
					         <table class="table table-bordered">
					            <tr>
					               <th>#</th>
					               <th>Godown</th>
					               <th>Stock</th>
					               <th>Priority</th>
					            </tr>
					            <?php 
					               $cnt=1;
					               $selb=$dbcon->query("select * from mst_godown where g_status=0");
					               while($rb=mysqli_fetch_array($selb))
					               {
					               ?>
					            <tr>
					               <td><?php echo $cnt; ?></td>
					               <td><?php echo $rb['gd_name']; ?></td>
					               <td>
					                  <input type="text" class="form-control bstock" name="bstock[]" value="<?php echo get_stock_by_branch($dbcon,$rb['gd_id'],$rel['product_id'],"stock"); ?>" onkeypress="return isNumberKey(event);" onkeyup="total_stock_value_count()"  />
					                  <input type="hidden" class="form-control bid" name="bid[]" value="<?php echo $rb['gd_id']; ?>" />
					               </td>
					               <td>
					                  <input type="text" class="form-control bpriority" name="bpriority[]" value="<?php echo get_stock_by_branch($dbcon,$rb['gd_id'],$rel['product_id'],"priority"); ?>" onkeypress="return isNumberKey(event)" />
					               </td>
					            </tr>
					            <?php $cnt++; } ?>
					            <input type="hidden" name="branch_mode" id="branch_mode" value="add_branch_stock"  />
					            <!-- <tr>
					               <td colspan="4" align="center">
					                  <input type="button" class="btn btn-primary" value="ADD"  style="box-shadow: 3px 3px #61a642;" onclick="add_branch_stock()" id="add_bstock" />
					               </td>
					            </tr> -->
					         </table>
					      </div>
					   </div>
					</div>
					<div class="tab-pane" id="timages" >
					   <div class="row">
					      <div class="col-md-12">
					         <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Upload Product Images</a></h3>
					      </div>
					   </div>
					   <table class="table table-bordered">
					      <tr> 
					         <th>
					            <input type="file" name="file[]" id="file" multiple/>
					         <th>
					         <th>
					            <input type="button" name="" value="Upload" class="btn btn-info" onclick="add_product_image()" />
					            <input type="hidden" name="img_mode" id="img_mode" value="add_product_image_temp" />
					         </th>
					      </tr>
					   </table>
					   <span id="uploaded_image"></span>
					</div>
					<div class="tab-pane" id="tdescription" >
					   <div class="row">
					      <div class="col-md-12">
					         <div class="form-group">
					            <label for="Product Description" class="col-md-4 control-label">Description</label>
					            <div class="col-md-8 col-xs-11">
					               <textarea class="form-control" id="product_desc" name="product_desc" placeholder="Enter Product Description"><?=$rel['product_desc']?></textarea>
					            </div>
					         </div>
					         <div class="clearfix"></div>
					         <div class="form-group">
					            <label for="Product Specification" class="col-md-4 control-label">Specification</label>
					            <div class="col-md-8 col-xs-11">
					               <textarea class="form-control" id="product_spec" name="product_spec" placeholder="Enter Product Specification"><?=$rel['product_spec']?></textarea>
					            </div>
					         </div>
					      </div>
					   </div>
					</div>
					<div class="tab-pane" id="tpurchase" >
					   <div class="row">
					      <div class="col-md-12">
					         <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Purchase Party</a></h3>
					      </div>
					   </div>
					   <div class="row">
					      <div class="col-md-12 margin_row">
					         <table class="table table-bordered">
					            <tr>
					               <th>Party Name</th>
					               <th>Rate</th>
					               <td></td>
					            </tr>
					            <tr>
					               <td>
					                  <select class="select2" name="party_id" id="party_id">
					                  <?php  echo getcust($dbcon,$id) ?>
					                  </select>
					               </td>
					               <td>
					                  <input type="text" class="form-control" name="party_rate" id="party_rate" onkeypress="return isNumberKey(event)"  />
					               </td>
					               <td><input type="button" class="btn btn-primary" value="ADD" onclick="add_party_purchase()" id="add_party_btn" /></td>
					               <input type="hidden" id="edit_id_party" value=""  />
					               <input type="hidden" id="eid_party" value=""  />
					            </tr>
					         </table>
					         <div id="table_party_purchase"></div>
					      </div>
					   </div>
					</div>
					<div class="tab-pane" id="talternative" >
					   <div class="row">
					      <div class="col-md-12">
					         <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Alternative Product</a></h3>
					      </div>
					   </div>
					   <div class="row">
					      <div class="col-md-12 margin_row">
					         <table class="table table-bordered">
					            <tr>
					               <th>Alternative Product Name</th>
					               <td>Action</td>
					            </tr>
					            <tr>
					               <td>
					                  <select class="select2" name="alternative_product_id" id="alternative_product_id">
					                  <?php  echo getproduct($dbcon,$id) ?>
					                  </select>
					               </td>
					               
					               <td><input type="button" class="btn btn-primary" value="ADD" onclick="add_alternative_product()" id="add_alternative_btn" /></td>
					               <input type="hidden" id="edit_id_alternate" value=""  />
					               <input type="hidden" id="eid_alternate" value=""  />
					            </tr>
					         </table>
					         <div id="table_alternative_product"></div>
					      </div>
					   </div>
					</div>
					<div class="tab-pane" id="tjobpurchase" >
					   <div class="row">
					      <div class="col-md-12">
					         <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Jobwork Party</a></h3>
					      </div>
					   </div>
					   <div class="row">
					      <div class="col-md-12 margin_row">
					         <table class="table table-bordered">
					            <tr>
					               <th>Process Name</th>
					               <th>Party Name</th>
					               <th>Rate</th>
					               <td></td>
					            </tr>
					            <tr>
					               <td>
					                  <select class="select2" name="job_party_process_id" id="job_party_process_id">
					                  <?php echo get_all_process($dbcon,$id) ?>
					                  </select>
					               </td>
					               <td>
					                  <select class="select2" name="job_party_id" id="job_party_id">
					                  <?php  echo getcust($dbcon,$id) ?>
					                  </select>
					               </td>
					               <td>
					                  <input type="text" class="form-control" name="job_party_rate" id="job_party_rate" onkeypress="return isNumberKey(event)"  />
					               </td>
					               <td><input type="button" class="btn btn-primary" value="ADD"  style="" onclick="add_job_party_purchase()" id="add_job_party_btn" /></td>
					               <input type="hidden" id="edit_id_job_party" value=""  />
					               <input type="hidden" id="eid_job_party" value=""  />
					            </tr>
					         </table>
					         <div id="table_job_party_purchase"></div>
					      </div>
					   </div>
					</div>
					<div class="tab-pane" id="tparam">
                  <div class="row">
                     <div class="col-md-12">
                        <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Qc Parameter</a></h3>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-12 margin_row">
                        <table class="table table-bordered">
                           <tr>
                           <th>Process Name</th>
                           <th>Parameter Name</th>
                           <th>Base Value</th>
                           <th>Tolerance (+)</th>
                           <th>Tolerance (-)</th>
                           <th>Unit</th>
                              <td></td>
                           </tr>
                           <tr>
                              <td>
                                 <select class="select2" name="qc_process_id" id="qc_process_id" >
                                 <?php // echo get_all_process($dbcon,$id) ?>
                                 </select>
                              </td>
                           <td>
                                 <select class="select2" name="param_id" id="param_id">
                                 <?php  echo get_all_parameter($dbcon,$id) ?>
                                 </select>
                              </td>
                              <td>
                                 <input type="text" class="form-control" name="param_value" id="param_value"  />
                              </td>
                              <td>
                                 <input type="text" class="form-control" name="tolerance_plus" id="tolerance_plus"  />
                              </td>
                              <td>
                                 <input type="text" class="form-control" name="tolerance_minus" id="tolerance_minus" />
                              </td>
                              <td>
                                 <select class="select2" name="param_unit_id" id="param_unit_id">
                                    <?php  echo getunit($dbcon) ?>
                                 </select>
                              </td>
                              <td><input type="button" class="btn btn-primary" value="ADD"  style="" onclick="add_param_value()" id="add_param" /></td>
                              <input type="hidden" id="edit_id_param" value=""  />
                           </tr>
                        </table>
                        <div id="table_product_parameter"></div>
                     </div>
                  </div>
               </div>
					<div class="tab-pane" id="tsetting" >
					   <div class="row">
					      <div class="col-md-12">
					         <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Product Setting</a></h3>
					      </div>
					   </div>
					   <div class="row" style="margin-left:20px;">
					      <div class="col-md-4 margin_row">
					         <label class="container"><span class="margin_span">Process on Product</span>
					         <input type="checkbox"  name="product_setting_check[]" id="product_setting_check"  value="process_product" class="product_process process_on_procduct" <?php if($mode=='Clone'){ if(in_array("process_product",$check_array_setting)) { echo "checked"; } } ?> onclick="return false;">
					         <span class="checkmark"></span>
					         </label>
					      </div>
					      <div class="col-md-4 margin_row">
                        <label class="container"><span class="margin_span">QC For Product</span>
                        <input type="checkbox"  name="product_setting_check[]" id="product_setting_check"  value="product_qc" class="product_process qc_on_procduct" <?php if($mode=='Clone'){ if(in_array("product_qc",$check_array_setting)) { echo "checked"; } } ?> onclick="return false;" >
                        <span class="checkmark"></span>
                        </label>
                     </div>
					      <div class="col-md-12" style="height:10px;"></div>
					      <div class="col-md-4">
					         <label class="container"><span class="margin_span">Tolerance For Product</span>
					         <input type="checkbox"  name="tolerance" id="tolerance"  value="1"  <?php if($rel['tolerance']=="1"){ echo "checked"; }  ?>>
					         <span class="checkmark"></span>
					         </label>
					      </div>
					      <div class="col-md-4">
					         <label class="container col-md-4">Minimum (%)</label>
					         <div class="col-md-8">
					            <input type="number" min="0" class="form-control " name="minimum_tolerance" id="minimum_tolerance" value="<?=$rel['minimum_tolerance']?>"  />
					         </div>
					      </div>
					      <div class="col-md-4">
					         <label class="container col-md-4"> Maximum (%)</label>
					         <div class="col-md-8">
					            <input type="number" min="0" class="form-control " name="maximum_tolerance" id="maximum_tolerance" value="<?=$rel['maximum_tolerance']?>" />
					         </div>
					      </div>
					     <!-- START JAYESH ADD NEW FIELDS FIELD  15-07-2021 -->
					      <div class="col-md-4">
					         
					       &nbsp;
					      </div>
					      <div class="col-md-4">
					         <label class="container col-md-4">Minimum Value</label>
					         <div class="col-md-8">
					            <input type="number" min="0" class="form-control " name="minimum_tolerance_value" id="minimum_tolerance_value" value="<?=$rel['minimum_tolerance_value']?>" />
					         </div>
					      </div>
					      <div class="col-md-4">
					         <label class="container col-md-4"> Maximum Value</label>
					         <div class="col-md-8">
					            <input type="number" min="0" class="form-control " name="maximum_tolerance_value" id="maximum_tolerance_value" value="<?=$rel['maximum_tolerance_value']?>" />
					         </div>
					      </div>
					   </div>
					   <!-- start jayesh (15-7) -->
					    <div class="row">
					      <div class="col-md-12">
					         <hr style="width:100%;border-bottom: 1px solid ; color: #ccc;"/>
					      </div>
					   </div>
					    <div class="row">
					      <div class="col-md-12">
					         <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Product Rate and stock</a></h3>
					      </div>
					   </div>
					    <!-- Start jayesh (15-7-21) reason : set in tab product settings-->
					     
                                 <div class="col-md-12 margin_row">
                                   
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Product Material</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="form-control" name="product_specification" id="product_specification" <?=$disabled?>>
                                             <?= get_product_specification($dbcon,$rel['product_specification']); ?>
                                             </select>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="opening stock" class="col-md-4 control-label">Product Valuation</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text" class="form-control" name="product_opening_valuation" id="product_opening_valuation" value="<?=$rel['product_opening_valuation']?>" onkeypress="return isNumberKey(event)"  />
                                          </div>
                                       </div>
                                    </div>
 <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="opening stock" class="col-md-4 control-label">Product Barcode</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text" class="form-control" name="product_barcode" id="product_barcode" value="<?=$rel['product_barcode']?>" />
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 
                                 <div class="col-md-12 margin_row">
                                   <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label" >Net Weight </label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text" class="form-control" name="product_net_weight" id="product_net_weight" value="<?=$rel['product_net_weight']?>" onkeypress="return isNumberKey(event)"  />
                                          </div>
                                       </div>
                                    </div>
                                  	<div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Making Time</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text" class="form-control" name="product_making_time" id="product_making_time" value="<?=$mode=='Clone'?$rel['product_making_time']:0?>" /> ( In Minute..)
                                          </div>
                                       </div>
                                    </div>
                                     <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">GST Type</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2" name="product_gst" id="product_gst"  title="Select Unit" required>
                                                <option value="">--Select GST Type--</option>
                                                <option value="including" <?php if($rel['product_gst']=='including'){ echo "selected"; }?>>Including</option>
                                                <option value="excluding" <?php if($mode=='Clone'){ if($rel['product_gst']=='excluding'){ echo "selected"; } } else { echo "selected"; } ?>>Excluding</option>
                                             </select>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                   
                                 <div class="col-md-12 margin_row">                                   
                                   <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Sale Rate</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="number" min="0" class="form-control" id="product_sale_rate" name="product_sale_rate" placeholder="Product Sale Rate" value="<?=$rel['product_sale_rate']?>" onkeypress="return isNumberKey(event)"  />
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Purchase Rate</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="number" min="0" class="form-control" id="product_purchase_rate" name="product_purchase_rate" placeholder="Product Purchase Rate" value="<?=$rel['product_purchase_rate']?>" onkeypress="return isNumberKey(event)"  />
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Weight" class="col-md-4 control-label">Weight</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text" name="weight" min="0" id="weight" class="form-control" placeholder="Weight" value="<?=$mode=='Edit'?$rel['weight']:''?>"  />
                                          </div>
                                       </div>
                                    </div>
                                     
                                 </div>
                                  <div class="col-md-12 margin_row">
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="opening stock" class="col-md-4 control-label">Opening Stock</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="number" name="product_opening" min="0" id="product_opening" class="form-control" placeholder="Opening Stock" value="<?=$mode=='Edit'?$rel['product_opening']:0?>" required readonly />
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="opening stock" class="col-md-4 control-label">Minimum Stock</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="number" name="product_min_stock" min="0" id="product_min_stock" class="form-control" placeholder="Minimum Stock" value="<?=$mode=='Edit'?$rel['product_min_stock']:''?>" onkeypress="return isNumberKey(event)"  onchange="add_decimal_weight(this);"  required />
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="opening stock" class="col-md-4 control-label">Maximum Stock</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="number" name="product_max_stock" min="0" id="product_max_stock" class="form-control" placeholder="Maximum Stock" value="<?=$mode=='Edit'?$rel['product_max_stock']:''?>" onkeypress="return isNumberKey(event)" onchange="add_decimal_weight(this);" required />
                                          </div>
                                       </div>
                                    </div>
                                 </div>
 						 <div class="col-md-12 margin_row">
 					 				<div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Minimum Order" class="col-md-4 control-label">Minimum Order</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="number" name="product_min_order" min="0" id="product_min_order" class="form-control" placeholder="Minimum Order" value="<?=$mode=='Edit'?$rel['product_min_order']:''?>" onkeypress="return isNumberKey(event)" onchange="add_decimal(this);" required />
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Maximum Order" class="col-md-4 control-label">Maximum Order</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="number" name="product_max_order" min="0" id="product_max_order" class="form-control" placeholder="Maximum Order" value="<?=$mode=='Edit'?$rel['product_max_order']:''?>" onkeypress="return isNumberKey(event)" onchange="add_decimal(this);" required />
                                          </div>
                                       </div>
                                    </div>
 					 			  <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="is_grn" class="col-md-4 control-label">GRN Required?</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2" id="is_grn" name="is_grn" >
                                             <?php echo get_common_boolean_value($dbcon,$rel['is_grn']); ?>
                                             </select>
                                          </div>
                                       </div>
                                    </div>
                                   
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Reorder Quantity" class="col-md-4 control-label">Reorder Quantity</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text" name="reorder_qty" min="0" id="reorder_qty" class="form-control" placeholder="Reorder Quantity" value="<?=$mode=='Edit'?$rel['reorder_qty']:''?>" onkeypress="return isNumberKey(event)" onchange="add_decimal(this);"  />
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Selft Life Days" class="col-md-4 control-label">Self Life Days</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="number" name="self_life_days" min="0" id="self_life_days" class="form-control" placeholder="Self Life Days" value="<?=$mode=='Edit'?$rel['self_life_days']:''?>" onkeypress="return isNumberKey(event)"  />
                                          </div>
                                       </div>
                                    </div>
									<div class="col-md-4">
									   <div class="form-group">
									      <label for="Warrenty Period" class="col-md-4 control-label">Warrenty Period</label>
									      <div class="col-md-8 col-xs-11">
									         <input type="number" name="warrenty_period" min="0" id="warrenty_period" class="form-control" placeholder="Warrenty Period" value="<?=$mode=='Edit'?$rel['warrenty_period']:''?>"  onkeypress="return isNumberKey(event)"  />
									      </div>
									   </div>
									</div>
                                    
                                 </div>
								<div class="col-md-12 margin_row">                                  
                                  
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Model No" class="col-md-4 control-label">Model No</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text" name="model_no" min="0" id="model_no" class="form-control" placeholder="Model No" value="<?=$mode=='Edit'?$rel['model_no']:''?>"  />
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Item Type" class="col-md-4 control-label">Item Type</label>
                                          <div class="col-md-8 col-xs-11">
                                           <select class="select2" id="item_type" name="item_type" >
                                          <?php echo get_product_item_type_company($dbcon,$rel['item_type'],''); ?>
                                          </select>                                            
                                          </div>
                                       </div>
                                    </div> 
                                    
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Item Type" class="col-md-4 control-label">Material Center</label>
                                          <div class="col-md-8 col-xs-11">
									<select class="select2" id="product_mat_center" name="product_mat_center">
										<option value="">--select Material Center--</option>
										<?=get_all_godown($dbcon,$rel['product_mat_center'],'');?>
									</select> 
									</div>
									</div>  
                                                          
                                 </div>  
                                                         
                                 </div>
						
    <div class="col-md-12 margin_row">
                                  <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="Item Type" class="col-md-4 control-label">Item Status</label>
                                          <div class="col-md-8 col-xs-11">
                                           <select class="select2" id="item_status" name="item_status" onchange="getitemstatus(this.value);">
                                          <?php echo get_product_item_status_company($dbcon,$rel['item_status'],''); ?>
                                          </select>
                                            
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-12 margin_row">
                                  <div class="col-md-4">
                                       <div class="form-group">
                                          <label for="is_grn" class="col-md-4 control-label">Stock Count?</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2" id="product_stock_count" name="product_stock_count" >
                                             <?php echo get_common_boolean_value($dbcon,$rel['product_stock_count']); ?>
                                             </select>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                    <div class="col-md-4 show_hide_fields">
                                       <div class="form-group">
                                          <label for="Item Date" class="col-md-4 control-label">Item Date</label>
                                          <div class="col-md-8 col-xs-11">
                                          <input id="item_status_date" name="item_status_date" type="text" class="form-control error default-date-picker required valid" title="Item Date" placeholder="Item Date" value="<?=$mode=='Edit'?$rel['item_status_date']:''?>" placeholder="Item Date" >
                                          
                                          </div>
                                       </div>
                                    </div>                                    
                                    <div class="col-md-4 show_hide_fields" >
                                       <div class="form-group">
                                          <label for="Reason" class="col-md-4 control-label">Reason</label>
                                          <div class="col-md-8 col-xs-11">
                                          <!-- <select class="select2" id="item_status_reason" name="item_status_reason" >
                                          <?php echo get_product_item_type_reason_company($dbcon,$rel['item_status_reason'],''); ?>
                                          </select>-->
                                          <textarea id="item_status_reason" name="item_status_reason"><?=$mode=='Edit'?$rel['item_status_reason']:''?></textarea>
                                            
                                          </div>
                                       </div>
                                    </div>
                                  </div>
                                  <!-- end  jayesh (15-7-21)-->
					    <!-- End jayesh (15-7) -->
					</div>
					<div class="tab-pane" id="tprocess">
					   <div class="row">
					      <div class="col-md-12">
					         <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Product Process</a></h3>
					      </div>
					   </div>
						<div class="row">
					      <div class="col-md-12 margin_row">
					         <table class="table table-bordered">
					            <tr>
					               <th>Process Name </th>
					               <th>Priority</th>
					               <th>Type</th>
                              <th class="processRate_label_manage">Rate</th>
					               <th>Time  (In Min.)</th>
					               <!-- <th>Opening Stock</th> -->
					               <th class="resource_label_manage">Resource Name</th>
					               <th>Loss (In %)</th>
                              <th>Scrap Tol. (+)</th>
                              <th>Scrap Tol. (-)</th>
					               <td></td>
					            </tr>
					            <tr>
					               <td>
					                  <select class="select2" name="process_id" id="process_id">
					                  
										<?php  echo get_all_process($dbcon,$id) ?> 
										
					                  </select>
					               </td>
					               <td>
					                  <!-- <input type="number" class="form-control" name="process_priority" id="process_priority" /> -->
                                 <label for="process_priority" class="form-control process_priority_label"></label>
					                  <input type="hidden" class="form-control process_priority" name="process_priority" id="process_priority" />
					               </td>
					               <td>
					                  <select class="form-control" name="process_type" id="process_type" onChange="manage_resource(this.value);">
					                     <option value="">--Select Process Type--</option>
					                     <option value="1">Inhouse</option>
					                     <option value="2">Outside</option>
					                  </select>
					               </td>
                              <td class="processRate_label_manage">
                                 <input type="number" class="form-control" name="process_rate" id="process_rate" />
                              </td>
					               <td>
					                  <input type="number" class="form-control" name="process_time" id="process_time" />
					               </td>
					               <!-- <td>
					                  <input type="text" class="form-control" name="process_opening" id="process_opening" />
					                  </td> -->
					               <td class="resource_label_manage">
					                  <select class="select2" name="resource_id" id="resource_id">
					                  <?php  echo get_all_resource($dbcon) ?>
					                  </select>
					               </td>
					               <td>
					                  <input type="number" class="form-control" name="process_loss" id="process_loss" />
					               </td>
                              <td>
                                 <input type="number" class="form-control" name="process_scrap_tolerance_plus" id="process_scrap_tolerance_plus"  />
                              </td>
                              <td>
                                 <input type="number" class="form-control" name="process_scrap_tolerance_minus" id="process_scrap_tolerance_minus"  />
                              </td>
					               <td><input type="button" class="btn btn-primary" value="ADD"  style="" onclick="add_process_value()" id="add_process" /></td>
					               <input type="hidden" id="edit_id_process" value=""  />
					            </tr>
					         </table>
					         <div id="table_product_process"></div>
					      </div>
					   </div>
					</div>
					<div class="tab-pane" id="tscrap" >
					   <div class="row">
					      <div class="col-md-12">
					         <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Scrap Details</a></h3>
					      </div>
					   </div>
					   <div class="row">
					      <div class="col-md-12 margin_row">
							<div class="col-md-12">
								<div class="col-md-2"> <strong> Mat. Issue Weight </strong></div>
								<div class="col-md-6">
									<input type="number" class="form-control" name="material_issue_weight" id="material_issue_weight" onkeypress="return isNumberKey(event)" value="<?=$rel['material_issue_weight']?>" />
								</div>
							</div>
							<div class="col-md-12" style="margin-top: 15px;">
								<div class="col-md-2"> <strong> Scrap Code </strong></div>
								<div class="col-md-6">
									<select class="select2" name="product_scrap_id" id="product_scrap_id">
										 <?=getScrapCode($dbcon,$rel['product_scrap_id'])?>
									</select>
								</div>
							</div>
							<div class="col-md-12" style="margin-top: 15px;">
								<div class="col-md-2"> <strong> Scrap Desc. </strong></div>
								<div class="col-md-6">
									 <textarea class="form-control" id="scrap_desc" name="scrap_desc" placeholder="Enter Scrap Description"><?=$rel['scrap_desc']?></textarea>
								</div>
							</div>
							
							<div class="col-md-12" style="margin-top: 15px;">
								<div class="col-md-2"> <strong> Scrap Qty. </strong> </div>
								<div class="col-md-6">
									<input type="number" class="form-control" name="scrap_qty" id="scrap_qty" value="<?=$rel['scrap_qty']?>"  />
								</div>
							</div>
							
							
							
					        <!--<table class="table table-bordered" style="margin-right: auto;margin-left: auto;width: 80%">
					            <tr>
					               <th width="20%">Mat. Issue Weight</th>
                              <th width="20%">Scrap Code</th>
					               <td width="10%"></td>
					            </tr>
					            <tr>
					               <td>
                                 <input type="number" class="form-control" name="material_issue_weight" id="material_issue_weight" onkeypress="return isNumberKey(event)"  />
                              </td>
                              <td>
                                 <select class="select2" name="product_scrap_id" id="product_scrap_id">
                                 <?php  //echo getScrapCode($dbcon,$id) ?>
                                 </select>
                              </td>
					               <td><input type="button" class="btn btn-primary" value="ADD"  style="" onclick="add_scrap()" id="addscrap_btn" /></td>
					               <input type="hidden" id="edit_id_scrap" value=""  />
					               <input type="hidden" id="eid_scrap" value=""  />
					            </tr>
					         </table>
					         <div id="table_scrap_data"></div>-->
					      </div>
					   </div>
					</div>
               <div class="tab-pane" id="tmake" >
                  <div class="row">
                     <div class="col-md-12">
                        <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Make</a></h3>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-12 margin_row">
                        <table class="table table-bordered" style="margin-right: auto;margin-left: auto;width: 80%">
                           <tr>
                              <th width="20%">Make Name</th>
                              <th width="20%">Make Number</th>
                              <th width="10%">Stock</th>
                              <th width="10%">Rate</th>
                              <td width="10%"></td>
                           </tr>
                           <tr>
                              <td>
                                 <select class="select2" name="make_id" id="make_id">
                                 <?php  echo getmake($dbcon,$id) ?>
                                 </select>
                              </td>
                              <td>
                                 <select class="select2" name="make_number_id" id="make_number_id">
                                 <?php  echo getmakenumber($dbcon,$id) ?>
                                 </select>
                                 <br><br>
                                 <input type="text" class="form-control" name="make_value" id="make_value" onkeypress="" placeholder="Enter Make Value"  onchange="add_decimal(this);" />
                              </td>
                              <td>
                                 <input type="number" class="form-control" name="make_stock" id="make_stock" onkeypress="return isNumberKey(event)"  onchange="add_decimal_weight(this);" />
                              </td>
                              <td>
                                 <input type="number" class="form-control" name="make_rate" id="make_rate" onkeypress="return isNumberKey(event)"  onchange="add_decimal(this);" />
                              </td>
                              <td><input type="button" class="btn btn-primary" value="ADD"  style="" onclick="add_make()" id="addmake_btn" /></td>
                              <input type="hidden" id="edit_id_make" value=""  />
                              <input type="hidden" id="eid_make" value=""  />
                           </tr>
                        </table>
                        <div id="table_make_data"></div>
                     </div>
                  </div>
               </div>
					<!-- <div class="tab-pane" id="stageprocess" >
					   <div class="row">
					      <div class="col-md-12">
					         <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Stage Process</a></h3>
					      </div>
					   </div>
					   <div class="row">
					      <div class="col-md-12 margin_row">
					         <table class="table table-bordered">
					            <tr>
					               <th>Stage Name</th>
					               <th>Contribution In percentage</th>
					               <td></td>
					            </tr>
					            <tr>
					               <td>
					                  <select class="select2" name="party_stage_id" id="party_stage_id">
					                  <?php  echo getstages($dbcon) ?>
					                  </select>
					               </td>
					               <td>
					                  <input type="text" class="form-control" name="stage_per" id="stage_per" onkeypress="return isNumberKey(event)"  />
					               </td>
					               <td><input type="button" class="btn btn-primary" value="ADD"  style="box-shadow: 3px 3px #61a642;" onclick="add_product_stage()" id="add_stage_btn" /></td>
					               <input type="hidden" id="edit_id_product_stage" value=""  />
					               <input type="hidden" id="eid_product_stage" value=""  />
					            </tr>
					         </table>
					         <div id="table_stage_purchase"></div>
					      </div>
					   </div>
					</div> -->
					</div>
					</div>
					<div class="clearfix"></div>
					</div>
					</div>
               <!-- End Tab View -->
               <!--Customer overview end-->
            </section>
            <section>
            <div class="row" style="background-color:white !important;padding:10px;">
            <div class="col-md-4 col-md-offset-5">	
            <input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
            <input type='hidden' name='form_mode' id='form_mode' value='<?php echo $mode; ?>' />				  
            <input type='hidden' name='pid' id='pid' value='<?php if($mode=='Clone'){ echo $rel['product_id']; } else { echo "0"; } ?>' />				  
            <input type='hidden' name='product_model' id='product_model' value='' />				  
            <button type="submit" class="btn btn-shadow btn-success">Submit</button>
            <a href="<?=ROOT.ADMINISTRATION_ROOT.'product_list'?>" type="button" class="btn btn-danger">Cancel</a><div class="col-md-3"></div>
            </div>
            </div>
            </form>
            </section>
         </section>
         <!--main content end-->
         <div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog custom-width">
               <div class="modal-content">
                  <div class="modal-header">
                  <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                     <h3 style="margin-top:-6px; important!">View Images</h3>
                  </div>
                  <div class="modal-body form">
                     <div class="form-group">
                        <div id="product_image"></div>
                     </div>   
                  </div>
                  <div class="modal-footer">
                     <input type="hidden" name="edit_id" id="edit_id" value="" />
                     <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
                  </div>
               </div>
            </div>
         </div>
         <!--footer start-->
         <?php include_once($include.'footer.php');?>
         <!--footer end-->
      </section>
      <!-- Modal -->
      </div><!-- /.modal-dialog -->
      </div><!-- /.modal -->
       <style>
      	.show_hide_fields{
			display:none;
		}
      </style>
      
      <?php include_once($include.'view_revision_image.php');?>  
      <?php include_once($include.'add_productinpro.php');?>  
      <?php include_once($include.'add_drawing.php');?>  
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once($include.'include_js_file.php');?>   
      <script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/product_mst.js?<?php echo time(); ?>"></script>
      <script>
         CKEDITOR.replace( 'product_desc', {
         	enterMode: CKEDITOR.ENTER_BR
         });
         CKEDITOR.replace( 'product_spec', {
         	enterMode: CKEDITOR.ENTER_BR
         });
         $(".select2").select2({
         	width: '100%'
         });
          $('.default-date-picker').datepicker({
         	format: 'dd-mm-yyyy',
         	autoclose: true
         }).datepicker("setDate",'now');
          var tableToExcel = (function() {
          var uri = 'data:application/vnd.ms-excel;base64,'
            , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
            , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
            , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
          return function(table, name) {
            if (!table.nodeType) table = document.getElementById(table)
            var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
            window.location.href = uri + base64(format(template, ctx))
          }
         })()
         
          /*START JAYESH Item Search filed hide show 21-07-2021*/
     
		window.onkeyup = function(e) {
		    var event = e.which || e.keyCode || 0; // .which with fallback
		    if (event == 27) { // ESC Key
		       window.location=root_domain+administration_domain+'product_list'; // Navigate to URL
		    }
		}
		
		function saveandcopy(id)
		{
			window.location=root_domain+administration_domain+'product_clone/'+id;
			return false;
		}
	
        function getitemstatus(id)
        {
        	if(id=='3' || id=='2')
			{
				$('.show_hide_fields').css('display','block');
				return false;
			}
			else
			{
				$('.show_hide_fields').css('display','none');
				return false;
			}
		}
		/*function readonlyform()
		{
			$('#product_add input').attr('readonly', 'readonly');
			$('#product_add select').attr('disabled', 'disabled');
			
		}
		function edit_form()
		{
			$('#product_add input').removeAttr('readonly');
			$('#product_add select').removeAttr('disabled');
			
		}*/
        </script>
      <?php
         echo "<script>pro_status(".$rel['product_type'].");</script>";
         echo "<script>getitemstatus(".$rel['item_status'].");</script>";
		 
		 if($mode=="Clone"){
			echo "<script>load_revision_image(".$rel['revision_id'].");</script>";
		 }
		 ?>
    
   </body>
</html>