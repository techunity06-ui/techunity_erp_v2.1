<?php 
$start_date= date('d-m-Y', strtotime('first day of last month'));
$end_date=date("d-m-Y");
$user_ids = check_user_chein($dbcon,$_SESSION['user_id'],1);

?>
<!--<link href="assets/morris.js-0.4.3/morris.css" rel="stylesheet" />-->
<div class="">
    <div class="col-lg-12">
        <section class="panel1">
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="icons icons12">
                            <div class="icon1 terques">
                                    <i class="fa fa-tags" aria-hidden="true"></i>
                            </div>
                            <div class="">
                                    <h1 class="count">72377</h1>
                                    <p style="color: #4c4e4e;">Total Outgoing Bills</p>
                                    <span>0% since last month</span>
                            </div>
                    </div>
                    <div class="icons icons12">
                            <div class="icon1 yellow">
                                    <i class="fa fa-tags" aria-hidden="true"></i>
                            </div>
                            <div class="">
                                    <h1 class="count">56521</h1>
                                    <p style="color: #4c4e4e;">Total Ingoing Bills</p>
                                    <span>0% since last month</span>
                            </div>
                    </div>
                    <div class="icons icons12">
                            <div class="icon1 pink">
                                    <i class="fa fa-money" aria-hidden="true"></i>
                            </div>
                            <div class="">
                                    <h1 class="count">62551</h1>
                                    <p style="color: #4c4e4e;">Total Incoming Payment</p>
                                    <span>0% since last month</span>
                            </div>
                    </div>
                     <div class="icons icons12">
                            <div class="icon1 pink">
                                    <i class="fa fa-money" aria-hidden="true"></i>
                            </div>
                            <div class="">
                                    <h1 class="count">32561</h1>
                                    <p style="color: #4c4e4e;">Total Outgoing Payment</p>
                                    <span>0% since last month</span>
                            </div>
                    </div>
                    
                    
                </div>
            </div>
        </section>
    </div>

   <!--  <div class="col-md-12 overflow-auto">
            
            <div id="chartContainer3" style="height: 370px; width: 100%;"></div>


        </div>
 -->
    <div class="col-lg-12">
        <section class="panel">
            <div class="panel-body" >
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
            <section class="panel">
                            <div class="row">
                <form>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                    <input id="lead_source_start_date" name="lead_source_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$start_date?>" placeholder="Start Date" onchange="load_lead_by();">
                                            </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                    <input id="lead_source_end_date" name="lead_source_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$end_date?>" placeholder="End Date" onchange="load_lead_by();">
                                            </div>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <div id="chartContainer3" style="height: 300px; width: 100%;"></div>
                                       <!--  <div id="lead_source_container" style="height: 300px; width: 100%;"></div> -->
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
            <div class="panel-body" >
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
			<section class="panel">
                            <div class="row">
				<form>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                    <input id="lead_source_start_date" name="lead_source_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$start_date?>" placeholder="Start Date" onchange="load_lead_by();">
                                            </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                    <input id="lead_source_end_date" name="lead_source_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$end_date?>" placeholder="End Date" onchange="load_lead_by();">
                                            </div>
                                    </div>
                                    <!--<div class="col-md-6">
                                                <select class="form-control" name="c_year" id="c_year" onchange="get_chart();" >
                                                        <?=get_year()?>
                                                </select>
                                        </div> -->
                                   <!--  <div class="col-md-6">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="lead_source_user_id" id="lead_source_user_id" onchange="get_chart();" >
                                                    <?=get_user_report($dbcon,$user_ids);?>
                                            </select>
                                        </div>
                                    </div> -->
                                    <div class="col-md-12">
                                        <div id="chartContainer" style="height: 300px; width: 100%;"></div>
                                       <!--  <div id="lead_source_container" style="height: 300px; width: 100%;"></div> -->
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
            <div class="panel-body" >
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
			<section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                <input id="employee_sales_start_date" name="employee_sales_start_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$start_date?>" placeholder="Start Date" onchange="load_employee_sales();">
                                            </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                <input id="employee_sales_end_date" name="employee_sales_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$end_date?>" placeholder="End Date" onchange="load_employee_sales();">
                                            </div>
                                    </div>
