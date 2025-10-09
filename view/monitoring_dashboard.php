<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$frmdt=date('d-m-Y');
$todt=date('d-m-Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>MONITORING DASHBOARD</title>
    <?php include_once('../include/include_css_file.php');?>
    <style>
    .head-text {
        font-family: "Segoe UI",Arial,sans-serif;
        font-weight: 400;
        width: 505px;
        margin: 10px 0;
        font-size: 25px;
        box-sizing: inherit;
        margin-block-start: -0.5em;
        margin-block-end: 0.0em;
        margin-inline-start: 8px;
        margin-inline-end: 0px;
        color: #fff!important;
        border-radius: 4px;
        position: relative;
        background-color: #009688!important;
    }

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
    <section id="container" >

        <?php include_once('../include/include_top_menu.php');?>
        <!--sidebar start-->
        <?php include_once('../include/left_menu.php');?>
        <!--sidebar end-->
        <!--main content start-->

        <section id="main-content">
           <section class="wrapper">			
              <!--state overview start-->
              <?php 
              if(!empty($_SESSION['company_id']))
              {
                include_once('../include/monitoring_dashboard_counter.php');
            }
            ?>
            
            <!--state overview end-->
        </section>
    </section>
    <!--main content end-->
    <!--footer start-->


    <?php include_once('../include/footer.php');?>
    <!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/monitoring_dashboard.js?<?=time()?>"></script>

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
	$('.datepikerdemo').trigger('click');
});
</script>
</body>
</html>
