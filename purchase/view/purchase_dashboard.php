<?php 
session_start();
include('../include/urlfile.php');
$frmdt=date('d-m-Y');
$todt=date('d-m-Y');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	PURCHASE_DASHBOARD_VIEW
]);
if(!in_array(PURCHASE_DASHBOARD_VIEW,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
?>
<?php

$dataPoints1 = array(
	array("label"=> "JAN", "y"=> 2016456,"x"=> 0 ),
	array("label"=> "FEB", "y"=> 1985801,"x"=> 0 ),
	array("label"=> "MARCH", "y"=> 1755904,"x"=> 2016456 ),
	array("label"=> "APRIL", "y"=> 1847290,"x"=> 2016456 )

);

$dataPoints2 = array(
	array("label"=> "JAN", "y"=> 49505,"x"=> 0 ),
	array("label"=> "FEB", "y"=> 31917,"x"=> 0 ),
	array("label"=> "MARCH", "y"=> 25972,"x"=> 0 ),
	array("label"=> "APRIL", "y"=> 23337,"x"=> 0 )
);
?>

<?php
//TOP 20 CUST 
$dataPoints20ven = array( 
	array("label"=>"Chrome", "y"=>64.02),
	array("label"=>"Firefox", "y"=>12.55),
	array("label"=>"IE", "y"=>8.47),
	array("label"=>"Safari", "y"=>6.08),
	array("label"=>"Edge", "y"=>4.29),
	array("label"=>"Others", "y"=>4.59)
)

?>
<?php
//TOP 20 product
$dataPoints20product = array( 
	array("label"=>"Chrome", "y"=>64.02),
	array("label"=>"Firefox", "y"=>12.55),
	array("label"=>"IE", "y"=>8.47),
	array("label"=>"Safari", "y"=>6.08),
	array("label"=>"Edge", "y"=>4.29),
	array("label"=>"Others", "y"=>4.59)
)

?>

<?php
 //dealy item
$dataPointsdealyitem = array(
	array("label"=> "Education", "y"=> 284935),
	array("label"=> "Entertainment", "y"=> 256548),
	array("label"=> "Lifestyle", "y"=> 245214),
	array("label"=> "Business", "y"=> 233464),
	array("label"=> "Music & Audio", "y"=> 200285),
	array("label"=> "Personalization", "y"=> 194422),
	array("label"=> "Tools", "y"=> 180337),
	array("label"=> "Books & Reference", "y"=> 172340),
	array("label"=> "Travel & Local1", "y"=> 118187),
	array("label"=> "Travel & Local2", "y"=> 118187),
	array("label"=> "Travel & Local3", "y"=> 118187),
	array("label"=> "Travel & Local4", "y"=> 118187),
	array("label"=> "Travel & Local5", "y"=> 118187),
	array("label"=> "Travel & Local6", "y"=> 118187),
	array("label"=> "Travel & Local8", "y"=> 118187),
	array("label"=> "Travel & Local9", "y"=> 118187),
	array("label"=> "Travel & Local0", "y"=> 118187),
	array("label"=> "Travel & Local11", "y"=> 118187),
	array("label"=> "Travel & Local12", "y"=> 118187),
	array("label"=> "Puzzle", "y"=> 107530)
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
</head>
	<title>PURCHASE DASHBOARD</title>
	<?php include_once($include.'/include_css_file.php');?>
	<style>
	.icons{
		width: 17.5%;
		float: left;
		margin: 10px 13px 5px;
		text-align: center;
		position:relative;
		border-radius: 8px;
	}
	.icons12{
		background-color:#fff;
		padding-top:15px;
		border: 8px;
	}
	.icons p{
		text-align:center;
		font-size:15px;
		font-weight:600;
		padding-top:5px;
		font-color:white

	}

	.icon1 fa{

	}
	.icon1.success{background-color: #5cb85c;}
	.icon1.primary{background-color: #0275d8;}
	.icon1.warning{background-color: #f0ad4e;}
	.icon1.info{background-color: #5bc0de;}
	.icon1.danger{background-color: #d9534f;}
	.icon1.terques{background-color: #6ccac9;}
	.icon1.yellow{background-color: #f8d347;}
	.icon1.pink{background-color:#E5649A;}
	.icon1.mustard{background-color:#F0BD23;}
	.icon1.success,.icon1.primary,.icon1.warning,.icon1.danger,.icon1.info,.icon1.terques,.icon1.yellow,.icon1.pink,.icon1.mustard{
		width: 110px;
		height:90px;
		border-radius: 8px;
		text-align:center;
		margin:0 auto
	}
	.icon1.success i,.icon1.primary i,.icon1.warning i,.icon1.danger i,.icon1.info i,.icon1.terques i,.icon1.yellow i,.icon1.pink i,.icon1.mustard i{
		text-align:center;
		color:#fff;
		padding-top: 27%;
		font-size: 37px;
	}
	@media (max-width:767px){
		.icons {
			width: 47%;
			float: left;
			margin: 30px 4px 25px;
			position:relative;
		}

	}
	@media (min-width:768px) and (max-width:980px)
	{
		.icons12{
			background-color:#fff;
			padding-top:20px;
			padding-bottom:20px;
			border-radius: 8px;
		}
		.icons {
			width: 265px;
			float: left;
			margin: 30px 4px 25px;
			text-align: center;
			position:relative;
		}

	}
	.icons .badge {
		position: absolute;
		right: 25px;
		top: 0px;
		z-index: 100;
	}


</style>
<style>
.small-box {
	border-radius: .25rem;
	-moz-border-radius: .25rem;
	-webkit-border-radius: .25rem;
	box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
	-moz-box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
	-webkit-box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
	display: block;
	margin-bottom: 20px;
	position: relative;
	margin-left: 50px;
	margin-top: 15px;
}

.small-box>.inner {
	padding: 10px;
}

.small-box h3 {
	font-size: 3rem;
	font-weight: 700;
	margin: 0 0 10px 0;
	padding: 0;
	white-space: nowrap;
}

.small-box h3, .small-box p {
	z-index: 5;
	color: #fff;
}
.small-box p {
	font-size: 2rem;
}

.small-box .icon {
	color: rgba(0,0,0,.15);
	z-index: 0;
}

.small-box .icon>i {
	font-size: 90px;
	position: absolute;
	right: 15px;
	top: 15px;
	transition: all .3s linear;
	-moz-transition: all .3s linear;
	-webkit-transition: all .3s linear;
}

.small-box .icon>i.ion {
	font-size: 70px;
	top: 20px;
}

.small-box>.small-box-footer {
	background: rgba(0,0,0,.1);
	color: rgba(255,255,255,.8);
	display: block;
	padding: 3px 0;
	position: relative;
	text-align: center;
	text-decoration: none;
	z-index: 10;
}

.bg-info {
	background-color: #17a2b8!important;
}

.bg-success {
	background-color: #28a745!important;
}

.bg-warning {
	background-color: #ffc107!important;
}

.bg-danger {
	background-color: #dc3545!important;
}

.chartStyle {
	height: 300px; 
	width: 100%;
}

.setMargin {
	margin: 20px 0;
}

.datepicker td.disabled.day {
	color: #ccc;
}
</style>

</head>
<body>
	<section id="container" class="sidebar-closed">

		<?php include_once($include.'/include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($include.'/left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->

		<section id="main-content">
			<section class="wrapper">	
				<div class="row">
					
					<div class=" col-md-4">
						<!-- small box -->
						<div class="small-box bg-info" onclick="detalils_view('1');">
							<div class="inner">
								<h3 class="live_complaint_cnt" id="sales"><span id="today_over_due_inword"></span></h3>
								<p>Over Due Inward</p>
								<span style="color: #FFFFFF;"><span id="sales_percentage"></span><!--% since yesterday--></span>
							</div>
							<div class="icon">
								<i class="ion fa fa-cog"></i> 
							</div>
						</div>
					</div>
					<div class="col-4 col-md-4">
						<!-- small box --> 
						<div class="small-box bg-success" onclick="detalils_view('2');">
							<div class="inner">
								<h3 class="inst_done_cnt" id="purchase"><span id="over_due_7days"></span></h3>
								<p>Order Due In Today</p>
								<span style="color: #FFFFFF;"><span id="purchase_percentage"><!-- 0 --></span><!-- % since yesterday --></span>
							</div>
							<div class="icon">
								<i class="ion fa fa-cog"></i>
							</div>
						</div>
					</div>
					<div class="col-4 col-md-4">
						<!-- small box -->
						<div class="small-box bg-danger" onclick="detalils_view('3');">
							<div class="inner">
								<h3 class="inst_pending_cnt" id="receivable"><span id="over_due_inworde"></span></h3>
								<p>Pending Inward</p>
								<span style="color: #FFFFFF;"><span id="receivable_percentage"><!-- 5 --></span><!-- % since yesterday --></span>
							</div>
							<div class="icon">
								<i class="ion fa fa-cog"></i>
							</div>
						</div>
					</div>



					<!-- <div class=" col-md-4">
						<div class="small-box bg-info" onclick="detalils_view('1');">
							<div class="inner">
								<h3 class="live_complaint_cnt" id="sales"><span id="today_over_due_inword"></span></h3>
								<p>Over Due Today</p>
								<span style="color: #FFFFFF;"><span id="sales_percentage"></span></span>
							</div>
							<div class="icon">
								<i class="ion fa fa-cog"></i> 
							</div>
						</div>
					</div>

					<div class="col-4 col-md-4">
						<div class="small-box bg-success" onclick="detalils_view('2');">
							<div class="inner">
								<h3 class="inst_done_cnt" id="purchase"><span id="over_due_7days"></span></h3>
								<p>Order Due In Today</p>
								<span style="color: #FFFFFF;"><span id="purchase_percentage"></span></span>
							</div>
							<div class="icon">
								<i class="ion fa fa-cog"></i>
							</div>
						</div>
					</div>

					<div class="col-4 col-md-4">
						<div class="small-box bg-danger" onclick="detalils_view('3');">
							<div class="inner">
								<h3 class="inst_pending_cnt" id="receivable"><span id="over_due_inworde"></span></h3>
								<p>Overdue Order</p>
								<span style="color: #FFFFFF;"><span id="receivable_percentage"></span></span>
							</div>
							<div class="icon">
								<i class="ion fa fa-cog"></i>
							</div>
						</div>
					</div> -->

				      <!-- <div class="col-4 col-md-3">
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
				          </div> -->
				      </div>
				      <div class="row">
				      	<div class="col-md-12">
				      		<div class="col-lg-6">
				      			<div class="col-lg-12">
				      				<div class="col-md-3" style="padding-right: 0px;float: right">
				      					<select class="form-control" id="pur_amount_filter" name="pur_amount_filter" onchange="po_and_purchase_diff_new()">
				      						<option value="0">Quantity</option>
				      						<option value="1">Amount</option>
				      					</select>
				      				</div>
				      			</div>
				      			<section class="panel col-lg-12">
				      				<div id="chartContainer" style="height: 370px; width: 100%;"></div>
				      			</section>
				      		</div>
				      		<div class="col-lg-6">
				      			<div class="col-lg-12">
				      				<div class="col-md-3" style="padding-right: 0px;float: right">
				      					<select class="form-control" id="vendor_filter" name="vendor_filter" onchange="load_top_20_vender()">
				      						<option value="0">Quantity</option>
				      						<option value="1">Amount</option>
				      					</select>
				      				</div>
				      			</div>
				      			<section class="panel col-lg-12">
				      				<div id="chartContainer20ven" style="height: 370px; width: 100%;"></div>
				      			</section>
				      		</div>
				      	</div>
				      	<div class="col-md-12">
				      		<div class="col-lg-6">
				      			<div class="col-lg-12">
				      				<div class="col-md-3" style="padding-right: 0px;float: right">
				      					<select class="form-control" id="product_filter" name="product_filter" onchange="load_top_20_product()">
				      						<option value="0">Quantity</option>
				      						<option value="1">Amount</option>
				      					</select>
				      				</div>
				      			</div>
				      			<section class="panel col-lg-12">
				      				<div id="chartContainer20product" style="height: 370px; width: 100%;"></div>
				      			</section>
				      		</div>
				      		<div class="col-lg-6">
				      			
				      			<section class="panel col-lg-12">
				      				<div id="chartContainerdealyitem" style="height: 370px; width: 100%;"></div>
				      			</section>
				      		</div>
				      	</div>
				      	<div class="col-md-12">
				      		<div class="col-lg-6">
				      			<div class="col-lg-12">
				      				<div class="col-md-3" style="padding-right: 0px;float: right">
				      					<select class="form-control" id="pur_cat_filter" name="pur_cat_filter" onchange="load_top_20_cat()">
				      						<option value="0">Quantity</option>
				      						<option value="1">Amount</option>
				      					</select>
				      				</div>
				      			</div>
				      			<section class="panel col-lg-12">
				      				<div id="chartContainer20cat" style="height: 370px; width: 100%;"></div>
				      			</section>
				      		</div>
				      		<div class="col-lg-6">
				      			<section class="panel">
				      				<div id="chart-5"></div>
				      				
				      			</section>
				      		</div> 
				      	</div>
				      </div>	
				      <!--state overview start-->
				      <?php 
				      if(!empty($_SESSION['company_id']))
				      {
				          	//include_once('../include/purchase_dashboard_counter.php');
				      }
				      ?>

				      <!--state overview end-->
				  </section>
				</section>
				<!--main content end-->
				<!--footer start-->


				<?php include_once($include.'/footer.php');?>
				<!--footer end-->
			</section>

			<!-- js placed at the end of the document so the pages load faster -->
			<?php include_once($include.'/include_js_file.php');?>   
			<script src="<?=ROOT.PURCHASE_ROOT?>js/app/purchase_dashbord.js?<?=time()?>"></script>

			<script>
				$(".select2").select2({
					width: '100%'
				});
//load_followup_status_history();
//show_todolist();
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
function cb(start, end) {
	$('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
}
cb(moment().subtract(29, 'days'), moment());


$('.datepikerdemo').daterangepicker({       
	locale: {
		format: 'DD-MM-YYYY'
	},
	"autoApply": true,	
	"startDate": $('#from_date').val(),
	"endDate": $('#to_date').val(),	
	ranges: {
		'Today': [moment(), moment()],
		'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
		'Last 7 Days': [moment().subtract(6, 'days'), moment()],
		'Last 30 Days': [moment().subtract(29, 'days'), moment()],
		'This Month': [moment().startOf('month'), moment().endOf('month')],
		'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
	}
}, cb);
$('.date-set').click(function(){
	$('.datepikerdemo').trigger('click')
});
</script>

<script type="text/javascript">
	//pathik start
	window.onload = function () {

		/*var chart1 = new CanvasJS.Chart("chartContainer", { 
			theme: "light2",
			title: {
				text: "TOTAL PO AND PURCHASE"
			},
			subtitles: [{
				text: ""
			}],
			legend:{
				cursor: "pointer",
				itemclick: toggleDataSeries
			},
			toolTip: {
				shared: true
			},
			data: [{
				type: "stackedArea",
				name: "PO",
				showInLegend: true,
				visible: true,
				yValueFormatString: "#,##0 GWh",
				dataPoints: <?php echo json_encode($dataPoints1, JSON_NUMERIC_CHECK); ?>
			},
			{
				type: "stackedArea",
				name: "PURCHASE",
				showInLegend: true,
				yValueFormatString: "#,##0 GWh",
				dataPoints: <?php echo json_encode($dataPoints2, JSON_NUMERIC_CHECK); ?>
			}]
		});

		chart1.render();
		*/
		function toggleDataSeries(e){
			if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
				e.dataSeries.visible = false;
			}
			else{
				e.dataSeries.visible = true;
			}
			chart1.render();
		}

//TOP 20 VENDOR


//TOP 20 PRODUCT



//dealy item
/*var chart = new CanvasJS.Chart("chartContainerdealyitem", {
	animationEnabled: true,
    theme: "light2", // "light1", "light2", "dark1", "dark2"
    title: {
    	text: "Top 20 DELAY ITEM"
    },
    axisY: {
    	title: "Number of Days"
    },
    data: [{
    	type: "column",
    	dataPoints: <?php echo json_encode($dataPointsdealyitem, JSON_NUMERIC_CHECK); ?>
    }]
});
chart.render();

*/
}
</script>
</body>
</html>