<!--                                    <div class="col-md-6">
                                            <select class="form-control" name="c_year1" id="c_year1" onchange="get_chart();" >
                                                    <?=get_year()?>
                                            </select>
                                    </div> -->
                                    <div class="col-md-12">
                                        <div id="chartContaineroutgoingbills" style="height: 300px; width: 100%;"></div>
                                            <!-- div id="employee_sales_container" style="height: 300px; width: 100%;"></div> -->
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
            <div class="panel-body" >
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                    <input id="lead_by_product_start_date" name="lead_by_product_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$start_date?>" placeholder="Start Date" onchange="load_lead_by_product();">
                                            </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                    <input id="lead_by_product_end_date" name="lead_by_product_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$end_date?>" placeholder="End Date" onchange="load_lead_by_product();">
                                            </div>
                                    </div>
<!--                                    <div class="col-md-6">
                                            <select class="form-control" name="c_year3" id="c_year3" onchange="get_chart();" >
                                                    <?//=get_year()?>
                                            </select>
                                    </div> -->
                                   <!--  <div class="col-md-6">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="lead_by_product_user_id" id="lead_by_product_user_id" onchange="get_chart();" >
                                                    <?=get_user_report($dbcon,$user_ids);?>
                                            </select>
                                        </div>
                                    </div> -->
                                    <div class="col-md-12">
                                            <div id="chartAcntReciver" style="height: 300px; width: 100%;"></div>
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
            <div class="panel-body" >
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                <input id="quote_stage_start_date" name="quote_stage_start_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$start_date?>" placeholder="Start Date" onchange="load_funal();">
                                            </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                <input id="quote_stage_end_date" name="quote_stage_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$end_date?>" placeholder="End Date" onchange="load_funal();">
                                            </div>
                                    </div>
<!--                                    <div class="col-md-6">
                                            <select class="form-control" name="c_year4" id="c_year4" onchange="get_chart();" >
                                                    <?//=get_year()?>
                                            </select>
                                    </div> -->
                                   
                                    <div class="col-md-12">
                                            <div id="chartAcntPayable" style="height: 300px; width: 100%;"></div>
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
            <div class="panel-body" >
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
            <section class="panel">
                            <div class="row">
                <form>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                    <input id="lead_source_start_date" name="lead_source_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$start_date?>" placeholder="Start Date" onchange="load_lead_by();">
                                            </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                    <input id="lead_source_end_date" name="lead_source_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$end_date?>" placeholder="End Date" onchange="load_lead_by();">
                                            </div>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <div id="chartbudgetvariance" style="height: 300px; width: 100%;"></div>
                                       <!--  <div id="lead_source_container" style="height: 300px; width: 100%;"></div> -->
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
            <div class="panel-body" >
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
				<form>
                                    <div class="col-md-4" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                <input id="month_won_revenue_start_date" name="month_won_revenue_start_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$start_date?>" placeholder="Start Date" onchange="load_month_wise_won();">
                                            </div>
                                    </div>
                                    <div class="col-md-4" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                <input id="month_won_revenue_end_date" name="month_won_revenue_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$end_date?>" placeholder="End Date" onchange="load_month_wise_won();">
                                            </div>
                                    </div>
<!--                                    <div class="col-md-6">
                                            <select class="form-control" name="c_year5" id="c_year5" onchange="load_month_wise_won();" >
                                                    <?//=get_year()?>
                                            </select>
                                    </div> -->                       
                                    <div class="col-md-12">
                                            <div id="chartbankbalance" style="height: 300px; width: 100%;"></div>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>
   
</div>


