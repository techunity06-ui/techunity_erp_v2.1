<?php

if (empty($_SESSION['start'])) {
    $start_date = date('1-m-Y');
    $end_date = date("d-m-Y");
} else {
    $start_date = $_SESSION['start'];
    $end_date = $_SESSION['end'];
}

$companyConfiguration = getCompanyConfiguration($dbcon);
$crm_user_type = $companyConfiguration['crm_user_type'];
if ($_SESSION['user_type'] == 2) {
    $user_ids = get_users_typewise($dbcon, "", " AND user_type IN (" . $crm_user_type . ")", true);
} else {
    $user_id = check_user_chein($dbcon, $_SESSION['user_id'], 1);
    $user_ids = get_user_report($dbcon, $user_id, true);
}
if ($companyConfiguration['forecast_calculation'] == 1) {
    $calcu = "Quotation Wise";
} else if ($companyConfiguration['forecast_calculation'] == 2) {
    $calcu = "Sales Order Wise";
} else if ($companyConfiguration['forecast_calculation'] == 3) {
    $calcu = "Invoice Wise";
}
?>
<div class="">
    <div class="col-lg-12">
        <section class="panel1">
            <header class="panel-heading">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label col-md-1">Choose Date</label>
                        <div class="col-md-2">
                            <div class="input-group date form_datetime-component">
                                <input type="hidden" id="from_date" value="<?= $start_date ?>">
                                <input type="hidden" id="to_date" value="<?= $end_date ?>">
                                <input type="text" id="rep_date" onChange="load_counts();" class="form-control datepikerdemo" value="">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="icons icons12">
                        <div class="icon1 terques">
                            <i class="fa fa-user" aria-hidden="true"></i>
                        </div>
                        <div class="">
                            <h1 class="count" id="business_achieved_counts">0</h1>
                            <p style="color: #4c4e4e;">Business Achieved <?= $calcu ?></p>
                        </div>
                    </div>
                    <div class="icons icons12">
                        <div class="icon1 pink">
                            <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                        </div>
                        <div class="">
                            <h1 class="count" id="pending_quotation_counts">0</h1>
                            <p style="color: #4c4e4e;">Pending Quotation</p>
                        </div>
                    </div>
                    <div class="icons icons12 inq-list-stage-box">
                        <a href="javascript:void(0);" id="inquiry_lost_lnk" title="Inquiries Lost" style="color: #4c4e4e;">
                            <div class="icon1 success">
                                <i class="fa fa-bar-chart-o" aria-hidden="true"></i>
                            </div>
                            <div class="">
                                <h1 class="count" id="lost_opportunity_counts">0</h1>
                                <p style="color: #4c4e4e;">&nbsp; &nbsp; Inquiries Lost &nbsp; &nbsp;&nbsp;&nbsp;&nbsp; </p>
                            </div>
                        </a>
                    </div>
                    <?php$qry = "select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_id != '" . GENERAL_TASK_TYPE . "' and mcd_cat_id=7";
                    $rs_state = $dbcon->query($qry);
                    while ($row = brp_mysqli_fetch_assoc($rs_state)) { ?>
                        <div class="icons icons12 inq-list-stage-box">
                            <a href="javascript:void(0);" class="inquiry_sales_stage_lnk" data-param="<?= $row['mcd_id'] ?>" title="Inquiries Lost" style="color: #4c4e4e;">
                                <div class="icon1 mustard">
                                    <i class="fa fa-book" aria-hidden="true"></i>
                                </div>
                                <div class="">
                                    <h1 class="count" id="hot_leads_counts_<?= $row['mcd_id'] ?>">0</h1>
                                    <p style="color: #4c4e4e;"><?= $row['mcd_name'] ?> Leads On Hand </p>
                                </div>
                            </a>
                        </div>
                    <?php} ?>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-12">
        <section class="panel">
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-4" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="closed_won_sales_start_date" name="closed_won_sales_start_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="load_closed_won_sales();">
                                        </div>
                                    </div>
                                    <div class="col-md-4" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="closed_won_sales_end_date" name="closed_won_sales_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="load_closed_won_sales();">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="closed_won_sales_user_id" id="closed_won_sales_user_id" onchange="load_closed_won_sales();">
                                                <?= $user_ids; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row chart-report-title">
                                            <div class="col-md-4">
                                                <h3><strong>Won Sales</strong></h3>
                                            </div>
                                            <div class="col-md-4">
                                                <h3><strong>Lost Sales</strong></h3>
                                            </div>
                                            <div class="col-md-4">
                                                <h3><strong>Inquiry Sales</strong></h3>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div id="won_sales_container" style="height: 300px; width: 100%;"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div id="lost_sales_container" style="height: 300px; width: 100%;"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <div id="pending_sales_container" style="height: 300px; width: 100%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-6">
        <section class="panel">
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="lead_by_product_start_date" name="lead_by_product_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="load_lead_by_product();">
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="lead_by_product_end_date" name="lead_by_product_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="load_lead_by_product();">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="lead_by_product_user_id" id="lead_by_product_user_id" onchange="load_lead_by_product();">
                                                <?= $user_ids; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Category</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="lead_by_product_category_id" id="lead_by_product_category_id" onchange="load_lead_by_product();">
                                                <?= get_all_category($dbcon, '0'); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 chart-report-title">
                                        <h3><strong>Lead By Product</strong></h3>
                                    </div>
                                    <div class="col-md-12">
                                        <div id="lead_by_product_container" style="height: 300px; width: 100%;"></div>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-lg-6">
        <section class="panel">
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="quote_stage_start_date" name="quote_stage_start_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="load_funal();">
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="quote_stage_end_date" name="quote_stage_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="load_funal();">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="quote_stage_user_id" id="quote_stage_user_id" onchange="load_funal();">
                                                <?= $user_ids; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 chart-report-title">
                                        <h3><strong>Stage Funnel</strong></h3>
                                    </div>
                                    <div class="col-md-12">
                                        <div id="quote_stage_container" style="height: 300px; width: 100%;"></div>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-lg-6">
        <section class="panel">
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="inq_cat_start_date" name="inq_cat_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="load_inq_cat();">
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="inq_cat_end_date" name="inq_cat_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="load_inq_cat();">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="inq_cat_user_id" id="inq_cat_user_id" onchange="load_inq_cat();">
                                                <?= $user_ids; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Category</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="inq_cat_category_id" id="inq_cat_category_id" onchange="load_inq_cat();">
                                                <option value="">All</option>
                                                <?= get_master_category_dtl($dbcon, $rel['inquiry_cat_id'], 9,0,0); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 chart-report-title">
                                        <h3><strong>Lead By Inquiry Category</strong></h3>
                                    </div>
                                    <div class="col-md-12">
                                        <div id="inq_cat_container" style="height: 300px; width: 100%;"></div>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-lg-12">
        <section class="panel">
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-4" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="month_wise_objection_start_date" name="month_wise_objection_start_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="load_month_wise_objection();">
                                        </div>
                                    </div>
                                    <div class="col-md-4" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="month_wise_objection_end_date" name="month_wise_objection_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="load_month_wise_objection();">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="month_wise_objection_user_id" id="month_wise_objection_user_id" onchange="load_month_wise_objection();">
                                                <?= $user_ids; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 chart-report-title">
                                        <h3><strong>Opportunity with Objections</strong></h3>
                                    </div>
                                    <div class="col-md-12">
                                        <div id="month_wise_objection_container" style="height: 300px; width: 100%;"></div>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-lg-12">
        <section class="panel">
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-4" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="month_won_revenue_start_date" name="month_won_revenue_start_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="load_month_wise_won();">
                                        </div>
                                    </div>
                                    <div class="col-md-4" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="month_won_revenue_end_date" name="month_won_revenue_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="load_month_wise_won();">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="month_won_revenue_user_id" id="month_won_revenue_user_id" onchange="load_month_wise_won();">
                                                <?= $user_ids; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 chart-report-title">
                                        <h3><strong>Month Wise Won Revnue</strong></h3>
                                    </div>
                                    <div class="col-md-12">
                                        <div id="month_won_revenue_container" style="height: 300px; width: 100%;"></div>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-lg-6">
        <section class="panel">
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="lead_by_city_start_date" name="lead_by_city_start_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="load_lead_by_city();">
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="lead_by_city_end_date" name="lead_by_city_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="load_lead_by_city();">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="lead_by_city_user_id" id="lead_by_city_user_id" onchange="load_lead_by_city();">
                                                <?= $user_ids; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>State</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="lead_by_city_state_id" id="lead_by_city_state_id" onchange="load_lead_by_city();">
                                                <?= get_state_all($dbcon, '', "101", true) ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 chart-report-title">
                                        <h3><strong>City Wise Leads</strong></h3>
                                    </div>
                                    <div class="col-md-12">
                                        <div id="lead_by_city_container" style="height: 300px; width: 100%;"></div>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-lg-6">
        <section class="panel">
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="lead_by_state_start_date" name="lead_by_state_start_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="load_lead_by_state();">
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="lead_by_state_end_date" name="lead_by_state_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="load_lead_by_state();">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="lead_by_state_user_id" id="lead_by_state_user_id" onchange="load_lead_by_state();">
                                                <?= $user_ids; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 chart-report-title">
                                        <h3><strong>State Wise Leads</strong></h3>
                                    </div>
                                    <div class="col-md-12">
                                        <div id="lead_by_state_container" style="height: 300px; width: 100%;"></div>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-6">
        <section class="panel">
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="lead_source_start_date" name="lead_source_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="load_lead_by();">
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="lead_source_end_date" name="lead_source_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="load_lead_by();">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="lead_source_user_id" id="lead_source_user_id" onchange="load_lead_by();">
                                                <?= $user_ids; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 chart-report-title">
                                        <h3><strong>Leads By Source</strong></h3>
                                    </div>
                                    <div class="col-md-12">
                                        <div id="lead_source_container" style="height: 300px; width: 100%;"></div>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-lg-6">
        <section class="panel">
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="employee_sales_start_date" name="employee_sales_start_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="load_employee_sales();">
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="employee_sales_end_date" name="employee_sales_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="load_employee_sales();">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="employee_sales_user_id" id="employee_sales_user_id" onchange="load_employee_sales();">
                                                <?= $user_ids; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 chart-report-title">
                                        <h3><strong>Employee Sales Performance</strong></h3>
                                    </div>
                                    <div class="col-md-12">
                                        <div id="employee_sales_container" style="height: 300px; width: 100%;"></div>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Target Chart Start -->
    <div class="col-lg-12">
        <section class="panel">
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <label class="col-md-12 control-label" style="font-weight: bold;font-size: 20px;color: black;">Target Product</label>
                            <select class="form-control" name="t_pro_id" id="t_pro_id" onchange="load_target_chart();">
                                <?= getproduct_typewise($dbcon, "", "0","",""); ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="col-md-12 control-label" style="font-weight: bold;font-size: 20px;color: black;">Target Year</label>
                            <?php$s_year = 2018;
                            $e_year = date('Y');
                            ?>
                            <select class="form-control" name="t_pro_year" id="t_pro_year" onchange="load_target_chart();">
                                <?phpfor ($i = $s_year; $i <= $e_year; $i++) {
                                    $sel = '';
                                    if (date('Y') == $i) {
                                        $sel = 'selected="selected"';
                                    }
                                ?>
                                    <option <?= $sel ?> value="<?= $i ?>"><?= $i ?></option>
                                <?php} ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="col-md-12 control-label" style="font-weight: bold;font-size: 20px;color: black;">Target Wise</label>
                            <select class="form-control" name="t_pro_wise" id="t_pro_wise" onchange="load_target_chart();">
                                <option value="1">Quantity</option>
                                <option value="2">Amount</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12 overflow-auto">
                        <div id="chart-5"></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <!-- Target Chart End -->
