<?php
if(isset($authenticate) && $authenticate == true) {
echo "
	<div id='mask' class='hidden-xs'>
		<div style='position:fixed;left: 45%;margin-left: -25%px;'>
			<img   src='".DOMAIN_CHEQUE."images/loading_lg.gif' />
			<h1> Loading ... </h1>
		</div>
    </div>
    <script src='".ROOT_CHEQUE."js/jquery.js'></script>
	<script src='".ROOT_CHEQUE."js/jquery.cookie/jquery.cookie.js'></script>
	<script src='".ROOT_CHEQUE."js/jquery.pushmenu/js/jPushMenu.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.nanoscroller/jquery.nanoscroller.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.sparkline/jquery.sparkline.min.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.ui/jquery-ui.js' ></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.gritter/js/jquery.gritter.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/behaviour/core.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.scroll/jquery.nicescroll.js'></script>
	<script type='text/javascript'> 
		$('#mask').height($(document).height());
		$(window).load(function () {
			$('#mask').fadeOut('slow');
			 $('html').niceScroll({cursoropacitymax:'0.6', cursorwidth:'5px', horizrailenabled:false});
		});
	</script>
	<!-- Bootstrap core JavaScript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src='".ROOT_CHEQUE."js/bootstrap/dist/js/bootstrap.min.js'></script>
	<script src='".ROOT_CHEQUE."js/jquery.parsley/parsley.js' type='text/javascript'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.datatables/jquery.datatables.min.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.datatables/bootstrap-adapter/js/datatables.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.select2/select2.min.js' ></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.flot/jquery.flot.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.flot/jquery.flot.pie.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.flot/jquery.flot.resize.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.flot/jquery.flot.labels.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/bootstrap.bootbox/bootbox.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.form.min.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/jquery.crop/js/jquery.Jcrop.js'></script>
	<script type='text/javascript' src='".ROOT_CHEQUE."js/bootstrap-datepicker/bootstrap-datepicker.js'></script>
	<script src='".ROOT_CHEQUE."js/jquery.maskedinput/jquery.maskedinput.js' type='text/javascript'></script>
	<script src='".ROOT_CHEQUE."js/app.js'></script>
	<script src='".ROOT_CHEQUE."js/jquery.clock.js'></script>
	";
	
	$dbcon->close();
}
else {
	die('Not auth');
}
?>
