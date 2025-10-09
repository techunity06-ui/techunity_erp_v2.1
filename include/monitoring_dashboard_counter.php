<?php 
$start_date= date('d-m-Y', strtotime('first day of last month'));
$end_date=date("d-m-Y");
?>
<!--counts of Stock-->
<div class="row">
    <div class=" col-md-3">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3 class="live_complaint_cnt" id="sales">0.00</h3>
                <p>Today Received</p>
                <span style="color: #FFFFFF;"><span id="sales_percentage">0</span>% since yesterday</span>
            </div>
            <div class="icon">
                <i class="ion fa fa-cog"></i>
            </div>
        </div>
    </div>
    <div class="col-4 col-md-3">
        <!-- small box --> 
        <div class="small-box bg-success">
            <div class="inner">
                <h3 class="inst_done_cnt" id="purchase">0.00</h3>
                <p>Today Paid</p>
                <span style="color: #FFFFFF;"><span id="purchase_percentage">0</span>% since yesterday</span>
            </div>
            <div class="icon">
                <i class="ion fa fa-cog"></i>
            </div>
        </div>
    </div>
    <div class="col-4 col-md-3">
        <!-- small box -->
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 class="inst_pending_cnt" id="receivable">0.00</h3>
                <p>Today Receivable</p>
                <span style="color: #FFFFFF;"><span id="receivable_percentage">5</span>% since yesterday</span>
            </div>
            <div class="icon">
                <i class="ion fa fa-cog"></i>
            </div>
        </div>
    </div>
    <div class="col-4 col-md-3">
        <!-- small box -->
        <div class="small-box" style="background-color:#f8d347;">
            <div class="inner">
                <h3 class="inst_pending_cnt" id="payable">0.00</h3>
                <p>Today Payable</p>
                <span style="color: #FFFFFF;"><span id="payable_percentage">7</span>% since yesterday</span>
            </div>
            <div class="icon">
                <i class="ion fa fa-cog"></i>
            </div>
        </div>
    </div>
</div>

<div class="">
    <!-- counts for today's Received, Paid, Receivable, Payable -->
    <div class="col-lg-12">
        <section class="panel1">
            <div class="panel-body">
                <div class="row state-overview">
                    <div class="icons icons12">
                        <div class="icon1 info">
                            <i class="fa fa-tags" aria-hidden="true"></i>
                        </div>
                        <div class="">
                            <h1 class="count">25,000.00</h1>
                            <p style="color: #4c4e4e;">Finish Stock</p>
                        </div>
                    </div>
                    <div class="icons icons12">
                        <div class="icon1 success">
                            <i class="fa fa-tags" aria-hidden="true"></i>
                        </div>
                        <div class="">
                            <h1 class="count">13,000.00</h1>
                            <p style="color: #4c4e4e;">WIP Stock</p>
                        </div>
                    </div>
                    <div class="icons icons12">
                        <div class="icon1 pink">
                            <i class="fa fa-tags" aria-hidden="true"></i>
                        </div>
                        <div class="">
                            <h1 class="count">40,000.00</h1>
                            <p style="color: #4c4e4e;">Raw Material</p>
                        </div>
                    </div>
                    <div class="icons icons12">
                        <div class="icon1 danger">
                            <i class="fa fa-tags" aria-hidden="true"></i>
                        </div>
                        <div class="">
                            <h1 class="count">30,000.00</h1>
                            <p style="color: #4c4e4e;">Consumable</p>
                        </div>
                    </div>
                    <div class="icons icons12">
                        <div class="icon1 yellow">
                            <i class="fa fa-tags" aria-hidden="true"></i>
                        </div>
                        <div class="">
                            <h1 class="count" >25,000.00</h1>
                            <p style="color: #4c4e4e;">Finish Part & BOI</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Top 5 Sold Product -->
    <div class="col-lg-12">
        <div class="col-lg-6" style="min-height: 300px;">
         <section class="panel">
            <div class="panel-body" >
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-12 head-text" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;">Top 5 Sold Product</div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="col-md-3" style="padding-right: 0px;float: right">
                                            <select class="form-control" id="product_filter" name="product_filter" onchange="get_five_sold_products()">
                                                <option value="0">Quantity</option>
                                                <option value="1">Amount</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5" style="padding-right: 0px;float: right">
                                            <select class="form-control" id="product_type" name="product_type" onchange="get_five_sold_products()">
                                                <?=getproducttype($dbcon,'');?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th style="text-align: right">Total Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody id="sold_products">
                                            </tbody>
                                        </table>
                                        <div style="float: right;">
                                            <a class="btn btn-success" target="_blank" href="">View More</a>
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
    
    <!-- Top 5 Purchased Product -->
    <div class="col-lg-6" style="min-height: 300px;">
        <section class="panel">
            <div class="panel-body" >
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-12 head-text" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;">Top 5 Purchased Product</div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="col-md-3" style="padding-right: 0px;float: right">
                                            <select class="form-control" id="purchase_product_filter" name="purchase_product_filter" onchange="get_five_purchased_products()">
                                                <option value="0">Quantity</option>
                                                <option value="1">Amount</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5" style="padding-right: 0px;float: right">
                                            <select class="form-control" id="purchase_product_type" name="purchase_product_type" onchange="get_five_purchased_products()">
                                                <?=getproducttype($dbcon,'');?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th style="text-align: right">Total Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody id="purchased_products">
                                            </tbody>
                                        </table>
                                        <div style="float: right;"><a class="btn btn-success" target="_blank" href="">View More</a></div>
                                        <!--                                            <div id="payable_aging_chart" style="height: 300px; width: 100%;"></div>-->
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

