<?php 
$start_date= date('d-m-Y', strtotime('first day of last month'));
$end_date=date("d-m-Y");


?>
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
                                    <h1 class="count" id="outgoing_bills">0</h1>
                                    <p style="color: #4c4e4e;">Total Outgoing Bills</p>
                                    <span><span id="outgoing_bills_percentage">0</span>% since last month</span>
                            </div>
                    </div>
                    <div class="icons icons12">
                            <div class="icon1 yellow">
                                    <i class="fa fa-tags" aria-hidden="true"></i>
                            </div>
                            <div class="">
                                    <h1 class="count" id="incoming_bills">0</h1>
                                    <p style="color: #4c4e4e;">Total Incoming Bills</p>
                                    <span><span id="incoming_bills_percentage">0</span>% since last month</span>
                            </div>
                    </div>
                    <div class="icons icons12">
                            <div class="icon1 pink">
                                    <i class="fa fa-money" aria-hidden="true"></i>
                            </div>
                            <div class="">
                                    <h1 class="count" id="incoming_payment">0</h1>
                                    <p style="color: #4c4e4e;">Total Incoming Payment</p>
                                    <span><span id="incoming_payment_percentage">0</span>% since last month</span>
                            </div>
                    </div>
                     <div class="icons icons12">
                            <div class="icon1 pink">
                                    <i class="fa fa-money" aria-hidden="true"></i>
                            </div>
                            <div class="">
                                    <h1 class="count" id="outgoing_payment">0</h1>
                                    <p style="color: #4c4e4e;">Total Outgoing Payment</p>
                                    <span><span id="outgoing_payment_percentage">0</span>% since last month</span>
                            </div>
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
<!--                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Profit This Year<br/>0.00</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                    <input id="lead_source_start_date" name="lead_source_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$start_date?>" placeholder="Start Date" onchange="load_lead_by();">
                                            </div>
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                            <div class="col-md-9" style="padding-right: 0px;">
                                                    <input id="lead_source_end_date" name="lead_source_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$end_date?>" placeholder="End Date" onchange="load_lead_by();">
                                            </div>
                                    </div>-->
                                    <div class="col-md-12">
                                        <div id="profit_loss_chart" style="height: 300px; width: 100%;"></div>
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
                                    <div class="col-md-12">
                                        <div id="incoming_bills_chart" style="height: 300px; width: 100%;"></div>
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
                                    <div class="col-md-12">
                                        <div id="outgoingbills_chart" style="height: 300px; width: 100%;"></div>
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
                                    <div class="col-md-12">
                                        <div id="receivable_aging_chart" style="height: 300px; width: 100%;"></div>
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
                                    <div class="col-md-12">
                                            <div id="payable_aging_chart" style="height: 300px; width: 100%;"></div>
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
                                    <div class="col-md-12">
                                        <div id="budgetvariance_chart" style="height: 300px; width: 100%;"></div>
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


