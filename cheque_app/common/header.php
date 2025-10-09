<?php
if(isset($authenticate) && $authenticate == true) {

echo '
	<!-- TOP NAVBAR -->
	<div id="head-nav" class="navbar navbar-default">
		<div class="container-fluid">
			<div class="navbar-collapse">
				<ul class="nav navbar-nav navbar-right user-nav">
					<li class="hidden-xs"><a style="color:#7761A7;"  href="'.DOMAIN.'" ><strong>Billing 360</strong></a></li>
					<li class="hidden-xs"><a href="#date" style="color:#7761A7;"><b>'.date("d-m-Y").'</b></a></li>
					<!--<li class="hidden-xs"><a href="'.DOMAIN_CHEQUE.'new-cheque" style="color:#7761A7;">New Cheque</a></li>-->
					<li class="hidden-xs"><a href="'.DOMAIN_CHEQUE.'logout" style="color:#e74c3c;">Logout</a></li>
				</ul>	
				<ul class="nav navbar-nav not-nav">
					<li class="hidden-xs">
						<a href="'.DOMAIN_CHEQUE.'home" style="color:#7761A7;font-size:24px;">'.(COMPANY).'</a>
					</li>
				</ul>
			</div><!--/.nav-collapse animate-collapse -->
		</div>
	</div>';
}
else {
	die();
}
?>