</div>

<script type="text/javascript">
    $(document).ready(function() {
        Loading(true);
        load_counts();
        load_target_chart();
        load_lead_by();
        load_employee_sales();
        load_lead_by_product();
        load_inq_cat();
        load_funal();
        load_month_wise_objection();
        load_month_wise_won();
        load_lead_by_city();
        load_lead_by_state();
        load_closed_won_sales();
        Unloading();

        $('#inquiry_lost_lnk').hover(
            function() {
                // Mouse enters, change the background color of the parent div
                $(this).parent('.inq-list-stage-box').css('background-color', 'lightblue');
            },
            function() {
                // Mouse leaves, revert to the default background color of the parent div
                $(this).parent('.inq-list-stage-box').css('background-color', '');
            }
        );
        $('.inquiry_sales_stage_lnk').hover(
            function() {
                // Mouse enters, change the background color of the parent div
                $(this).parent('.inq-list-stage-box').css('background-color', 'lightblue');
            },
            function() {
                // Mouse leaves, revert to the default background color of the parent div
                $(this).parent('.inq-list-stage-box').css('background-color', '');
            }
        );

    });

    function load_funal() {
        var d_start_date = $('#quote_stage_start_date').val();
        var d_end_date = $('#quote_stage_end_date').val();
        var d_user_id = $('#quote_stage_user_id').val();
        //var c_year=$('#c_year4').val();
        var mainurl = root_domain + crm_domain + 'app/crm_dashboard/index.php?mode=load_funal&start_date=' + d_start_date + '&end_date=' + d_end_date + '&user_id=' + d_user_id
        $.getJSON(mainurl, function(json) {
            var arr1 = new Array();
            for (var i = 0; i < json.length; i++) {
                arr1[i] = json[i], json[i];
            }
            //console.log(arr1);
            if (arr1.length) {
                var chart = new CanvasJS.Chart("quote_stage_container", {
                    animationEnabled: true,
                    title: {
                        text: ""
                    },
                    data: [{
                        cursor: "pointer",
                        type: "funnel",
                        indexLabel: "{label} - {y}",
                        yValueFormatString: "#,##0",
                        neckHeight: 0,
                        dataPoints: arr1
                    }]
                });
                chart.options.data[0].click = function(e) {

                    var dataSeries = e.dataSeries;
                    var dataPoint = e.dataPoint;
                    var dataPointIndex = e.dataPointIndex;
                    if (dataPoint.link) {
                        window.open(dataPoint.link, '_blank');
                    }


                    for (var i = 0; i < dataSeries.dataPoints.length; i++) {
                        if (i === dataPointIndex) {
                            continue;
                        }

                        dataSeries.dataPoints[i].exploded = false;
                    }
                };
                chart.render();
            } else {
                $("#quote_stage_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
            }
        });
    }

    function load_counts() {
        var rep_date = $("#rep_date").val();
        $.ajax({
            type: "POST",
            url: root_domain + crm_domain + 'app/crm_dashboard/',
            data: {
                mode: "load_counts",
                date: rep_date
            },
            success: function(response) {
                //console.log(response);
                var data = JSON.parse(response);
                $('#business_achieved_counts').html(data.business_achieved_counts).attr("title", data.business_achieved_words);
                $('#opportunity_onhand_counts').html(data.opportunity_onhand_counts).attr("title", data.opportunity_onhand_words);
                $('#pending_quotation_counts').html(data.pending_quotation_counts).attr("title", data.pending_quotation_counts);
                $('#lost_opportunity_counts').html(data.lost_opportunity_counts).attr("title", data.lost_opportunity_words);
                $('#hot_leads_counts_4').html(data.hot_leads_counts).attr("title", data.hot_leads_words);
                $('#hot_leads_counts_5').html(data.cold_leads_counts).attr("title", data.cold_leads_words);
                $('#hot_leads_counts_6').html(data.warm_leads_counts).attr("title", data.warm_leads_words);
                $('#hot_leads_counts_7').html(data.not_appli_leads_counts).attr("title", data.not_appli_leads_words);
            }
        });
        Unloading();
    }

    function load_graph() {
        Loading(true);
        var c_year = $('#c_year').val();
        var mainurl = root_domain + crm_domain + 'app/crm_dashboard/index.php?mode=dynamic_chart&c_year=' + c_year;
        $.getJSON(mainurl, function(json) {
            var arr = new Array();
            for (var i = 0; i < 12; i++) {
                arr[i] = json[i];
            }
            Morris.Bar({
                element: 'chart-3',
                data: arr,
                barSizeRatio: 0.55,
                xkey: 'device',
                ykeys: ['geekbench'],
                labels: ['Total Inquiry'],
                barRatio: 0.4,
                xLabelAngle: 35,
                hideHover: 'auto',
                barColors: ['#6883a3'],
                lineWidth: 25
            });
        });
        Unloading();
    }

    function load_lead_by() {
        var d_start_date = $('#lead_source_start_date').val();
        var d_end_date = $('#lead_source_end_date').val();
        var d_user_id = $('#lead_source_user_id').val();

        //var mainurl = root_domain+'app/dashboard/index.php?mode=employee_circle&c_year='+c_year
        var mainurl = root_domain + crm_domain + 'app/crm_dashboard/index.php?mode=lead_circle&start_date=' + d_start_date + '&end_date=' + d_end_date + '&user_id=' + d_user_id

        $.getJSON(mainurl, function(json) {
            console.log(json);
            var arr1 = new Array();
            for (var i = 0; i < json.length; i++) {
                arr1[i] = json[i], json[i];
            }
            // console.log(arr1);

            if (arr1.length) {
                var chart = new CanvasJS.Chart("lead_source_container", {
                    theme: "light2",
                    animationEnabled: true,
                    title: {
                        text: ""
                    },
                    data: [{
                        cursor: "pointer",
                        type: "doughnut",
                        radius: "100%",
                        innerRadius: "50%",
                        indexLabelPlacement: "outside",
                        indexLabel: "{symbol} - {y}",
                        yValueFormatString: "#,##0.0\"\"",
                        showInLegend: false,
                        legendText: "{label} : {symbol}",
                        dataPoints: arr1
                    }]
                });
                chart.options.data[0].click = function(e) {

                    var dataSeries = e.dataSeries;
                    var dataPoint = e.dataPoint;
                    var dataPointIndex = e.dataPointIndex;
                    if (dataPoint.link) {
                        window.open(dataPoint.link, '_blank');
                    }


                    for (var i = 0; i < dataSeries.dataPoints.length; i++) {
                        if (i === dataPointIndex) {
                            continue;
                        }

                        dataSeries.dataPoints[i].exploded = false;
                    }
                };
                chart.render();
            } else {
                $("#lead_source_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
            }
        });

    }

    function load_employee_sales() {
        var d_start_date = $('#employee_sales_start_date').val();
        var d_end_date = $('#employee_sales_end_date').val();
        var d_user_id = $('#employee_sales_user_id').val();
        var c_year = $('#c_year1').val();

        var mainurl = root_domain + crm_domain + 'app/crm_dashboard/index.php?mode=load_employee_sales&d_start_date=' + d_start_date + '&d_end_date=' + d_end_date + '&d_user_id=' + d_user_id + '&c_year=' + c_year
        $.getJSON(mainurl, function(json) {
            var arr1 = new Array();
            for (var i = 0; i < json.length; i++) {
                arr1[i] = json[i], json[i];
            }
            // console.log(arr1);
            if (arr1.length) {
                var chart = new CanvasJS.Chart("employee_sales_container", {
                    animationEnabled: true,
                    title: {
                        text: ""
                    },
                    axisX: {
                        interval: 1
                    },
                    axisY: {
                        title: "",

                    },
                    data: [{
                        type: "bar",
                        dataPoints: arr1
                    }]
                });
                chart.render();
            } else {
                $("#employee_sales_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
            }
        });
    }

    function load_closed_won_sales() {
        var d_start_date = $('#closed_won_sales_start_date').val();
        var d_end_date = $('#closed_won_sales_end_date').val();
        var d_user_id = $('#closed_won_sales_user_id').val();
        var c_year = $('#c_year1').val();

        var mainurl = root_domain + crm_domain + 'app/crm_dashboard/index.php?mode=load_closed_won_sales&d_start_date=' + d_start_date + '&d_end_date=' + d_end_date + '&d_user_id=' + d_user_id + '&c_year=' + c_year
        $.getJSON(mainurl, function(json) {
            var arr1 = new Array();
            var won = json.won;

            // for Won Inquiry
            for (var i = 0; i < won.length; i++) {
                arr1[i] = won[i], won[i];
            }
            if (arr1.length) {
                var chart = new CanvasJS.Chart("won_sales_container", {
                    animationEnabled: true,
                    title: {
                        text: ""
                    },
                    axisX: {
                        interval: 1
                    },
                    axisY: {
                        title: "",
                    },
                    data: [{
                        cursor: "pointer",
                        type: "bar",
                        dataPoints: arr1
                    }]
                });
                chart.options.data[0].click = function(e) {
                    var dataSeries = e.dataSeries;
                    var dataPoint = e.dataPoint;
                    var dataPointIndex = e.dataPointIndex;
                    if (dataPoint.link) {
                        window.open(dataPoint.link, '_blank');
                    }
                    for (var i = 0; i < dataSeries.dataPoints.length; i++) {
                        if (i === dataPointIndex) {
                            continue;
                        }
                        dataSeries.dataPoints[i].exploded = false;
                    }
                };
                chart.render();
            } else {
                $("#won_sales_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
            }

            // For Lost Inquiry 
            var lost = json.lost;
            var arr1 = new Array();
            for (var i = 0; i < lost.length; i++) {
                arr1[i] = lost[i], lost[i];
            }
            if (arr1.length) {
                var chart1 = new CanvasJS.Chart("lost_sales_container", {
                    animationEnabled: true,
                    title: {
                        text: ""
                    },
                    axisX: {
                        interval: 1
                    },
                    axisY: {
                        title: "",
                    },
                    data: [{
                        cursor: "pointer",
                        type: "bar",
                        dataPoints: arr1
                    }]
                });
                chart1.options.data[0].click = function(e) {

                    var dataSeries = e.dataSeries;
                    var dataPoint = e.dataPoint;
                    var dataPointIndex = e.dataPointIndex;
                    if (dataPoint.link) {
                        window.open(dataPoint.link, '_blank');
                    }

                    for (var i = 0; i < dataSeries.dataPoints.length; i++) {
                        if (i === dataPointIndex) {
                            continue;
                        }

                        dataSeries.dataPoints[i].exploded = false;
                    }
                };
                chart1.render();
            } else {
                $("#lost_sales_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
            }

            // For Lotherst Inquiry 
            var inquiry = json.inquiry;
            var arr1 = new Array();
            for (var i = 0; i < inquiry.length; i++) {
                arr1[i] = inquiry[i], inquiry[i];
            }
            if (arr1.length) {
                var chart2 = new CanvasJS.Chart("pending_sales_container", {
                    animationEnabled: true,
                    title: {
                        text: ""
                    },
                    axisX: {
                        interval: 1
                    },
                    axisY: {
                        title: "",
                    },
                    data: [{
                        cursor: "pointer",
                        type: "bar",
                        dataPoints: arr1
                    }]
                });
                chart2.options.data[0].click = function(e) {

                    var dataSeries = e.dataSeries;
                    var dataPoint = e.dataPoint;
                    var dataPointIndex = e.dataPointIndex;
                    if (dataPoint.link) {
                        window.open(dataPoint.link, '_blank');
                    }


                    for (var i = 0; i < dataSeries.dataPoints.length; i++) {
                        if (i === dataPointIndex) {
                            continue;
                        }

                        dataSeries.dataPoints[i].exploded = false;
                    }
                };
                chart2.render();
            } else {
                $("#pending_sales_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
            }
        });
    }

    function load_lead_by_product() {
        var d_start_date = $('#lead_by_product_start_date').val();
        var d_end_date = $('#lead_by_product_end_date').val();
        var d_user_id = $('#lead_by_product_user_id').val();
        var d_category_id = $('#lead_by_product_category_id').val();

        var mainurl = root_domain + crm_domain + 'app/crm_dashboard/index.php?mode=load_lead_by_product&start_date=' + d_start_date + '&end_date=' + d_end_date + '&user_id=' + d_user_id + '&category_id=' + d_category_id
        $.getJSON(mainurl, function(json) {
            var arr1 = new Array();
            for (var i = 0; i < json.length; i++) {
                arr1[i] = json[i], json[i];
            }

            if (arr1.length) {
                var chart = new CanvasJS.Chart("lead_by_product_container", {
                    animationEnabled: true,
                    //exportEnabled: true,
                    title: {
                        text: ""
                    },
                    /* subtitles: [{
                        text: "Currency Used: Thai Baht (฿)"
                    }], */
                    data: [{
                        cursor: "pointer",
                        type: "pie",
                        radius: "100%",
                        showInLegend: "true",
                        legendText: "{label}",
                        indexLabelFontSize: 16,
                        //indexLabel: "{label} - #percent",
                        indexLabel: "{label} - {y}",
                        //yValueFormatString: "฿#,##0",
                        dataPoints: arr1
                    }]
                });
                chart.options.data[0].click = function(e) {
                    var dataSeries = e.dataSeries;
                    var dataPoint = e.dataPoint;
                    var dataPointIndex = e.dataPointIndex;
                    if (dataPoint.link) {
                        window.open(dataPoint.link, '_blank');
                    }


                    for (var i = 0; i < dataSeries.dataPoints.length; i++) {
                        if (i === dataPointIndex) {
                            continue;
                        }

                        dataSeries.dataPoints[i].exploded = false;
                    }
                };
                chart.render();
            } else {
                $("#lead_by_product_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
            }
        });
    }

    function load_inq_cat() {
        var d_start_date = $('#inq_cat_start_date').val();
        var d_end_date = $('#inq_cat_end_date').val();
        var d_user_id = $('#inq_cat_user_id').val();
        var d_category_id = $('#inq_cat_category_id').val();

        var mainurl = root_domain + crm_domain + 'app/crm_dashboard/index.php?mode=load_inq_cat&start_date=' + d_start_date + '&end_date=' + d_end_date + '&user_id=' + d_user_id + '&category_id=' + d_category_id
        $.getJSON(mainurl, function(json) {
            var arr1 = new Array();
            for (var i = 0; i < json.length; i++) {
                arr1[i] = json[i], json[i];
            }

            if (arr1.length) {
                var chart = new CanvasJS.Chart("inq_cat_container", {
                    animationEnabled: true,
                    //exportEnabled: true,
                    title: {
                        text: ""
                    },
                    /* subtitles: [{
                        text: "Currency Used: Thai Baht (฿)"
                    }], */
                    data: [{
                        cursor: "pointer",
                        type: "pie",
                        radius: "100%",
                        showInLegend: "true",
                        legendText: "{label}",
                        indexLabelFontSize: 16,
                        //indexLabel: "{label} - #percent",
                        indexLabel: "{label} - {y}",
                        //yValueFormatString: "฿#,##0",
                        dataPoints: arr1
                    }]
                });
                chart.options.data[0].click = function(e) {
                    var dataSeries = e.dataSeries;
                    var dataPoint = e.dataPoint;
                    var dataPointIndex = e.dataPointIndex;
                    if (dataPoint.link) {
                        window.open(dataPoint.link, '_blank');
                    }


                    for (var i = 0; i < dataSeries.dataPoints.length; i++) {
                        if (i === dataPointIndex) {
                            continue;
                        }

                        dataSeries.dataPoints[i].exploded = false;
                    }
                };
                chart.render();
            } else {
                $("#inq_cat_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
            }
        });
    }

    function load_month_wise_objection() {
        var d_start_date = $('#month_wise_objection_start_date').val();
        var d_end_date = $('#month_wise_objection_end_date').val();
        var d_user_id = $('#month_wise_objection_user_id').val();

        var mainurl = root_domain + crm_domain + 'app/crm_dashboard/index.php?mode=load_month_wise_objection&start_date=' + d_start_date + '&end_date=' + d_end_date + '&user_id=' + d_user_id
        $.getJSON(mainurl, function(json) {
            var arr1 = new Array();
            for (var i = 0; i < json.length; i++) {
                arr1[i] = json[i], json[i];
            }
            //console.log(arr1);
            if (arr1.length) {
                var chart = new CanvasJS.Chart("month_wise_objection_container", {
                    animationEnabled: true,
                    theme: "light2", // "light1", "light2", "dark1", "dark2"
                    title: {
                        text: ""
                    },
                    /* axisY: {
                        title: "Number of Apps",
                        includeZero: false
                    }, */
                    data: [{
                        cursor: "pointer",
                        type: "column",
                        name: "Artificial Trees",
                        indexLabel: "{y}",
                        yValueFormatString: "#0.##",
                        //showInLegend: true,
                        dataPoints: arr1
                    }]
                });
                chart.options.data[0].click = function(e) {
                    var dataSeries = e.dataSeries;
                    var dataPoint = e.dataPoint;
                    var dataPointIndex = e.dataPointIndex;
                    if (dataPoint.link) {
                        window.open(dataPoint.link, '_blank');
                    }

                    for (var i = 0; i < dataSeries.dataPoints.length; i++) {
                        if (i === dataPointIndex) {
                            continue;
                        }
                        dataSeries.dataPoints[i].exploded = false;
                    }
                };
                chart.render();
            } else {
                $("#month_wise_objection_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
            }
        });
    }

    function load_month_wise_won() {
        var d_start_date = $('#month_won_revenue_start_date').val();
        var d_end_date = $('#month_won_revenue_end_date').val();
        var d_user_id = $('#month_won_revenue_user_id').val();

        var mainurl = root_domain + crm_domain + 'app/crm_dashboard/index.php?mode=load_month_wise_won&start_date=' + d_start_date + '&end_date=' + d_end_date + '&user_id=' + d_user_id
        $.getJSON(mainurl, function(json) {
            var arr1 = new Array();
            for (var i = 0; i < json.length; i++) {
                arr1[i] = json[i], json[i];
            }
            //console.log(arr1);
            if (arr1.length) {
                var chart = new CanvasJS.Chart("month_won_revenue_container", {
                    animationEnabled: true,
                    theme: "light2", // "light1", "light2", "dark1", "dark2"
                    title: {
                        text: ""
                    },
                    /* axisY: {
                    	title: "Number of Apps",
                    	includeZero: false
                    }, */
                    data: [{
                        type: "column",
                        name: "Artificial Trees",
                        indexLabel: "{y}",
                        yValueFormatString: "₹#0.##",
                        //showInLegend: true,
                        dataPoints: arr1
                    }]
                });
                chart.render();
            } else {
                $("#month_won_revenue_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
            }
        });
    }

    function load_lead_by_city() {
        var d_start_date = $('#lead_by_city_start_date').val();
        var d_end_date = $('#lead_by_city_end_date').val();
        var d_user_id = $('#lead_by_city_user_id').val();
        var d_state_id = $('#lead_by_city_state_id').val();
        //var c_year=$('#c_year6').val();

        var mainurl = root_domain + crm_domain + 'app/crm_dashboard/index.php?mode=load_lead_by_city&start_date=' + d_start_date + '&end_date=' + d_end_date + '&user_id=' + d_user_id + '&state_id=' + d_state_id
        $.getJSON(mainurl, function(json) {
            var arr1 = new Array();
            for (var i = 0; i < json.length; i++) {
                arr1[i] = json[i], json[i];
            }
            //console.log(arr1);
            if (arr1.length) {
                var chart = new CanvasJS.Chart("lead_by_city_container", {
                    animationEnabled: true,
                    //exportEnabled: true,
                    title: {
                        text: ""
                    },
                    /* subtitles: [{
                        text: "Currency Used: Thai Baht (฿)"
                    }], */
                    data: [{
                        cursor: "pointer",
                        type: "pie",
                        radius: "100%",
                        //showInLegend: "true",
                        legendText: "{label}",
                        indexLabelFontSize: 12,
                        //indexLabel: "{label} - #percent",
                        indexLabel: "{label} - {y}",
                        //yValueFormatString: "฿#,##0",
                        dataPoints: arr1
                    }]
                });
                chart.options.data[0].click = function(e) {
                    var dataSeries = e.dataSeries;
                    var dataPoint = e.dataPoint;
                    var dataPointIndex = e.dataPointIndex;
                    if (dataPoint.link) {
                        window.open(dataPoint.link, '_blank');
                    }

                    for (var i = 0; i < dataSeries.dataPoints.length; i++) {
                        if (i === dataPointIndex) {
                            continue;
                        }
                        dataSeries.dataPoints[i].exploded = false;
                    }
                };
                chart.render();
            } else {
                $("#lead_by_city_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
            }
        });
    }

    function load_lead_by_state() {
        var d_start_date = $('#lead_by_state_start_date').val();
        var d_end_date = $('#lead_by_state_end_date').val();
        var d_user_id = $('#lead_by_state_user_id').val();
        //var c_year=$('#c_year7').val();

        var mainurl = root_domain + crm_domain + 'app/crm_dashboard/index.php?mode=load_lead_by_state&start_date=' + d_start_date + '&end_date=' + d_end_date + '&user_id=' + d_user_id
        $.getJSON(mainurl, function(json) {
            var arr1 = new Array();
            for (var i = 0; i < json.length; i++) {
                arr1[i] = json[i], json[i];
            }
            //console.log(arr1);
            if (arr1.length) {
                var chart = new CanvasJS.Chart("lead_by_state_container", {
                    animationEnabled: true,
                    //exportEnabled: true,
                    title: {
                        text: ""
                    },
                    /* subtitles: [{
                        text: "Currency Used: Thai Baht (฿)"
                    }], */
                    data: [{
                        cursor: "pointer",
                        type: "pie",
                        radius: "100%",
                        //showInLegend: "true",
                        legendText: "{label}",
                        indexLabelFontSize: 12,
                        //indexLabel: "{label} - #percent",
                        indexLabel: "{label} - {y}",
                        //yValueFormatString: "฿#,##0",
                        dataPoints: arr1
                    }]
                });
                chart.options.data[0].click = function(e) {
                    var dataSeries = e.dataSeries;
                    var dataPoint = e.dataPoint;
                    var dataPointIndex = e.dataPointIndex;
                    if (dataPoint.link) {
                        window.open(dataPoint.link, '_blank');
                    }

                    for (var i = 0; i < dataSeries.dataPoints.length; i++) {
                        if (i === dataPointIndex) {
                            continue;
                        }
                        dataSeries.dataPoints[i].exploded = false;
                    }
                };
                chart.render();
            } else {
                $("#lead_by_state_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
            }
        });
    }

    function load_target_chart() {
        $('#chart-5').html('');
        $('.title_chart1').html('');
        Loading();
        var t_pro_id = $('#t_pro_id').val();
        var t_pro_year = $('#t_pro_year').val();
        var t_pro_wise = $('#t_pro_wise').val();

        var mainurl = root_domain + crm_domain + 'app/crm_dashboard/index.php?mode=load_target_chart&t_pro_id=' + t_pro_id + '&t_pro_year=' + t_pro_year + '&t_pro_wise=' + t_pro_wise;

        $.getJSON(mainurl, function(json) {
            //console.log(json);
            if (!json) {
                $('#chart-5').html('<strong>No Data !!</strong>');
            } else {
                var arr = new Array();
                for (var i = 0; i < 12; i++) {
                    arr[i] = [json[json[i]], json[i]];
                }
                fil_arr = arr;
                $('#chart-5').jqBarGraph({
                    data: fil_arr,
                    colors: ['#6883a3', '#3fc343', ''],
                    legends: ['Target', 'Achived', ''],
                    legend: true,
                    width: 1100,
                    color: '#ffffff',
                    type: 'multi',
                    postfix: '',
                    showValues: true,
                    title: '<h3 class="title_chart1 chart-report-title">Target Chart</h3>'
                });
            }
        });
        Unloading();
    }

    // Open Lost inquiries in inquiry list
    $("#inquiry_lost_lnk").on('click', function(e) {
        var rep_date = $("#rep_date").val();
        var dates = rep_date.split(" - ");
        var start_date = dates[0];
        var end_date = dates[1];

        var url = "<?= ROOT . CRM_ROOT . 'inquiry_list_stage_lost/' . LOST . '/'; ?>" + start_date + '/' + end_date;

        // Open the URL in a new tab
        window.open(url, '_blank');

    });

    // Open Lost inquiries in inquiry list
    $(".inquiry_sales_stage_lnk").on('click', function(e) {
        var rep_date = $("#rep_date").val();
        var dates = rep_date.split(" - ");
        var start_date = dates[0];
        var end_date = dates[1];

        var sales_stage_id = $(this).data('param');
        var url = "<?= ROOT . CRM_ROOT . 'inquiry_list_sales_stage/'; ?>" + sales_stage_id + '/' + start_date + '/' + end_date;

        // Open the URL in a new tab
        window.open(url, '_blank');

    });
</script>