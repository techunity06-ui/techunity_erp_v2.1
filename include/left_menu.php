<style>
.scrollbar
{
	height: 700px;
	width: 100%;
	overflow-y: scroll;
	margin-bottom: 25px;
}
.style-11::-webkit-scrollbar-track
{
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
	background-color: #F5F5F5;
	border-radius: 10px;
}
.style-11::-webkit-scrollbar
{
	width: 5px;
	background-color: #F5F5F5;
}
.style-11::-webkit-scrollbar-thumb
{
	background-color: #ff0000;
	border-radius: 10px;
	background-image: -webkit-linear-gradient(0deg,
	                                          rgba(255, 255, 255, 0.5) 25%,
											  transparent 25%,
											  transparent 50%,
											  rgba(255, 255, 255, 0.5) 50%,
											  rgba(255, 255, 255, 0.5) 75%,
											  transparent 75%,
											  transparent)
}
ul.sidebar-menu li a{
	font-size: 13px !important;
}
#sidebar .sub-menu > .sub li{
	padding-left: 25px !important;
}
ul.sidebar-menu li a span{
	display: initial !important;;
    word-break: break-all !important;
}
</style>
<?php //echo $menuList = getLeftMenu($dbcon,0); ?>
<aside>
  	<div id="sidebar"  class="nav-collapse scrollbar style-11">
      	<!-- sidebar menu start-->
	  	<ul class="sidebar-menu" id="nav-accordion">
    		<?php 
				echo $menuList = getLeftMenu($dbcon,0);
			?>
      	</ul>
  	</div>
</aside>          
			