<?php
if(isset($authenticate) && $authenticate == true) {
echo '
	<div class="cl-sidebar">
		<div class="cl-toggle"><i class="fa fa-bars"></i></div>
		<div class="cl-navblock">
			<div class="menu-space">
				<div class="content">
					<div class="sidebar-logo">
						<a href="'.DOMAIN_CHEQUE.'"><div class="logo"></div></a>
					</div>
					<ul class="cl-vnavigation">
						<li class="visible-xs" title="New Cheque" alt="New Cheque"><a href="'.DOMAIN_CHEQUE.'new-cheque"><i class="fa fa-leaf"></i><span>New Cheque</span></a></li>
						<li><a href="'.DOMAIN_CHEQUE.'home" title="Home" alt="Home"><i class="fa fa-home"></i><span>Home</span></a></li>
						<li><a href="'.DOMAIN_CHEQUE.'payee" title="Payee" alt="Payee"><i class="fa fa-users"></i><span>Payee</span></a></li>
						<li><a href="'.DOMAIN_CHEQUE.'cheque" title="Cheque" alt="Cheque"><i class="fa fa-money"></i><span>Cheques</span></a></li>
						
						
						<li class="visible-xs"><a href="'.DOMAIN_CHEQUE.'logout"><i class="fa fa-power-off"></i><span>Logout</span></a></li>
					</ul>
				</div>
			</div>
			<div class="text-right collapse-button" style="padding:7px 9px;">
				<button id="sidebar-collapse" class="btn btn-default" style=""><i style="color:#fff;" class="fa fa-angle-right"></i></button>
			</div>
		</div>
	</div>';
}
else {
	die();
}
//<li><a href="'.DOMAIN_CHEQUE.'banks" title="Bank" alt="Bank"><i class="fa fa-building-o"></i><span>Banking</span></a></li><li><a href="#" title="Master" alt="Master"><i class="fa fa-list"></i><span>Master</span></a><ul class="submenu-vnavigation"><li class="" title="Pay Mode" alt="Pay Mode"><a href="'.DOMAIN_CHEQUE.'payment-mode"><i class="fa fa-leaf"></i> <span>Payment Mode</span></a></li></ul></li>
?>
