<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="metR Technology">
<meta name="keyword" content="">
<link rel="shortcut icon" href="<?= ROOT ?>img/favicon.html">
<title><?= TITLE ?></title>
<!-- Bootstrap core CSS -->
<style>
.cke_notification_warning {
    display: none !important;
}
</style>
<link href="<?= ROOT ?>css/bootstrap.min.css?<?= time() ?>" rel="stylesheet">

<!--<link href="<?= ROOT ?>css/bootstrap-reset.css" rel="stylesheet">-->
<!--external css-->
<link href="<?= ROOT ?>assets/font-awesome/css/font-awesome.css?<?= time() ?>" rel="stylesheet"/>
<link href="<?= ROOT ?>assets/jquery-easy-pie-chart/jquery.easy-pie-chart.css" rel="stylesheet" type="text/css"
      media="screen"/>
<link rel="stylesheet" href="<?= ROOT ?>css/owl.carousel.css" type="text/css">
<!--right slidebar-->
<link href="<?= ROOT ?>css/slidebars.css" rel="stylesheet">
<link href="<?= ROOT ?>assets/advanced-datatable/media/css/demo_table.css" rel="stylesheet"/>
<link rel="stylesheet" href="<?= ROOT ?>assets/data-tables/DT_bootstrap.css"/>
<!-- Custom styles for this template -->
<link href="<?= ROOT ?>css/style.css?<?= time() ?>" type="text/css" rel="stylesheet">


<link href="<?= ROOT ?>css/fonts.css" rel="stylesheet">
<link href="<?= ROOT ?>css/style-responsive.css?<?= time() ?>" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="<?= ROOT ?>css/jquery.steps.css" />
<link href="<?= ROOT ?>assets/toastr-master/toastr.css" rel="stylesheet" type="text/css"/>
<!--For Multiselect -->
<link rel="stylesheet" type="text/css" href="<?= ROOT ?>assets/jquery-multi-select/css/multi-select.css"/>
<!--for Gallary-->
<link href="<?= ROOT ?>assets/fancybox/source/jquery.fancybox.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="<?= ROOT ?>css/gallery.css"/>
<link href="<?= ROOT ?>js/jquery.select2/select2.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="<?= ROOT ?>assets/bootstrap-datepicker/css/datepicker.css"/>


<link rel="stylesheet" type="text/css" media="all" href="<?= ROOT ?>css/daterangepicker.css"/>
<link rel="stylesheet" type="text/css" media="all" href="<?= ROOT ?>css/bootstrap.vertical-tabs.css"/>
<link rel="stylesheet" type="text/css" media="all" href="<?= ROOT ?>css/bootstrap-clockpicker.min.css"/>

<link rel="stylesheet" type="text/css" href="<?= ROOT ?>assets/nestable/jquery.nestable.css"/>

<link rel="stylesheet" type="text/css" href="<?= ROOT ?>assets/bootstrap-datetimepicker/css/datetimepicker.css"/>

<!-- <link rel="stylesheet" type="text/css" href="<?= ROOT ?>css/bootstrap-datetimepicker-standalone.min.css" />-->

<link href="<?= ROOT ?>css/summernote.css" rel="stylesheet">
<script type='text/javascript' src='<?= ROOT ?>js/jquery-2.1.0.js'></script>
<script type='text/javascript' src="<?= ROOT ?>js/jqBarGraph.1.1.js"></script>
<script src="<?= ROOT ?>assets/morris.js-0.4.3/morris.min.js" type="text/javascript"></script>
<script src="<?= ROOT ?>assets/morris.js-0.4.3/raphael-min.js" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="<?= ROOT ?>css/select2.min.css"/>
<link rel="stylesheet" type="text/css" href="<?= ROOT ?>css/select2-bootstrap.min.css"/>

<script>
    var root_domain = '<?php echo DOMAIN;?>';
	
	//Purchase Domain Added By Maulik 
    var purchase_domain = '<?php echo PURCHASE_ROOT;?>';
    var dispatch_domain = '<?php echo DISPATCH_ROOT;?>';
    var import_domain = '<?php echo IMPORT_ROOT?>';
	//Purchase Domain End By Maulik 
	
	//administration domain
	var administration_domain = '<?php echo ADMINISTRATION_ROOT; ?>';
	//Finance Domain
    var finance_root_domain = '<?php echo FINANCE_ROOT; ?>';
    //Service Change
    var service_domain = '<?php echo SERVICE_ROOT;?>';
    // crm domain
    var crm_domain = '<?php echo CRM_ROOT;?>';
    // hrms domain
    var hrms_domain = '<?php echo HRMS_ROOT;?>';
	
	 // production domail
    var production_domain = '<?php echo PRODUCTION_ROOT; ?>';

    // production domail
    var inventory_domain = '<?php echo INVENTORY_ROOT; ?>';
    var report_domain = '<?php echo REPORT_ROOT; ?>';
    var maintenance_domain = '<?php echo MAINTENANCE_ROOT; ?>';
	
    // support domain
    
	var support_domain = '<?php echo SUPPORT_ROOT;?>';
    var support_url = '<?php echo SUPPORT_URL;?>';

    var print_root_domain = '<?php echo PRINT_ROOT; ?>';
	var currency_id = '<?php echo @$_SESSION['currency_id']; ?>';
    var set_session = '<?php echo (isset($_SESSION['LOGGED_IN']) && $_SESSION['LOGGED_IN'] == true && $_SESSION['domain'] == SUPPORT_URL) ? 'true' : 'false'; ?>';
    var is_session = (set_session == 'true');

   
	

    ///Maulik Code Start////
    <?
        //var_dump($com_con_setting);
        if(!empty($_SESSION['company_id'])){
        $com_con_setting = getspecialConfiguration($dbcon);
        $companyConfig=getCompanyConfiguration($dbcon);
    ?>

    var hermattic_permission    = "<?=$com_con_setting['hermattic_permission']?>";
    var elcon_permission        = "<?=$com_con_setting['elcon_permission']?>";
    var maruti_permission       = "<?=$com_con_setting['maruti_permission']?>";
    var rb_auto_permission      = "<?=$com_con_setting['rb_auto_permission']?>";
    var umaboy_permission       = "<?=$com_con_setting['umaboy_permission']?>";
    var oilfield_permission     = "<?=$com_con_setting['oilfield_permission']?>";
    var jr_fiber_glass_permission= "<?=$com_con_setting['jr_fiber_glass_permission']?>"; 
    var vipul_copper_permission = "<?=$com_con_setting['vipul_copper_permission']?>";
    var filter_concept_permission= "<?=$com_con_setting['filter_concept_permission']?>";
    var atlas_permission        = "<?=$com_con_setting['atlas_permission']?>";
    var smpl_permission         = "<?=$com_con_setting['smpl_permission']?>";
    var durva_permission        = "<?=$com_con_setting['durva_permission']?>";
    var aeon_permission         = "<?=$com_con_setting['aeon_permission']?>";
    var reciclar                = "<?=$com_con_setting['reciclar']?>";
    var comp_config_data        = '<?=json_encode($companyConfig);?>';
    var comp_config             = jQuery.parseJSON(comp_config_data);
    //console.log(comp_config.cat_wise_product_load);
    <?
        }
    ?>
    ///Maulik Code End////
</script>