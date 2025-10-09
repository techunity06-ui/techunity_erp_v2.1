<?php 
	session_start();
	include('../include/urlfile.php');	
	$frmdt=date('d-m-Y');
	$todt=date('d-m-Y');
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    PRODUCTION_DASHBOARD_SLUG_VIEW
	]);

	if(!in_array(PRODUCTION_DASHBOARD_SLUG_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
    if(empty($_SESSION['start'])){
    $start = date('1-m-Y');
    $end = date("d-m-Y");
}
else{
    $start = $_SESSION['start'];
    $end = $_SESSION['end'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>PRODUCTION DASHBOARD</title>
<?php include_once($include.'include_css_file.php');?>
<style>
.icons{
    width: 13%;
    float: left;
    margin: 25px 100px;
    text-align: center;
    position:relative;

}
.icons12{
    background-color:#fff;
    padding-top:15px;
    border: 8px;
}
.icons p{
 text-align:center;
 font-size:25px;
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
    width: 250px;
    height:130px;
    border-radius: 8px;
    text-align:center;
}
.icon1.success i,.icon1.primary i,.icon1.warning i,.icon1.danger i,.icon1.info i,.icon1.terques i,.icon1.yellow i,.icon1.pink i,.icon1.mustard i{
 text-align:center;
 color:#fff;
 padding-top: 27%;
 font-size: 37px;
}
@media (max-width:767px){
    .icons {
        width:265px;
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
    width: 17%;
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
.hh {
	font-family: "Segoe UI",Arial,sans-serif;
    font-weight: 400;
    margin: 10px 0;
    font-size: 25px;
    box-sizing: inherit;
    margin-block-start: -0.5em;
    margin-block-end: 0.0em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    color: #fff!important;
    background-color: #009688!important;
}
</style>
</head>
<body>
<section id="container">
	<?php include_once($include.'include_top_menu.php');?>
	<!--sidebar start-->
	<?php include_once($include.'left_menu.php');?>
	<!--sidebar end-->
	<!--main content start-->
	
	<section id="main-content">
		<section class="wrapper">
			<section class="panel">
	<div class="panel-body ">
		<div class="row">
			<div class="col-md-12 hh" style="text-align:center;/*border-left-style: groove;border-right-style: groove;border-top-style: groove;*/"> Total Overview </div>
            <div class="col-md-12" style="/*border-left-style: groove;border-right-style: groove;border-top-style: groove;border-bottom-style: groove;*/">
                <div class="col-md-12 centeral-align" style="text-align:center;">
                   
                    <div class="icons" id="abcid">
                       <!--  <a class="" data-original-title="" data-toggle="tooltip" data-placement="top" href="<?php echo CRM_ROOT.'quotation_list' ?>" target="new"> -->
                            <div class="icon1 danger" >
                                <p style="color:white;padding-top:5px;">Total Complete</p>
                                <h3 style="font-size:18px;color:white;padding-top:2px;" id="quotamount"></h3>
                                <p style="color:white;">Qty : <span id="total_completed"></span></p>
                            </div>
                        <!-- </a> -->
                    </div>
                   
                    <div class="icons">
                       <!--  <a class="" data-original-title="" data-toggle="tooltip" data-placement="top" href="<?php echo ROOT.'pending_so_approve_list';?>" target="new"> -->
                            <div class="icon1 primary" >
                                <p style="color:white;padding-top:5px;">Total Reject</p>
                                <h3 style="font-size:18px;color:white;padding-top:2px;" id="soamount"></h3>
                                <p style="color:white;">Qty : <span id="total_reject"></span></p>
                            </div>
                        <!-- </a> -->
                    </div>
                   
                    <div class="icons">
                        <!-- <a class="" data-original-title="" data-toggle="tooltip" data-placement="top" href="<?=ROOT.PURCHASE_ROOT.'po_approve_pending_list'?>" target="new"> -->
                            <div class="icon1 success" >
                                <p style="color:white;padding-top:5px;">Total Pending</p>
                                <h3 style="font-size:18px;color:white;padding-top:2px;" id="poamount"></h3>
                                <p style="color:white;">Qty : <span id="total_pending"></span></p>
                            </div>
                        <!-- </a> -->
                    </div>

                </div>
			</div>
		</div>
	</div>
</section>
			<!--state overview start-->
			<?php 
				if(!empty($_SESSION['company_id']))
				{
					include_once($include.'production_dashbord_counter.php');
				}
			?>
			
			<!--state overview end-->
		</section>
	</section>
	<!--main content end-->
	<!--footer start-->
	
	<?php include_once($include.'footer.php');?>
	<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   

<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/production_dashboard.js?<?=time()?>"></script>
<script>

    var isLoad = 0;
    var wisLoad = 0;
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});

$(".select2").select2({
	width: '100%'
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
</body>
</html>
