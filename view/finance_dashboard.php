<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$frmdt=date('d-m-Y');
	$todt=date('d-m-Y');
	//Ankit Sompura 09-01-2021
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		FINANCE_DASHBOARD
	]);
	if(!in_array(FINANCE_DASHBOARD,$bulkAccessArray)){
       header("Location: ".DOMAIN."permission_access");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
<style>
.icons{
width: 22.5%;
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
				include_once('../include/finance_dashbord_counter.php');
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
<script src="<?=ROOT?>js/app/finance_dashboard.js?<?=time()?>"></script>

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
</body>
</html>
