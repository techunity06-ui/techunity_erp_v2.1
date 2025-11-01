<?php
    session_start();
    include('../include/urlfile.php');

    $token = md5(rand(1000, 9999));
    $_SESSION['token'] = $token;
    $form = "Item";
    // check permission for item list
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        ADMINISTRATOR_PRODUCT_CREATE,
        ADMINISTRATOR_PRODUCT_UPDATE,
        ADMINISTRATOR_PRODUCT_CLONE
    ]);
    $branch_id = $_SESSION['branch_id'];
    $actual_link = $_SERVER['REQUEST_URI'];
    $urls = explode("/", $actual_link);

    $readonly = '';
    $disabled = '';

    $readonly_unit = "";
    $com = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
    $comty = brp_mysqli_fetch_assoc($dbcon->query($com));
    if (strpos($_SERVER['REQUEST_URI'], "product_edit_ip") == false) {
        $mode = "Add";
        if (!in_array(ADMINISTRATOR_PRODUCT_CREATE, $bulkAccessArray)) {
            header("Location: " . DOMAIN . "permission_access");
        }
    } else {
       $mode = "Edit";
	   
        if (!in_array(ADMINISTRATOR_PRODUCT_UPDATE, $bulkAccessArray)) {
            header("Location: " . DOMAIN . "permission_access");
        }
        $pro_id = $dbcon->real_escape_string($_REQUEST['id']);
        $query = "select pm.*,tdd.drawing_number from product_mst as pm left join tbl_drawing as tdd on tdd.drawing_id = pm.drawing_id where product_id=$pro_id";
        $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
		

        $query_field = "select * from product_name_field where product_id=$pro_id";
        $rel_field = brp_mysqli_fetch_assoc($dbcon->query($query_field));
        //	echo "<pre>"; print_r($rel);die;
        $check_array = explode(",", $rel['product_check']);
        $check_array_setting = explode(",", $rel['product_setting_check']);

        if ($rel['product_status'] == 0) {
            $disabled_u = 'disabled';
        }

        $readonly_unit = "readonly";
        $disabled_u = 'disabled';
        /*$bomquery="select bom_id from tbl_bom where bom_product=$pro_id and bom_status=0";
$bomrel=brp_mysqli_num_rows($dbcon->query($bomquery));

if($bomrel > 0){
$readonly='readonly';
$disabled = 'disabled';
}*/
    }



    $product_query = "SELECT MAX(product_id) as max_id , MIN(product_id) as min_id  FROM product_mst";
    $row = brp_mysqli_fetch_array($dbcon->query($product_query));

    $prev_id = $pro_id - 1;
    $max_id = $row['max_id'];
    $min_id = $row['min_id'];
    $next_id = $pro_id + 1;
    /* Start Jayesh  15-7-2021  serail numbers*/
    /*$serial_no = 0;
$prefix = 'BRP';
while ($serial_no <= 100)
{
$numZero = 7 - strlen($serial_no);
echo '<br>'.$prefix.str_repeat('0', $numZero).$serial_no;
$x++;
}
die;*/

    /* Start Jayesh  15-7-2021  serail numbers*/
    $companyConfiguration = getCompanyConfiguration($dbcon);
    $purchase_party_show = $companyConfiguration['purchase_party_show'];
    //var_dump($purchase_party_show);
    $getspecialConfiguration = getspecialConfiguration($dbcon);
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>ITEM</title>
        <?php include_once($include . 'include_css_file.php'); ?>
        <link href="<?= ROOT ?>assets/sweetalert2/sweetalert2.min.css" rel="stylesheet">
        <style>
            .margin_row {
                margin-top: 10px !important;
            }

            .margin_span {
                margin-left: 10px !important;
                font-size: 16px;
                vertical-align: middle;
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
            .container:hover input~.checkmark {
                background-color: #ccc;
            }

            /* When the checkbox is checked, add a blue background */
            .container input:checked~.checkmark {
                background-color: #2196F3;
            }

            /* Create the checkmark/indicator (hidden when not checked) */
            .checkmark:after {
                content: "";
                position: absolute;
                display: none;
            }

            /* Show the checkmark when checked */
            .container input:checked~.checkmark:after {
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

            .head_margin {
                margin-bottom: 10px;
            }
        </style>
        <script type="text/javascript" src="<?php echo ROOT; ?>js/jquery.form.min.js"></script>
    </head>

    <body>
        <section id="container" class="sidebar-closed">
            <?php include_once($include . 'include_top_menu.php'); ?>
            <!--sidebar start-->
            <?php include_once($include . 'left_menu.php'); ?>
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
                                        New <?= $form ?>
                                        <!--<a href="<?= ROOT . 'import_product' ?>" >
    <button class="btn btn-primary btn-flat pull-right">Import <?= $form ?></button></a>-->
                                    </h3>
                                </header>
                                <div class="">
                                    <ul class="breadcrumb">
                                        <li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="<?= ROOT . 'masters_list' ?>"> Masters List</a></li>
                                        <li class="active"><a href="<?= ROOT . ADMINISTRATION_ROOT . 'product_list' ?>"><?= $form ?> List </a></li>
                                    </ul>
                                </div>
                            </section>
                            <!--breadcrumbs end -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 margin_row">
                            <?php if (strpos($_SERVER['REQUEST_URI'], "product_edit") == true) {
                                if ($prev_id > $min_id) { ?>
                                    <div class="col-sm-4">
                                        <a href="<?= ROOT . ADMINISTRATION_ROOT . 'product_edit/' . $prev_id; ?>"><button class="btn btn-shadow btn-success">Previous</button></a>
                                    </div>
                                <?php } ?>
                                <div class="col-sm-4">
                                    &nbsp;
                                    <!-- <a><button  class="btn btn-shadow btn-success" onclick="edit_form();">Edit</button></a>-->
                                </div>
                                <?php if ($next_id <= $max_id) { ?>
                                    <div class="col-sm-4" style="text-align: right;">
                                        <a href="<?= ROOT . ADMINISTRATION_ROOT . 'product_edit/' . $next_id; ?>"><button class="btn btn-shadow btn-success">Next</button></a>
                                    </div>
                            <?php }
                            } ?>
                        </div>
                    </div>
                    <!--Customer overview start-->
                    <div class="row">
                        <div class="col-sm-12">
                            <section class="panel">
                                <header class="panel-heading">
                                    New <?= $form ?>
                                    <span class="tools pull-right">
                                        <a href="javascript:;" class="fa fa-chevron-down"></a>
                                    </span>
                                </header>
                                <div class="panel-body">
                                    <form role="form" id="product_add" action="javascript:;" method="post" name="product_add">
                                        <div class="col-md-12" style="padding-top: 25px;">
                                            <div class="col-md-12 margin_row">


                                                <div class="col-md-4 typeled" style="display: none;">
                                                    <!-- add pathik -->
                                                    <div class="form-group">
                                                        <!--<div class="col-md-4" style="white-space:nowrap;"><strong>Select Ledger*</strong></div>-->
                                                        <label for="Product Type" class="col-md-4 control-label">Select Ledger*</label>
                                                        <div class="col-md-8">
                                                            <select class="select2" name="ledger_id" id="ledger_id" title="Select Ledger">
                                                                <?= get_ledger($dbcon, $rel['ledger_id'], " and l_group in (16,17,18,19,20)"); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- <div class="col-md-12 margin_row"> -->


                                                <!--  Start jayesh  15-7-2021 dynamic data from database  -->
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="product_type" class="col-md-4 control-label">Product Type*</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <select class="select2" id="product_type" name="product_type" onchange="pro_status(this.value);get_product_code(this.value);get_project_product(this.value)/*product_load();*/">
                                                                <?php echo get_product_type_company($dbcon, $rel['product_type'], ''); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php if($getspecialConfiguration['reciclar']==1){?>
                                                <div class="col-md-4" style="display: none;">
                                                    <div class="form-group">
                                                        <label for="product_category" class="col-md-4 control-label">Parent Category*</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <select class="select2" name="parent_category" id="parent_category" onchange="get_child_category();">
                                                                <?= get_all_category($dbcon, $rel['parent_category']); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php }?>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="product_category" class="col-md-4 control-label">Select Category</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <select class="select2" name="product_category" id="product_category">
                                                                <?= get_all_category($dbcon, $rel['product_category']); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-md-4 margin_row">
                                                    <div class="form-group">
                                                        <label for="product_icode" class="col-md-4 control-label">Part Code</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <input type="text" class="form-control" id="product_icode" name="product_icode" placeholder="Item Code" value="<?= $rel['product_icode']; ?>" <?php echo ($companyConfiguration['generate_item_code'] == 0) ? 'readonly' : ''; ?> <?php if ($companyConfiguration['generate_item_code'] == 1) { ?>onkeyup="icode_validation(this.value)" <?php } ?> />
                                                            <input type="hidden" class="form-control" id="product_icode_code" name="product_icode_code" value="" readonly />
                                                            <div id="icodeval" style="color: red; font-size: 13px;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <!-- </div> -->
                                            <?php if ($getspecialConfiguration['filter_concept_permission'] == 1) { ?>
                                                <div class="col-md-12 margin_row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="Product Type" class="col-md-4 control-label">Material Name</label>
                                                            <div class="col-md-8 col-xs-11">
                                                                <select class="select2" name="product_first_name" id="product_first_name" title="Select First Name" onchange="generate_product_name();">
                                                                    <?= get_first_name($dbcon, $rel['first_name_id']); ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="Product Type" class="col-md-4 control-label">Type</label>
                                                            <div class="col-md-8 col-xs-11">
                                                                <select class="select2" name="pro_mst_type" id="pro_mst_type" title="Select First Name" onchange="generate_product_name();">
                                                                    <?= get_type_mst($dbcon, $rel['pro_mst_type']); ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="Product Type" class="col-md-4 control-label">Product Description</label>
                                                            <div class="col-md-8 col-xs-11">
                                                                <select class="select2" name="product_surface_area" id="product_surface_area" title="Select Surface Name" onchange="generate_product_name();">
                                                                    <?= get_surface_area($dbcon, $rel['product_surface_area']); ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 margin_row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="Product Type" class="col-md-4 control-label">Cartridge Dia</label>
                                                            <div class="col-md-8 col-xs-11">
                                                                <select class="select2" name="pro_cartridge_mst" id="pro_cartridge_mst" title="Select Impregnation Name" onchange="generate_product_name();">
                                                                    <?= get_cartridge_dia($dbcon, $rel['pro_cartridge_mst']); ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="Product Type" class="col-md-4 control-label">Size Name</label>
                                                            <div class="col-md-8 col-xs-11">
                                                                <select class="select2" name="product_model_name" id="product_model_name" title="Select Model Name" onchange="generate_product_name();">
                                                                    <?= get_model($dbcon, $rel['product_model_name']); ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="Product Type" class="col-md-4 control-label">Configuration</label>
                                                            <div class="col-md-8 col-xs-11">
                                                                <select class="select2" name="product_installation" id="product_installation" title="Select Installation Name" onchange="generate_product_name();">
                                                                    <?= get_installation($dbcon, $rel['product_installation']); ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 margin_row">

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="Product Type" class="col-md-4 control-label">End Connection</label>
                                                            <div class="col-md-8 col-xs-11">
                                                                <select class="select2" name="product_mst_type" id="product_mst_type" title="Select Type Name" onchange="generate_product_name();">
                                                                    <?= get_mst_type($dbcon, $rel['product_mst_type']); ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="Product Type" class="col-md-4 control-label">Class</label>
                                                            <div class="col-md-8 col-xs-11">
                                                                <select class="select2" name="pro_class_mst" id="pro_class_mst" title="Select Type Name" onchange="generate_product_name();">
                                                                    <?= get_class_mst($dbcon, $rel['product_mst_type']); ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="Product Type" class="col-md-4 control-label">Style Name</label>
                                                            <div class="col-md-8 col-xs-11">
                                                                <select class="select2" name="product_impregnation" id="product_impregnation" title="Select Impregnation Name" onchange="generate_product_name();">
                                                                    <?= get_impregnation($dbcon, $rel['product_impregnation']); ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            <?php } ?>
                                            <?php if($getspecialConfiguration['power_drive']==1){
                                                $query_field = "select * from tbl_item_master_field where item_master_field_status=0 and company_id=".$_SESSION['company_id']." order by priority ASC";
                                                $res_field = $dbcon->query($query_field);
                                                $ro_cnt = brp_mysqli_num_rows($res_field);
                                                $field=1;$counter=1;
                                                while($row_field = brp_mysqli_fetch_array($res_field)){
                                                    $field_name = $row_field['item_master_field_db_name'];
                                                    if($field==1){
                                            ?>
                                                <div class="col-md-12 margin_row">  
                                                <?php }?>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label"><?=$row_field['item_master_field']?>*</label>
                                                        <div class="col-md-8 col-xs-11">
                                                           <select class="select2 dynamic_field" name="<?=$row_field['item_master_field_db_name']?>" id="field_id<?=$field?>" title="<?=$row_field['item_master_field']?>" onchange="generate_product_name();">
                                                                <option value="" data-pcode="">--CHOOSE <?=$row_field['item_master_field']?>--</option>
                                                                <?=get_field_value($dbcon,$rel_field[$field_name],$row_field['item_master_field_id'])?>
                                                           </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php if($ro_cnt == $field){?>
                                                </div>
                                                <?php }else{
                                                    if($counter==3){ 
                                                        $counter=0;
                                                
                                                    ?>
                                                    </div><div class="col-md-12 margin_row">
                                                <?php }}?>

                                            <?php $field++;$counter++;}}?>
                                            <input type="hidden" name="dynamic_field" id="dynamic_field" value="<?=$field-1?>">
                                            <div class="col-md-12 margin_row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="Product Type" class="col-md-4 control-label">Product Descrtiption*</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <input type="text" class="form-control" id="product_name" name="product_name" placeholder="Product Descrtiption" value="<?= htmlspecialchars(stripcslashes($rel['product_name'])) ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <label class="col-md-4 control-label">Branch *</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <select class="select2" name="branch_id" id="branch_id" required>
                                                                <?php $branch = isset($edit_branch_id) ? $edit_branch_id : (isset($branch_id) ? $branch_id : '1000'); ?>
                                                                <?= getBranchBox_new($dbcon, $branch, 'all'); ?>
                                                            </select>

                                                        </div>
                                                    </div>
                                                </div>
                                                 <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Product Type" class="col-md-4 control-label">Making Days</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" class="form-control" name="product_making_time" id="product_making_time" value="<?= $mode == 'Edit' ? $rel['product_making_time'] : 0 ?>" /> ( In Days..)
                                                </div>
                                            </div>
                                        </div>
                                                <!--<div class="col-md-4">-->

                                                <!--    <div class="form-group">-->
                                                <!--        <label for="Product Image" class="col-md-4 control-label">Product Image</label>-->
                                                <!--        <div class="col-md-8 col-xs-11">-->
                                                <!--            <input type="file" name="image_name" id="image_name" accept="image/*" />-->
                                                <!--            <span class="text-info"> NOTE : Image size 300 X 200 </span>-->
                                                <!--            <?php if ($rel['image_name'] != null) { ?>-->
                                                <!--                <a class="btn btn-xs btn-primary" title="View Image" data-toggle="tooltip" data-id="2" data-placement="top" href="javascript:void(0)" onclick="view_product_image('<?= $pro_id ?>')"><i class="fa fa-eye"></i></a>-->
                                                <!--            <?php } ?>-->
                                                <!--        </div>-->
                                                <!--    </div>-->
                                                <!--</div>-->
                                            </div>

                                            <div class="col-md-12 margin_row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="Product Type" class="col-md-4 control-label" style="white-space: nowrap;">Old Part Code</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <input type="text" class="form-control" id="product_alias_name" name="product_alias_name" placeholder="Old Part Code" value="<?= htmlspecialchars(stripcslashes($rel['product_alias_name'])) ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Drawing Number </label>
                                                        <div class="col-md-6 col-xs-10">
														
														<?php if ($mode == 'Edit') { ?>
														 <input type="text" class="form-control" id="drawing_id" name="drawing_id" placeholder="Drawing Number" value="<?= $rel['drawing_number'] ?>" readonly  />
                                                            
                                                        <?php } else { ?>
                                                            <select class="select2" name="drawing_id" id="drawing_id" onChange="get_revision_data(this.value)" title="SO No.">
                                                                <?= getdrawingnumber($dbcon, $rel['drawing_id']);?>
                                                            </select>
														<?php } ?>
															
															
                                                        </div>
                                                        <div class="col-md-2 col-xs-1">
                                                            <?php if ($mode != 'Edit') { ?>
															<a class="btn btn-primary" title="View Image" data-toggle="tooltip" data-id="2" data-placement="top" href="javascript:void(0)" onclick="add_drawing()"><i class="fa fa-plus"></i></a>
															<?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="Product Type" class="col-md-4 control-label">HSN Code</label>
                                                        <div class="col-md-6 col-xs-11">
                                                            <select class="select2" name="product_hsn" id="product_hsn" title="Select HSN Code" onchange="getGst(this.value);">
                                                                <?= get_hsn($dbcon, $rel['product_hsn']); ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2 col-xs-1">
                                                            <a class="btn btn-primary" title="Add HSN Code" data-toggle="tooltip" data-id="2" data-placement="top" href="javascript:void(0)" onclick="add_hsn()"><i class="fa fa-plus"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                               <!-- <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Revision </label>
                                                        <div class="col-md-6 col-xs-11">
                                                            <select class="select2" name="revision_id" id="revision_id" title="SO No." onchange="load_revision_image(this.value)">
                                                                <?= getrevision_validate($dbcon, $rel['revision_id'], $rel['drawing_id']); ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-1" id="r_image"></div>
                                                    </div>
                                                </div>-->

                                            </div>
                                            <div class="col-md-12 margin_row">
                                                
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="Product Type" class="col-md-4 control-label">Sale GST</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <input type="text" class="form-control" id="product_sale_gst" name="product_sale_gst" placeholder="Sale GST" value="<?= $rel['product_sale_gst'] ?>" readonly required />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="Product Type" class="col-md-4 control-label">Purchase GST</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <input type="text" class="form-control" id="product_purchase_gst" name="product_purchase_gst" placeholder="Purchase GST" value="<?= $rel['product_purchase_gst'] ?>" readonly required />
                                                        </div>
                                                    </div>
                                                </div>
                                                 <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="Cat No." class="col-md-4 control-label">Manufactur Name.</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <input type="text" class="form-control" id="manufactur_name" name="manufactur_name" placeholder="Manufactur Part Name" value="<?= $rel['manufactur_name'] ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                                
                                               
                                            <div class="col-md-12 margin_row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="Cat No." class="col-md-4 control-label">Manufactur Part Code.</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <input type="text" class="form-control" id="cat_no" name="cat_no" placeholder="Manufactur Part Code" value="<?= $rel['cat_no'] ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                            
                                                 <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="opening stock" class="col-md-4 control-label">Minimum Stock</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="number" name="product_min_stock" min="0" id="product_min_stock" class="form-control" placeholder="Minimum Stock" value="<?= $mode == 'Edit' ? $rel['product_min_stock'] : '' ?>" onkeypress="return isNumberKey(event)" onchange="add_decimal_weight(this);" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="opening stock" class="col-md-4 control-label">Maximum Stock</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="number" name="product_max_stock" min="0" id="product_max_stock" class="form-control" placeholder="Maximum Stock" value="<?= $mode == 'Edit' ? $rel['product_max_stock'] : '' ?>" onkeypress="return isNumberKey(event)" onchange="add_decimal_weight(this);" required />
                                                </div>
                                            </div>
                                        </div>
                                        
                                         </div>
                                         <div class="col-md-4">
                                             <div class="form-group">
                                                 <label for="Item Type" class="col-md-4 control-label">Material Center</label>
                                                 <div class="col-md-8 col-xs-11">
                                                     <select class="select2" id="product_mat_center" name="product_mat_center">
                                                         <option value="">--select Material Center--</option>
                                                         <?= get_all_godown($dbcon, $rel['product_mat_center'], ''); ?>
                                                     </select>
                                                 </div>
                                             </div>
                                         </div>

                                                 <?php
                                            $sel_iso_verify_yes = "";
                                            $sel_iso_verify_no = "";
                                             if($mode == "Edit" && $rel['iso_verify'] == 1){
                                                $sel_iso_verify_yes = "selected";
                                             }else{
                                                $sel_iso_verify_no = "selected";
                                             }
                                        ?>
                                       
                                                <?php if($getspecialConfiguration['power_drive']==1){ ?>
                                                    <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="ISO Verify" class="col-md-4 control-label">ISO Verify</label>
                                                        <div class="col-md-8 col-xs-11">
                                                             <select class="select2" id="iso_verify" name="iso_verify">
                                                                <option <?=$sel_iso_verify_no?> value="0">No</option>
                                                                <option value="1" <?=$sel_iso_verify_yes?>>Yes</option>
                                                             </select>
                                                        </div>
                                                    </div>
                                                </div>
                                               <?php } ?>
                                                <?php if ($getspecialConfiguration['smpl_permission'] == "1") { ?>
                                                     <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="smpl_size" class="col-md-4 control-label">Size</label>
                                                            <div class="col-md-8 col-xs-11">
                                                                <input type="text" class="form-control" id="smpl_size" name="smpl_size" placeholder="Size" value="<?= $rel['smpl_size'] ?>" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                     <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="smpl_material" class="col-md-4 control-label">Material</label>
                                                            <div class="col-md-8 col-xs-11">
                                                                <input type="text" class="form-control" id="smpl_material" name="smpl_material" placeholder="Material" value="<?= $rel['smpl_material'] ?>" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>

                                            <div class="col-md-12 margin_row" style="margin-top:25px !important;">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="Product Type" class="col-md-4 control-label">Production Unit</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <select class="select2" name="product_base_unit" id="product_base_unit" title="Select Unit" onchange="get_product_unit(this.value)" required <?= $disabled_u ?>>
                                                                <?php if ($mode == 'Edit') {
                                                                    echo getunit($dbcon, $rel['product_base_unit']);
                                                                } else {
                                                                    echo getunit($dbcon, 3);
                                                                } ?>
                                                            </select>
                                                            
                                                            <?php if ($mode == 'Edit') { ?>
                                                                <input type="hidden" name="product_base_unit" value="<?= $rel['product_base_unit'] ?>">
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="Product Type" class="col-md-4 control-label">Qty</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <input type="text" class="form-control" name="product_base_qty" id="product_base_qty" <?=$readonly_unit?> value="<?php if ($mode == 'Edit') {
                                                                                                                                                                echo $rel['product_base_qty'];
                                                                                                                                                            } else { ?> 1 <?php } ?>" onkeypress="return isNumberKey(event)" onchange="add_decimal_weight(this);" required <?= $readonly ?> />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="Product Type" class="col-md-4 control-label">Purchase Unit</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <select class="select2" name="product_conv_unit" id="product_conv_unit" title="Select Unit" required <?= $disabled_u ?>>
                                                                <?php if ($mode == 'Edit') {
                                                                    echo getunit($dbcon, $rel['product_conv_unit']);
                                                                } else {
                                                                    echo getunit($dbcon, 3);
                                                                } ?>
                                                            </select>
                                                        </div>
                                                        <?php if ($mode == 'Edit') { ?>
                                                            <input type="hidden" name="product_conv_unit" value="<?= $rel['product_conv_unit'] ?>">
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="Product Type" class="col-md-4 control-label">Qty</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <input type="text" class="form-control" name="product_conv_qty" id="product_conv_qty"  <?=$readonly_unit?> value="<?php if ($mode == 'Edit') {
                                                                                                                                                                echo $rel['product_conv_qty'];
                                                                                                                                                            } else { ?> 1 <?php } ?>" onkeypress="return isNumberKey(event)" onchange="add_decimal_weight(this);" required <?= $readonly ?> />
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="mode" id="mode" value="<?php if ($mode == 'Add') {
                                                                                                        echo "add";
                                                                                                    } else {
                                                                                                        echo "edit";
                                                                                                    } ?>" />
                                                <input type="hidden" name="eid_main" id="eid_main" value="<?php if ($mode == 'Edit') {echo $rel['product_id']; } ?>" />
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
                        <div class="col-xs-2">
                            <!-- required for floating -->
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs tabs-left">
                                <!--<li class="active"><a href="#tunit" data-toggle="tab" id="ltunit" >Unit Converter</a></li>-->
                                <li class="" style="display:none;"><a href="#tbopen" data-toggle="tab" id="ltbopen">Godown Opening</a></li>
                                <li class="active"><a href="#timages" data-toggle="tab" id="ltimages">Images</a></li>
                                <li><a href="#tdescription" data-toggle="tab" id="ltdescription">Additional Details</a></li>
                                <li><a href="#tpurchase" data-toggle="tab" id="ltpurchase">Purchase Party</a></li>
                                <li><a href="#tjobpurchase" data-toggle="tab" id="ltjobwork"> Jobwork Party</a></li>
                                <li><a href="#tprocess" data-toggle="tab" id="ltprocess">Process List</a></li>
                                <li><a href="#tparam" data-toggle="tab" id="ltparam">QC Parameter</a></li>
                                <li><a href="#tsetting" data-toggle="tab" id="ltreq">Product Setting</a></li>
                                <li><a href="#tscrap" data-toggle="tab" id="ltscrap">Scrap Details</a></li>
								<li><a href="#talternative" data-toggle="tab" id="ltalternative">Accessories Product</a></li>
                                <?php if ($getspecialConfiguration['solid_permission'] == 1) { ?>
								<li><a href="#tsolidplaning" data-toggle="tab" id="solidplaning">Solidedge Planning</a></li>
                                <?php } ?>
                                <li id="project_product" style="display: none;"><a href="#tprojectproduct" data-toggle="tab" id="ltprojectproduct">Project Product</a></li>
                              <!--  <li><a href="#tmake" data-toggle="tab" id="ltmake" style="display:block;">Make</a></li> -->
                                <?php if ($getspecialConfiguration['vipul_copper_permission'] == 1) { ?>
                                    <li><a href="#tdieitemlist" data-toggle="tab" id="ltdieitemlist">Die Allocation</a></li>
                                <?php } ?>
                                <!-- <li class="stagelist"><a href="#stageprocess" data-toggle="tab" id="ltprocess">Stage List</a></li> -->
                            </ul>
                        </div>
                        <div class="col-xs-9">
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <!-- <div class="tab-pane active" id="tunit" >
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
                                            <?php //=getunit($dbcon,0);
                                            ?>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control" name="utab_basic_qty" value="1" id="utab_basic_qty" onkeypress="return isNumberKey(event)"  /></td>
                                    <td>
                                        <select class="form-control" name="utab_basic_unit" id="utab_basic_unit">
                                            <option value="">--Select Unit--</option>
                                            <?php //=getunit($dbcon,0);
                                            ?>
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
                </div> -->
                                <div class="tab-pane" id="tbopen" style="display:none;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Godown Opening Stock</a></h3>
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
                                                $cnt = 1;
                                                $selb = $dbcon->query("select * from mst_godown where g_status=0");
                                                while ($rb = brp_mysqli_fetch_array($selb)) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $cnt; ?></td>
                                                        <td><?php echo $rb['gd_name']; ?></td>
                                                        <td>
                                                            <input type="text" class="form-control bstock" name="bstock[]" value="<?php echo get_stock_by_branch($dbcon, $rb['gd_id'], $rel['product_id'], "stock"); ?>" onkeypress="return isNumberKey(event);" onkeyup="total_stock_value_count()" />
                                                            <input type="hidden" class="form-control bid" name="bid[]" value="<?php echo $rb['gd_id']; ?>" />
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control bpriority" name="bpriority[]" value="<?php echo get_stock_by_branch($dbcon, $rb['gd_id'], $rel['product_id'], "priority"); ?>" onkeypress="return isNumberKey(event)" />
                                                        </td>
                                                    </tr>
                                                <?php $cnt++;
                                                } ?>
                                                <input type="hidden" name="branch_mode" id="branch_mode" value="add_branch_stock" />
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane active" id="timages">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Upload Product Images</a></h3>
                                        </div>
                                    </div>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>
                                                <input type="file" name="file[]" id="file" accept="image/*" multiple />

                                            </th>
                                            <th>
                                                <input type="button" name="" value="Upload" class="btn btn-info" onclick="add_product_image()" />
                                                <input type="hidden" name="img_mode" id="img_mode" value="add_product_image_temp" />
                                            </th>
                                        </tr>
                                    </table>
                                    <span id="uploaded_image"></span>
                                </div>
                                <div class="tab-pane" id="tdescription">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="Product Description" class="col-md-4 control-label">Additional Details</label>
                                                <div class="col-md-12 col-xs-11">
                                                    <textarea class="form-control" id="product_desc" name="product_desc" placeholder="Enter Additional Details"><?= $rel['product_desc'] ?></textarea>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div class="form-group">
                                                <label for="Product Specification" class="col-md-4 control-label">Specification</label>
												<div class="form-group">
																				<label class="col-md-4 control-label text-left">Choose Specification</label>
																				<div class="col-md-4">
																					<select class="select2 categojj" id="specification_id" name="specification[]" onchange="load_specification_content();" multiple data-placeholder="Choose Annexure">
																						<?=get_specification_types($dbcon,$rel['product_spec_id']);?>
																					</select>
																				</div>
																			</div>
                                                <div class="col-md-12 col-xs-11">
                                                    <textarea class="form-control" id="product_spec" name="product_spec" placeholder="Enter Product Specification"><?= $rel['product_spec'] ?></textarea>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div class="form-group" style="display:none">
                                                <div class="col-md-6">
                                                    <table class="table table-bordered">
                                                        <tr>
                                                            <th>
                                                                <input type="file" name="image[]" id="image" accept="image/*" multiple />
                                                            </th>
                                                            <th>
                                                                <input type="button" name="" value="Upload" class="btn btn-info" onclick="add_product_tempimage()" />
                                                                <input type="hidden" name="img_tempmode" id="img_tempmode" value="add_product_tempimage" />
                                                            </th>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <div id="pro_temp_images"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tpurchase">
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
                                                    <th>Rate Tolerance (%) *</th>
                                                    <th>Disc (%) *</th>
                                                    <th>Quatation No.</th>
                                                    <th>Qutation Date</th>
                                                    <th>Effictive Date</th>
                                                    <th>Valid Date</th>
                                                    <th>Rate</th>
                                                    <th>Action</th>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <select class="select2" name="party_id" id="party_id">
                                                            <?php echo getcust($dbcon, $id, $purchase_party_show, $flag = 0) ?>
                                                        </select>
                                                    </td>
                                                    <td><input id="rate_tolerance" name="rate_tolerance" type="number" class="form-control" title="Rate Tolerance" placeholder="Rate Tolerance"></td>
                                                    <td><input id="discount_percentage" name="discount_percentage" type="number" class="form-control" title="Discount Percentage" maxlength="2" placeholder="Discount Percentage"></td>
                                                    <td><input id="quotation_no" name="quotation_no" type="number" class="form-control" title="Date" value="" placeholder="Quotation No"></td>
                                                    <td><input id="quotation_date" name="quotation_date" type="text" class="form-control default-date-picker" title="Date" placeholder="Date"></td>
                                                    <td><input id="affected_date" name="affected_date" type="text" class="form-control default-date-picker" title="Date" placeholder="Effective Date"></td>
                                                    <td><input id="valid_date" name="valid_date" type="text" class="form-control default-date-picker" title="Date" placeholder="Valid Date"></td>
                                                    <td>
                                                        <input type="text" class="form-control" name="party_rate" id="party_rate" onkeypress="return isNumberKey(event)" onkeyup="return isNumberKey(event)" onchange="return isNumberKey(event)" placeholder="Rate" />
                                                    </td>
                                                    <td><input type="button" class="btn btn-primary" value="ADD" onclick="add_party_purchase()" id="add_party_btn" /></td>
                                                    <input type="hidden" id="edit_id_party" value="" />
                                                    <input type="hidden" id="eid_party" value="" />
                                                </tr>
                                            </table>
                                            <div id="table_party_purchase"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="talternative" >
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Accessories Product</a></h3>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 margin_row">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th>Accessories Product Name</th>
													<th>Qty</th>
													<td>Action</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input id="acc_product_id" name="acc_product_id" style="width:100%;" placeholder="Select Product" onchange="load_product_dtls_pop(this.value);get_hsn_pop(this.value);" />
														<br><label id="current_stock_pop" style="display: none;"></label><strong class="hsncode_pop" style="display:none;color:blue">HSN Code : <span id="hsncode_pop"></span></strong><br>
                                                    </td>
													 <td>
                                                        <input type="text" class="form-control" name="acc_product_qty" id="acc_product_qty" placeholder="QTY" />
														<strong class="unit_pop" style="display:none;color:blue"><span id="unit_pop"></span></strong>
                                                    </td>
													<td rowspan="2"><input type="button" class="btn btn-primary" value="ADD" onclick="add_accessories_product()" id="add_alternative_btn" /></td>
                                                    <input type="hidden" id="edit_id_accessories" value="" />
                                                    <input type="hidden" id="eid_accessories" value="" />
													</tr>
													<tr>
													<td colspan="2">
													 <div class="form-group">
														<label for="Product Description" class="col-md-4 control-label">Description</label>
														<div class="col-md-12 col-xs-11">
														<textarea class="form-control" id="acc_product_desc" name="acc_product_desc" placeholder="Enter Product Description"></textarea>
														</div>
													</div>
													</td>
													</tr>
													
                                            </table>
                                            <div id="table_accessories_product"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tsolidplaning" >
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Solidedge planing</a></h3>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 margin_row">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th>Printing</th>
													<th>
                                                        <select class="select2" name="printing_material" id="printing_material" >
                                                            <option>Select Product</option>
                                                            <?php echo load_all_product($dbcon, $where,$rel['printing_material']);?>
                                                            <?php //echo get_all_resource($dbcon) ?>
                                                        </select>
                                                       
                                                    </th>
													<td>
                                                        <select class="select2" name="printing_balty" id="printing_balty" >
                                                             <option>Select Balty</option>
                                                            <?php echo load_balty($dbcon, $where,$rel['printing_balty']);?>
                                                        </select> 
                                                    </td>
                                                    <td>
                                                        <select class="select2" name="printing_req" id="printing_req" >
                                                             <option value="1" <?php if ($rel['printing_req'] == 1) { ?> selected="selected" <?php }?> >Yes</option>
                                                             <option value="0" <?php if ($rel['printing_req'] == 0) { ?> selected="selected" <?php }?> >No</option>
                                                        </select> 
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Extrusion</th>
													<th>
                                                        <select class="select2" name="extrusion_material" id="extrusion_material" >
                                                            <option>Select Product</option>
                                                            <?php echo load_all_product($dbcon, $where,$rel['extrusion_material']);?>
                                                            <?php //echo get_all_resource($dbcon) ?>
                                                        </select>
                                                    </th>
													<td>
                                                        <select class="select2" name="extrusion_size" id="extrusion_size" >
                                                             <option>Select Size</option>
                                                            <?php echo load_size($dbcon, $where,$rel['extrusion_size']);?>
                                                        </select> 
                                                    </td>
                                                    <td>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Mixing</th>
													<th>
                                                        
                                                    </th>
													<td>
                                                        <select class="select2" name="mixing_batch_size" id="mixing_batch_size" >
                                                             <option>Select Batch Size</option>
                                                            <?php echo load_batch_size($dbcon, $where,$rel['mixing_batch_size']);?>
                                                        </select> 
                                                    </td>
                                                    <td>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tprojectproduct">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Project Product</a></h3>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="card">
                                            <ul class="nav nav-tabs" id="my_tab_id" role="tablist">
                                                <li role="presentation" id="tab1" class="active"><a href="#product-details" aria-controls="product-details" role="tab" data-toggle="tab">Product Details</a></li>
                                                <li role="presentation" id="tab2"><a href="#product-desc" aria-controls="product-desc" role="tab" data-toggle="tab">Description</a></li>
                                            </ul>

                                            <div class="tab-content">
                                                <!-- Remaks Tab Start -->
                                                <div role="tabpanel" class="tab-pane active" id="product-details">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <table cellspacing="10" style="border-collapse:inherit; table-layout: fixed;" id="product_list" class="display table table12 table-striped table-bordered">
                                                                <tr id="field">
                                                                    <th width="20%" class="text-center">Product Detail</th>
                                                                    <th width="8%" class="text-center">HSN Code</th>
                                                                    <th width="6%" class="text-center">Quantity</th>
                                                                    <th width="7%" class="text-center">Rate</th>
                                                                    <th width="5%" class="text-center"></th>
                                                                </tr>
                                                                <input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
                                                                <tr id="field1">
                                                                    <td data-label="PRODUCT DETAIL" style="vertical-align:top;">
                                                                        <input id="project_product_id" name="project_product_id" style="width:100%;" placeholder="Select Product" onchange="load_project_productdetail(this.value);"/>
                                                                    </td>   
                                                                    <td data-label="HSN CODE" style="vertical-align:top;">
                                                                        <select class="select2" name="product_project_hsn_code" id="product_project_hsn_code"  title="Select HSN Code">
                                                                            <?=get_hsn($dbcon,$rel['product_hsn_code']);?>
                                                                        </select>
                                                                    </td>
                                                                    <td data-label="QUANTITY" style="vertical-align:top;">
                                                                        <input type="number"  title="Enter Qty"  min="0" id="product_project_qty" name="product_project_qty"  class="form-control" />
                                                                    </td>
                                                                    <td data-label="RATE" style="vertical-align:top;">
                                                                        <input type="number"  title="Enter Rate" placeholder="Rate" min="0" id="product_project_rate" name="product_project_rate" class="form-control"/>
                                                                    </td>
                                                                    <td style="vertical-align:top;">
                                                                        <input type="button"  name="addrow" id="addrow" onClick="return add_project_field();"  class="btn btn-primary" value="Add"/>
                                                                    </td>
                                                                    <input type='hidden' name='edit_id_project' id='edit_id_project' value='' />
                                                                </tr>
                                                            </table>            
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="product-desc" >
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Description</label>
                                                                <div class="col-md-12">
                                                                    <textarea class="form-control" id="product_project_des" name="product_project_des" placeholder="Enter Product Description"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Specification</label>
                                                                <div class="col-md-12">
                                                                    <textarea class="form-control" id="product_project_spec" name="product_project_spec" placeholder="Enter Product Specification"></textarea> 
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="sale_project_productdata"></div>
                                </div>
                                <div class="tab-pane" id="tjobpurchase">
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
                                                            <?php echo get_all_process($dbcon, $id) ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="select2" name="job_party_id" id="job_party_id">
                                                            <?php echo getcust($dbcon, $id) ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="job_party_rate" id="job_party_rate" onkeypress="return isNumberKey(event)" />
                                                    </td>
                                                    <td><input type="button" class="btn btn-primary" value="ADD" style="" onclick="add_job_party_purchase()" id="add_job_party_btn" /></td>
                                                    <input type="hidden" id="edit_id_job_party" value="" />
                                                    <input type="hidden" id="eid_job_party" value="" />
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
                                                    <!-- <th>Unit</th> -->
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <select class="select2" name="qc_process_id" id="qc_process_id">
                                                            <?php // echo get_all_process($dbcon,$id) 
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="select2" name="param_id" id="param_id">
                                                            <?php echo get_all_parameter($dbcon, $id) ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="param_value" id="param_value" onkeyup="check_base_value(this.value)" />
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control numbersOnly" name="tolerance_plus" id="tolerance_plus" onkeyup="check_param_tolerance(this.value)" />
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control numbersOnly" name="tolerance_minus" id="tolerance_minus" onkeyup="check_param_tolerance(this.value)" />
                                                    </td>
                                                    <td><input type="button" class="btn btn-primary" value="ADD" style="" onclick="add_param_value()" id="add_param" /></td>
                                                    <input type="hidden" id="edit_id_param" value="" />
                                                </tr>
                                            </table>
                                            <div id="table_product_parameter"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tsetting">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Product Setting</a></h3>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-left:20px;">
                                        <div class="col-md-4 margin_row">
                                            <label class="container"><span class="margin_span">Process on Product</span>
                                                <input type="checkbox" name="product_setting_check[]" id="product_setting_check" value="process_product" class="product_process process_on_procduct" <?php if ($mode == 'Edit') {
                                                                                                                                                                                                            if (in_array("process_product", $check_array_setting)) {
                                                                                                                                                                                                                echo "checked";
                                                                                                                                                                                                            }
                                                                                                                                                                                                        } ?> onclick="return false;">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-md-4 margin_row">
                                            <label class="container"><span class="margin_span">QC For Product</span>
                                                <input type="checkbox" name="product_setting_check[]" id="product_setting_check" value="product_qc" class="product_process qc_on_procduct" <?php if ($mode == 'Edit') {
                                                                                                                                                                                                if (in_array("product_qc", $check_array_setting)) {
                                                                                                                                                                                                    echo "checked";
                                                                                                                                                                                                }
                                                                                                                                                                                            } ?> onclick="return false;">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-md-12" style="height:10px;"></div>
                                        <div class="col-md-4">
                                            <label class="container"><span class="margin_span">Tolerance For Product</span>
                                                <input type="checkbox" name="tolerance" id="tolerance" value="1" <?php if ($rel['tolerance'] == "1") {
                                                                                                                        echo "checked";
                                                                                                                    }  ?>>
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="container col-md-4">Minimum (%)</label>
                                            <div class="col-md-8">
                                                <input type="number" min="0" class="form-control " name="minimum_tolerance" id="minimum_tolerance" value="<?= $rel['minimum_tolerance'] ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="container col-md-4"> Maximum (%)</label>
                                            <div class="col-md-8">
                                                <input type="number" min="0" class="form-control " name="maximum_tolerance" id="maximum_tolerance" value="<?= $rel['maximum_tolerance'] ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="container"><span class="margin_span">Maintain stock balance</span>
                                                <input type="checkbox" name="enable_stockbalance" id="enable_stockbalance" <?php if ($rel['enable_stockbalance'] == "1") {
                                                                                                                                echo "checked";
                                                                                                                            }  ?>>
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="container"><span class="margin_span">Enable negative stock</span>
                                                <input type="checkbox" name="enable_negative_stock" id="enable_negative_stock" <?php if ($rel['enable_negative_stock'] == "1") {
                                                                                                                                    echo "checked";
                                                                                                                                }  ?>>
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- START JAYESH ADD NEW FIELDS FIELD  15-07-2021 -->
                                    <div class="col-md-4">

                                        &nbsp;
                                    </div>
                                    <div class="col-md-4">
                                        <label class="container col-md-4">Minimum Value</label>
                                        <div class="col-md-8">
                                            <input type="number" min="0" class="form-control " name="minimum_tolerance_value" id="minimum_tolerance_value" value="<?= $rel['minimum_tolerance_value'] ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="container col-md-4"> Maximum Value</label>
                                        <div class="col-md-8">
                                            <input type="number" min="0" class="form-control " name="maximum_tolerance_value" id="maximum_tolerance_value" value="<?= $rel['maximum_tolerance_value'] ?>" />
                                        </div>
                                    </div>

                                    <!-- start jayesh (15-7) -->
                                    <div class="row">
                                        <div class="col-md-12">
                                            <hr style="width:100%;border-bottom: 1px solid ; color: #ccc;" />
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
                                                    <select class=" select2" name="product_specification" id="product_specification" <?= $disabled ?>>
                                                        <?= get_product_specification($dbcon, $rel['product_specification']); ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="opening stock" class="col-md-4 control-label">Product Valuation</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" class="form-control" name="product_opening_valuation" id="product_opening_valuation" value="<?= $rel['product_opening_valuation'] ?>" onkeypress="return isNumberKey(event)" onchange="add_decimal(this);" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="opening stock" class="col-md-4 control-label">Product Barcode</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" class="form-control" name="product_barcode" id="product_barcode" value="<?= $rel['product_barcode'] ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 margin_row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Net Weight </label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" class="form-control" name="product_net_weight" id="product_net_weight" value="<?= $rel['product_net_weight'] ?>" onkeypress="return isNumberKey(event)" onchange="add_decimal_weight(this);" />
                                                </div>
                                            </div>
                                        </div>

                                        <!--<div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Product Type" class="col-md-4 control-label">Making Time</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" class="form-control" name="product_making_time" id="product_making_time" value="<?= $mode == 'Edit' ? $rel['product_making_time'] : 0 ?>" /> ( In Minute..)
                                                </div>
                                            </div>
                                        </div>-->

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Product Type" class="col-md-4 control-label">Po Lead Time</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" class="form-control" name="product_lead_time" id="product_lead_time" value="<?= $mode == 'Edit' ? $rel['product_lead_time'] : 0 ?>" /> ( In <?php $company_config = getCompanyConfiguration($dbcon, $id = false);
                                                                                                                                                                                                            if ($company_config['resource_time'] == '0') {
                                                                                                                                                                                                                echo  $resource_time = "Mintue";
                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                echo $resource_time = "Days";
                                                                                                                                                                                                            } ?> ..)
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($getspecialConfiguration['elcon_permission'] == 1) { ?>
                                        <div class="col-md-12 margin_row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Base Weight </label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input type="text" class="form-control" name="base_weight" id="base_weight" value="<?= $rel['base_weight'] ?>" onkeypress="return isNumberKey(event)" onchange="add_decimal_weight(this);" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="Product Type" class="col-md-4 control-label">Conv. Weight</label>
                                                    <div class="col-md-8 col-xs-11">
                                                        <input type="text" class="form-control" name="conv_weight" id="conv_weight" value="<?= $rel['conv_weight'] ?>" nkeypress="return isNumberKey(event)" onchange="add_decimal_weight(this);" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <div class="col-md-12 margin_row">

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Product Type" class="col-md-4 control-label">GST Type</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <select class="select2" name="product_gst" id="product_gst" title="Select Unit" required>
                                                        <option value="">--Select GST Type--</option>
                                                        <option value="including" <?php if ($rel['product_gst'] == 'including') {
                                                                                        echo "selected";
                                                                                    } ?>>Including</option>
                                                        <option value="excluding" <?php if ($mode == 'Edit') {
                                                                                        if ($rel['product_gst'] == 'excluding') {
                                                                                            echo "selected";
                                                                                        }
                                                                                    } else {
                                                                                        echo "selected";
                                                                                    } ?>>Excluding</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Product Type" class="col-md-4 control-label">Sale Rate</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="number" min="0" class="form-control" id="product_sale_rate" name="product_sale_rate" placeholder="Product Sale Rate" value="<?= $rel['product_sale_rate'] ?>" onkeypress="return isNumberKey(event)" onchange="add_decimal(this);" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Product Type" class="col-md-4 control-label">Purchase Rate</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="number" min="0" class="form-control" id="product_purchase_rate" name="product_purchase_rate" placeholder="Product Purchase Rate" value="<?= $rel['product_purchase_rate'] ?>" onkeypress="return isNumberKey(event)" onchange="add_decimal(this);" />
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-md-12 margin_row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Weight" class="col-md-4 control-label">Weight</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" name="weight" min="0" id="weight" class="form-control" placeholder="Weight" value="<?= $mode == 'Edit' ? $rel['weight'] : '' ?>" onchange="add_decimal_weight(this);" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4" style="display: none;">
                                            <div class="form-group">
                                                <label for="opening stock" class="col-md-4 control-label">Opening Stock</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="number" name="product_opening" min="0" id="product_opening" class="form-control" placeholder="Opening Stock" value="<?= $mode == 'Edit' ? $rel['product_opening'] : 0 ?>" required readonly />
                                                </div>
                                            </div>
                                        </div>
                                       

                                    </div>
                                    <div class="col-md-12 margin_row">

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Minimum Order" class="col-md-4 control-label">Minimum Order</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="number" name="product_min_order" min="0" id="product_min_order" class="form-control" placeholder="Minimum Order" value="<?= $mode == 'Edit' ? $rel['product_min_order'] : '' ?>" onkeypress="return isNumberKey(event)" onchange="add_decimal(this);" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Maximum Order" class="col-md-4 control-label">Maximum Order</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="number" name="product_max_order" min="0" id="product_max_order" class="form-control" placeholder="Maximum Order" value="<?= $mode == 'Edit' ? $rel['product_max_order'] : '' ?>" onkeypress="return isNumberKey(event)" onchange="add_decimal(this);" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="is_grn" class="col-md-4 control-label">GRN Required?</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <select class="select2" id="is_grn" name="is_grn">
                                                        <?php echo get_common_boolean_value($dbcon, $rel['is_grn']); ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Reorder Quantity" class="col-md-4 control-label">Reorder Quantity</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" name="reorder_qty" min="0" id="reorder_qty" class="form-control" placeholder="Reorder Quantity" value="<?= $mode == 'Edit' ? $rel['reorder_qty'] : '' ?>" onkeypress="return isNumberKey(event)" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Selft Life Days" class="col-md-4 control-label">Self Life Days</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="number" name="self_life_days" min="0" id="self_life_days" class="form-control" placeholder="Self Life Days" value="<?= $mode == 'Edit' ? $rel['self_life_days'] : '' ?>" onkeypress="return isNumberKey(event)" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Selft Life Days" class="col-md-4 control-label">Rack No.</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" name="rack_no" min="0" id="rack_no" class="form-control" placeholder="Rack No." value="<?= $mode == 'Edit' ? $rel['rack_no'] : '' ?>" />
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="col-md-12 margin_row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Warrenty Period" class="col-md-4 control-label">warranty Period</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="number" name="warrenty_period" min="0" id="warrenty_period" class="form-control" placeholder="warranty Period Days" value="<?= $mode == 'Edit' ? $rel['warrenty_period'] : '' ?>" onkeypress="return isNumberKey(event)" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Model No" class="col-md-4 control-label">Model No</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" name="model_no" min="0" id="model_no" class="form-control" placeholder="Model No" value="<?= $mode == 'Edit' ? $rel['model_no'] : '' ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Item Type" class="col-md-4 control-label">Item Type</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <select class="select2" id="item_type" name="item_type">
                                                        <?php echo get_product_item_type_company($dbcon, $rel['item_type'], ''); ?>
                                                    </select>

                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-md-12 margin_row">
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="is_grn" class="col-md-4 control-label">Stock Count?</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <select class="select2" id="product_stock_count" name="product_stock_count">
                                                        <?php echo get_common_boolean_value($dbcon, $rel['product_stock_count']); ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                            $sel_bom_req_yes = "";
                                            $sel_bom_req_no = "";
                                             if($mode == "Edit" && $rel['bom_required'] == 1){
                                                $sel_bom_req_yes = "selected";
                                             }else{
                                                $sel_bom_req_no = "selected";
                                             }
                                        ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="is_grn" class="col-md-4 control-label">Bom Required?</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <select class="select2" id="bom_required" name="bom_required">
                                                        <option value="1" <?=$sel_bom_req_yes?>>Yes</option>
                                                        <option <?=$sel_bom_req_no?> value="0">No</option>
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
                                                        <?php echo get_product_item_status_company($dbcon, $rel['item_status'], ''); ?>
                                                    </select>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 show_hide_fields">
                                            <div class="form-group">
                                                <label for="Item Date" class="col-md-4 control-label">Item Date</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input id="item_status_date" name="item_status_date" type="text" class="form-control error default-date-picker required valid" title="Item Date" placeholder="Item Date" value="<?= $mode == 'Edit' ? $rel['item_status_date'] : '' ?>" placeholder="Item Date">

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 show_hide_fields">
                                            <div class="form-group">
                                                <label for="Reason" class="col-md-4 control-label">Reason</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <!-- <select class="select2" id="item_status_reason" name="item_status_reason" >
                                          <?php echo get_product_item_type_reason_company($dbcon, $rel['item_status_reason'], ''); ?>
                                      </select>-->
                                                    <textarea id="item_status_reason" name="item_status_reason"><?= $mode == 'Edit' ? $rel['item_status_reason'] : '' ?></textarea>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="batch_wise_stock_manage" class="col-md-4 control-label">Batch Wise Stock Manage?</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <select class="select2" id="batch_wise_stock_manage" name="batch_wise_stock_manage">

                                                        <option <?php if ($rel['batch_wise_stock_manage'] == 0) { ?> selected="selected" <?php } else { ?> selected="selected" <?php } ?> value="0">No</option>
                                                        <option value="1" <?php if ($rel['batch_wise_stock_manage'] == 1) { ?> selected="selected" <?php } else { ?> <?php } ?>>Yes</option>

                                                    </select>
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
                                                    <th>Time (In Min.)</th>
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

                                                            <?php echo get_all_process($dbcon, $id) ?>

                                                        </select>
                                                    </td>
                                                    <td>
                                                        <!-- <input type="number" class="form-control" name="process_priority" id="process_priority" /> -->
                                                        <label for="process_priority" class="form-control process_priority_label"></label>
                                                        <input type="hidden" class="form-control process_priority" name="process_priority" id="process_priority" />
                                                    </td>
                                                    <td>
                                                        <!-- <select class="form-control" name="process_type" id="process_type" onChange="manage_resource(this.value);">  -->
                                                    <select class="form-control" name="process_type" id="process_type">
                                                            <option value="">--Select Process Type--</option>
                                                            <option value="1">Inhouse</option>
                                                            <option value="2">Outside</option>
                                                        </select>
                                                    </td>
                                                    <td class="processRate_label_manage">
                                                        <input type="number" class="form-control numbersOnly" name="process_rate" id="process_rate" />
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control digitOnly" name="process_time" id="process_time" />
                                                    </td>
                                                    <!-- <td>
					                  <input type="text" class="form-control" name="process_opening" id="process_opening" />
                               </td> -->
                                                    <td class="resource_label_manage">
                                                        <select class="select2" name="resource_id" id="resource_id">
                                                            <?php echo get_all_resource($dbcon) ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control numbersOnly" name="process_loss" value="" id="process_loss" />
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control numbersOnly" name="process_scrap_tolerance_plus" id="process_scrap_tolerance_plus" />
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control numbersOnly" name="process_scrap_tolerance_minus" id="process_scrap_tolerance_minus" />
                                                    </td>
                                                    <td><input type="button" class="btn btn-primary" value="ADD" style="" onclick="add_process_value()" id="add_process" /></td>
                                                    <input type="hidden" id="edit_id_process" value="" />
                                                </tr>
                                            </table>
                                            <div id="table_product_process"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tscrap">
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
                                                    <input type="number" class="form-control" name="material_issue_weight" id="material_issue_weight" onkeypress="return isNumberKey(event)" value="<?= $rel['material_issue_weight'] ?>" onchange="add_decimal_weight(this);" />
                                                </div>
                                            </div>
                                            <div class="col-md-12" style="margin-top: 15px;">
                                                <div class="col-md-2"> <strong> Scrap Code </strong></div>
                                                <div class="col-md-6">
                                                    <select class="select2" name="product_scrap_id" id="product_scrap_id">
                                                        <?= getScrapCode($dbcon, $rel['product_scrap_id']) ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12" style="margin-top: 15px;">
                                                <div class="col-md-2"> <strong> Scrap Desc. </strong></div>
                                                <div class="col-md-6">
                                                    <textarea class="form-control" id="scrap_desc" name="scrap_desc" placeholder="Enter Scrap Description"><?= $rel['scrap_desc'] ?></textarea>
                                                </div>
                                            </div>

                                            <div class="col-md-12" style="margin-top: 15px;">
                                                <div class="col-md-2"> <strong> Scrap Qty. </strong> </div>
                                                <div class="col-md-6">
                                                    <input type="number" class="form-control" name="scrap_qty" id="scrap_qty" value="<?= $rel['scrap_qty'] ?>" />
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
                                 <?php  //echo getScrapCode($dbcon,$id) 
                                    ?>
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
                               <!-- <div class="tab-pane" id="tmake" style="display:block;">
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
                                                            <?php echo getmake($dbcon, $id) ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="select2" name="make_number_id" id="make_number_id">
                                                            <?php echo getmakenumber($dbcon, $id) ?>
                                                        </select>
                                                        <br><br>
                                                        <input type="text" class="form-control" name="make_value" id="make_value" onkeypress="" placeholder="Enter Make Value" />
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" name="make_stock" id="make_stock" onkeypress="return isNumberKey(event)" />
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" name="make_rate" id="make_rate" onkeypress="return isNumberKey(event)" />
                                                    </td>
                                                    <td><input type="button" class="btn btn-primary" value="ADD" style="" onclick="add_make()" id="addmake_btn" /></td>
                                                    <input type="hidden" id="edit_id_make" value="" />
                                                    <input type="hidden" id="eid_make" value="" />
                                                </tr>
                                            </table>
                                            <div id="table_make_data"></div>
                                        </div>
                                    </div>
                                </div>-->
                                <?php if ($getspecialConfiguration['vipul_copper_permission'] == 1) { ?>
                                    <div class="tab-pane" id="tdieitemlist">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Die Allocation</a></h3>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 margin_row">
                                                <table class="table table-bordered" style="margin-right: auto;margin-left: auto;width: 80%">
                                                    <tr>
                                                        <th width="20%">Die Allocation Name</th>
                                                        <th width="20%">Customer Name</th>
                                                        <td width="10%"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <select class="select2" name="die_product_id" id="die_product_id">
                                                                <?php echo getdieallocation($dbcon, $die_product_id) ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="select2" name="die_customer_id" id="die_customer_id">
                                                                <?php
                                                                $where = "and l_group IN ('37','38')";
                                                                ?>
                                                                <?= get_ledger($dbcon, '', $where); ?>
                                                            </select>
                                                        </td>
                                                        <td><input type="button" class="btn btn-primary" value="ADD" onclick="add_die_allocation()" id="add_die_allocation_btn" /></td>
                                                        <input type="hidden" id="edit_id_die_allocation" value="" />
                                                        <input type="hidden" id="eid_die_allocation" value="" />
                                                    </tr>
                                                </table>
                                                <div id="table_die_allocation_data"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
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
					                  <?php echo getstages($dbcon) ?>
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
                            <input type='hidden' name='pid' id='pid' value='<?php if ($mode == 'Edit') {
                                                                                echo $rel['product_id'];
                                                                            } else {
                                                                                echo "0";
                                                                            } ?>' />
                            <input type='hidden' name='product_model' id='product_model' value='' />
                            <!-- <?php if ($mode == 'Edit') { ?> <a onclick="saveandcopy('<?php echo $rel['product_id']; ?>');" type="button" class="btn btn-shadow btn-success">Save & Copy</a> <?php } ?> -->
                            <button type="submit" class="btn btn-shadow btn-success"><?php if ($mode == 'Edit') {
                                                                                            echo "Save";
                                                                                        } else {
                                                                                            echo "Save & New";
                                                                                        } ?></button>
                            <a href="<?= ROOT . ADMINISTRATION_ROOT . 'product_list' ?>" type="button" class="btn btn-danger">Cancel</a>
                            <div class="col-md-3"></div>
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
            <?php include_once($include . 'footer.php'); ?>
            <!--footer end-->
        </section>
        <!-- Modal -->
        </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <style>
            .show_hide_fields {
                display: none;
            }
        </style>
        <?php include_once($include1 . 'view_revision_image.php'); ?>
        <?php include_once($include1 . 'add_productinpro.php'); ?>
        <?php include_once($include1 . 'add_drawing.php'); ?>
        <?php include_once($include1 . 'add_hsn.php'); ?>
        <!-- js placed at the end of the document so the pages load faster -->
        <?php include_once($include . 'include_js_file.php'); ?>
        <script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/product_mst.js?<?php echo time(); ?>"></script>
        <script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/hsn_master.js?<?php echo time(); ?>"></script>
        <script type='text/javascript' src='<?= ROOT ?>assets/sweetalert2/sweetalert2.all.min.js'></script>
        <script>
            	$(".select2").select2({
				width: '100%',
				//minimumInputLength: 3
			});
            $("form :input").attr("autocomplete", "off");


            CKEDITOR.replace('product_desc', {
                enterMode: CKEDITOR.ENTER_BR
            });
            CKEDITOR.replace('product_project_des', {
                enterMode: CKEDITOR.ENTER_BR
            });
            CKEDITOR.replace('product_spec', {
                enterMode: CKEDITOR.ENTER_BR
            });
            CKEDITOR.replace('product_project_spec', {
                enterMode: CKEDITOR.ENTER_BR
            });
			CKEDITOR.replace('acc_product_desc', {
                enterMode: CKEDITOR.ENTER_BR
            });
            $(".select2").select2({
                width: '100%'
            });
            $('.default-date-picker').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            }).datepicker("setDate", 'now');
            var tableToExcel = (function() {
                var uri = 'data:application/vnd.ms-excel;base64,',
                    template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
                    base64 = function(s) {
                        return window.btoa(unescape(encodeURIComponent(s)))
                    },
                    format = function(s, c) {
                        return s.replace(/{(\w+)}/g, function(m, p) {
                            return c[p];
                        })
                    }
                return function(table, name) {
                    if (!table.nodeType) table = document.getElementById(table)
                    var ctx = {
                        worksheet: name || 'Worksheet',
                        table: table.innerHTML
                    }
                    window.location.href = uri + base64(format(template, ctx))
                }
            })()
            /*START JAYESH Item Search filed hide show 21-07-2021*/

            window.onkeyup = function(e) {
                var event = e.which || e.keyCode || 0; // .which with fallback
                if (event == 27) { // ESC Key
                    window.location = root_domain + administration_domain + 'product_list'; // Navigate to URL
                }
            }

            function saveandcopy(id) {
                window.location = root_domain + administration_domain + 'product_clone/' + id;
                return false;
            }

            function getitemstatus(id) {
                if (id == '3' || id == '2') {
                    $('.show_hide_fields').css('display', 'block');
                    return false;
                } else {
                    $('.show_hide_fields').css('display', 'none');
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
        echo "<script>pro_status(" . $rel['product_type'] . ");</script>";
        echo "<script>getitemstatus(" . $rel['item_status'] . ");</script>";
        /*echo "<script>readonlyform();</script>";*/
        if ($mode == "Edit") {
            echo "<script>load_revision_image(" . $rel['revision_id'] . ");</script>";
			}
        ?>
    </body>

    </html>