<!-- Top 5 Customers -->
<div class="col-lg-12">
    <div class="col-lg-6" style="min-height: 300px;">
        <section class="panel">
            <div class="panel-body" >
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-12 head-text" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;">Top 5 Customer</div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="col-md-4" style="padding-right: 0px;float: right">
                                            <select class="form-control" id="cust_filter" name="cust_filter" onchange="get_five_customer()">
                                                <option value="0">Quantity</option>
                                                <option value="1">Amount</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Customer</th>
                                                    <th style="text-align: right">Business</th>
                                                </tr>
                                            </thead>
                                            <tbody id="five_customer">
                                            </tbody>
                                        </table>
                                        <div style="float: right;"><a class="btn btn-success" target="_blank" href="">View More</a></div>
                                        <!--                                            <div id="payable_aging_chart" style="height: 300px; width: 100%;"></div>-->
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Top 5 Vendors -->
    <div class="col-lg-6" style="min-height: 300px;">
        <section class="panel">
            <div class="panel-body" >
                <div class="row state-overview">
                    <div class="col-md-12 col-sm-6">
                        <section class="panel">
                            <div class="row">
                                <form>
                                    <div class="col-md-12 head-text" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;">Top 5 Vendor</div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="col-md-4" style="padding-right: 0px;float: right">
                                            <select class="form-control" id="vendor_filter" name="vendor_filter" onchange="get_five_vendors()">
                                                <option value="0">Quantity</option>
                                                <option value="1">Amount</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Vendor</th>
                                                    <th style="text-align: right">Business</th>
                                                </tr>
                                            </thead>
                                            <tbody id="five_vendors">
                                            </tbody>
                                        </table>
                                        <div style="float: right;"><a class="btn btn-success" target="_blank" href="">View More</a></div>
                                        <!--                                            <div id="payable_aging_chart" style="height: 300px; width: 100%;"></div>-->
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

<!-- Profit and Loss -->
<div class="col-lg-12">
    <section class="panel">
        <div class="panel-body" >
            <div class="row state-overview">
                <div class="col-md-12 col-sm-6">
                    <section class="panel">
                        <div class="row">
                            <form>
                                <div class="col-md-12" style="margin-bottom: 15px;">
                                    <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><h2>Profit And loss</h2></div>
                                    <div class="col-md-4" style="padding-right: 0px;float: right">
                                        <select class="form-control" id="pl_filter" name="pl_filter">
                                            <option value="">Yearly</option>
                                            <option value="">Monthly</option>
                                            <option value="">Daily</option>
                                        </select>
                                    </div>
                                </div>
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

<!-- Total Sales -->
<div class="col-lg-12">
    <section class="panel">
        <div class="panel-body" >
            <div class="row state-overview">
                <div class="col-md-12 col-sm-6">
                   <section class="panel">
                    <div class="row">
                        <form>
                            <div class="col-md-12" style="margin-bottom: 15px;">
                                <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><h2>Sales</h2></div>
                                <div class="col-md-4" style="padding-right: 0px;float: right">
                                    <select class="form-control" id="sales_filter" name="sales_filter" onchange="load_sales()">
                                        <option value="1">Yearly</option>
                                        <option value="2" selected="">Monthly</option>
                                        <option value="3">Daily</option>
                                    </select>
                                </div>
                                <div class="col-md-3" style="padding-right: 0px;float: right">
                                    <select class="form-control" id="amount_filter" name="amount_filter" onchange="load_sales()">
                                        <option value="0">Quantity</option>
                                        <option value="1">Amount</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div id="month_wise_sales" style="height: 300px; width: 100%;"></div>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
</div>

<!-- Total Purchase -->
<div class="col-lg-12">
    <section class="panel">
        <div class="panel-body" >
            <div class="row state-overview">
                <div class="col-md-12 col-sm-6">
                   <section class="panel">
                    <div class="row">
                        <form>
                            <div class="col-md-12" style="margin-bottom: 15px;">
                                <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><h2>Purchase</h2></div>
                                <div class="col-md-4" style="padding-right: 0px;float: right">
                                    <select class="form-control" id="purchase_filter" name="purchase_filter" onchange="load_purchase()">
                                        <option value="1">Yearly</option>
                                        <option value="2" selected="">Monthly</option>
                                        <option value="3">Daily</option>
                                    </select>
                                </div>
                                <div class="col-md-3" style="padding-right: 0px;float: right">
                                    <select class="form-control" id="pur_amount_filter" name="pur_amount_filter" onchange="load_purchase()">
                                        <option value="0">Quantity</option>
                                        <option value="1">Amount</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div id="month_wise_purchase" style="height: 300px; width: 100%;"></div>
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



