<?php 
$start_date= date('d-m-Y', strtotime($_SESSION['financial_start_date']));
$end_date=date("d-m-Y", strtotime($_SESSION['financial_end_date']));

$getspecialConfiguration=getspecialConfiguration($dbcon);
error_reporting(E_ALL);
?>
<div class="">
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
                                    <div class="col-md-4">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="month_won_revenue_user_id" id="month_won_revenue_user_id" onchange="load_month_wise_won();" >
                                                <option value="">Select User</option>
                                                <?php $qry = $dbcon->query("SELECT f.f_user_id, user.user_name FROM tbl_forecast_user as f LEFT JOIN users as user ON user.user_id = f.f_user_id WHERE f.forecast_status = 0 AND f.company_id = ".$_SESSION['company_id']." GROUP BY f_user_id");
                                                while($rese = brp_mysqli_fetch_assoc($qry)){ 
                                                    // $sel = '';
                                                    if($rese['f_user_id']==$_SESSION['user_id']){
                                                        $sel = 'selected';
                                                    }
                                                    ?>
                                                    <option value="<?=$rese['f_user_id']?>" <?=$sel?>><?=$rese['user_name']?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
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
    <?php if ($getspecialConfiguration["rb_auto_permission"] == 0) { ?>
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
                                            <input id="month_won_qty_start_date" name="month_won_qty_start_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$start_date?>" placeholder="Start Date" onchange="load_month_wise_won_qty();">
                                        </div>
                                    </div>
                                    <div class="col-md-4" style="margin-bottom: 15px;">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <input id="month_won_qty_end_date" name="month_won_qty_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$end_date?>" placeholder="End Date" onchange="load_month_wise_won_qty();">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                                        <div class="col-md-9" style="padding-right: 0px;">
                                            <select class="form-control" name="month_won_qty_user_id" id="month_won_qty_user_id" onchange="load_month_wise_won_qty();" >
                                                <option value="">Select User</option>
                                                <?php $qry = $dbcon->query("SELECT f.f_user_id, user.user_name FROM tbl_forecast_user as f LEFT JOIN users as user ON user.user_id = f.f_user_id WHERE f.forecast_status = 0 AND f.company_id = ".$_SESSION['company_id']." GROUP BY f_user_id");
                                                while($rese = brp_mysqli_fetch_assoc($qry)){ 
                                                    // $sel = '';
                                                    if($rese['f_user_id']==$_SESSION['user_id']){
                                                        $sel = 'selected';
                                                    }
                                                    ?>
                                                    <option value="<?=$rese['f_user_id']?>" <?=$sel?>><?=$rese['user_name']?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div id="month_won_qty_container" style="height: 300px; width: 100%;"></div>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php } ?>
    <!-- Target Chart Start -->
    <div class="col-lg-12">
        <section class="panel">
            <div class="panel-body" >
                <div class="row state-overview">
                    <div class="col-md-12">
                        <div class="col-md-3">
                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                            <div class="col-md-9" style="padding-right: 0px;">
                                <input id="target_start_date" name="target_start_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$start_date?>" placeholder="Start Date" onchange="load_target_chart();">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                            <div class="col-md-9" style="padding-right: 0px;">
                                <input id="target_end_date" name="target_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$end_date?>" placeholder="End Date" onchange="load_target_chart();">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
                            <div class="col-md-9" style="padding-right: 0px;">
                                <select class="form-control" name="target_user_id" id="target_user_id" onchange="load_target_chart();" >
                                    <option value="">Select User</option>
                                    <?php $qry = $dbcon->query("SELECT f.f_user_id, user.user_name FROM tbl_forecast_user as f LEFT JOIN users as user ON user.user_id = f.f_user_id WHERE f.forecast_status = 0 AND f.company_id = ".$_SESSION['company_id']." GROUP BY f_user_id");
                                    while($rese = brp_mysqli_fetch_assoc($qry)){ 
                                        // $sel = '';
                                        if($rese['f_user_id']==$_SESSION['user_id']){
                                            $sel = 'selected';
                                        }
                                        ?>
                                        <option value="<?=$rese['f_user_id']?>" <?=$sel?>><?=$rese['user_name']?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Target Wise</strong></div>
                            <div class="col-md-9" style="padding-right: 0px;">
                            <select class="form-control" name="t_pro_wise" id="t_pro_wise" onchange="load_target_chart();" >
                                <?php if ($getspecialConfiguration["rb_auto_permission"] == 0) { ?>
                                    <option value="1">Quantity</option>
                                <?php } ?>
                                <option value="2">Amount</option>
                            </select>
                        </div>
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
        load_target_chart();
        load_month_wise_won();
        load_month_wise_won_qty();
        Unloading();
    });

    function load_month_wise_won()
    {
        var d_start_date=$('#month_won_revenue_start_date').val();
        var d_end_date=$('#month_won_revenue_end_date').val();
        var d_user_id=$('#month_won_revenue_user_id').val();
        // var c_year=$('#c_year5').val();

        var mainurl = root_domain + crm_domain +'app/forecast_dashboard/index.php?mode=load_month_wise_won_amount&start_date='+d_start_date+'&end_date='+d_end_date+'&user_id='+d_user_id
        $.getJSON(mainurl, function(json) {
            var arr1=new Array();
            for(var i=0;i<json.length;i++)
            {	
                arr1[i]=json[i],json[i];	
            }
            //console.log(arr1);
            if(arr1.length){
                var chart = new CanvasJS.Chart("month_won_revenue_container", {
                    animationEnabled: true,
                    theme: "light2", // "light1", "light2", "dark1", "dark2"
                    title: {
                        text: "Month Wise Achieved Target Amount"
                    },
                    /*axisY: {
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
    function load_month_wise_won_qty()
    {
        var d_start_date=$('#month_won_qty_start_date').val();
        var d_end_date=$('#month_won_qty_end_date').val();
        var d_user_id=$('#month_won_qty_user_id').val();
        // var c_year=$('#c_year5').val();

        var mainurl = root_domain + crm_domain +'app/forecast_dashboard/index.php?mode=load_month_wise_won_qty&start_date='+d_start_date+'&end_date='+d_end_date+'&user_id='+d_user_id
        $.getJSON(mainurl, function(json) {
            var arr1=new Array();
            for(var i=0;i<json.length;i++)
            {   
                arr1[i]=json[i],json[i];    
            }
            //console.log(arr1);
            if(arr1.length){
                var chart = new CanvasJS.Chart("month_won_qty_container", {
                    animationEnabled: true,
                    theme: "light2", // "light1", "light2", "dark1", "dark2"
                    title: {
                        text: "Month Wise Achieved Target Qty"
                    },
                    /*axisY: {
                        title: "Number of Apps",
                        includeZero: false
                    }, */
                    data: [{
                        type: "column",
                        name: "Artificial Trees",
                        indexLabel: "{y}",
                        yValueFormatString: "#0.##",
                        //showInLegend: true,
                        dataPoints: arr1
                    }]
                });
                chart.render();
            } else {
                $("#month_won_qty_container").html('<br/><br/><br/><div style="text-align: center;"><strong>No Data Found<strong></div>');
            }
        }); 
    }
    function load_target_chart()
    {
        $('#chart-5').html('');
        $('.title_chart1').html('');
        Loading();
        var target_start_date=$('#target_start_date').val();
        var target_end_date=$('#target_end_date').val();
        var target_user_id=$('#target_user_id').val();
        var t_pro_wise=$('#t_pro_wise').val();

        var mainurl = root_domain + crm_domain +'app/forecast_dashboard/index.php?mode=load_target_chart&target_start_date='+target_start_date+'&target_end_date='+target_end_date+'&t_pro_wise='+t_pro_wise+'&target_user_id='+target_user_id;

        $.getJSON(mainurl, function(json) {
            //console.log(json);
            if(!json){
                $('#chart-5').html('<strong>No Data !!</strong>');
            }
            else{
                var arr=new Array();
                for(var i=0;i<12;i++)
                {	
                    arr[i]=[json[json[i]],json[i]];	
                }
                fil_arr=arr;
                $('#chart-5').jqBarGraph({
                    data: fil_arr,
                    colors: ['#6883a3','#3fc343',''],
                    legends: ['Target','Achived',''],
                    legend: true,
                    width: 1100,
                    color: '#ffffff',
                    type: 'multi',
                    postfix: '',
                    showValues: true,
                    title: '<h3 class="title_chart1">Target vs Achived Chart</h3>'
                });
            }
        });
        Unloading();
    }
</script>