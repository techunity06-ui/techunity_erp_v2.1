<?php 
session_start();
include('../include/urlfile.php');
// $incPath = $path.'include/';
$form="Leads By Ownership Report";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']= 'crm/'.$infopage['filename'];
$start=date('1-m-Y');
$end=date("d-m-Y");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('../../include/include_css_file.php');?>

</head>
<body>
    <section id="container">
        <?php include_once('../../include/include_top_menu.php');?>
        <!--sidebar start-->
        <?php include_once('../../include/left_menu.php');?>
        <!--sidebar end-->
        <!--main content start-->
        <script>
            $(document).ready(function() {
                generate_report();
            });
            function generate_report(){
                Loading(true);
                $.ajax({
                    type: "POST",
                    url: crm_domain + 'app/crm_reports/report_leads_by_owner/',
                    data: { mode : "generate_report"},
                    success: function(response)
                    {
                        var resp=JSON.parse(response);
                        $('#adv-table').html(resp.html_resp);
                        draw_chart(resp.chart_data);
                        Unloading();							
                    }
                });	
            }

            function draw_chart(chart_data)
            {
                var arr1 = new Array();
                for(var i=0; i<chart_data.length; i++)
                {	
                    arr1[i] = chart_data[i],chart_data[i];	
                }

                var chart = new CanvasJS.Chart("employee_sales_container", {
                    animationEnabled: true,
                    axisX: {
                        interval: 1,
                        title: "Lead Owner"
                    },
                    axisY: {
                        title: "Record Count"

                    },
                    data: [{
                        type: "bar",
                        dataPoints:arr1
                    }]
                });
                chart.render();
            }
        </script>
        <section id="main-content">
           
           <section class="wrapper">
              
              <div class="row">
                 <div class="col-lg-12">
                    <!--breadcrumbs start -->
                    <section class="panel">
                       <header class="panel-heading">
<!--						<span class="tools pull-right">
							<a href="<?=ROOT.'report_list'?>"><button type="button" class="btn btn-info"><i class="fa fa-long-arrow-left" aria-hidden="true"></i> Report List</button></a>	
						</span>-->
						
						<h3 style=""><?=$form?> </h3>
					</header>	
					<div class="">
						<ul class="breadcrumb">
							<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="<?=ROOT.CRM_ROOT.'crm_report_list'?>"> CRM Report List</a></li>
                                    <li><?=$form?></li>
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
                    <div class="panel-body">
                        <div id="employee_sales_container" style="height: 300px; width: 100%;"></div>
                    </div>
                </section>
            </div>
        </div>	
        <div class="row">			
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading"> 
                        <span class="tools pull-right"> 
                            <a href="javascript:;" onClick="tableToExcel('adv-table', '<?=$form?>')" ><button class="btn btn-primary btn-flat" >Export Excel</button></a>	
                        </span> 
                        <div class="clearfix"></div>
                    </header>	
                    <div class="clearfix"></div>
                    <div class="panel-body">
                        <div class="adv-table" id="adv-table" style="overflow-x: scroll;">

                        </div>
                    </div>
                </section>
            </div>
        </div>	
        <!--state overview end-->
    </section>
</section>
<!--main content end-->
<!--footer start-->
<?php
include_once('../../include/footer.php');
?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   
<?php include_once('../../include/include_report_js_file.php');?>   


</body>
</html>
