<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Masters List";		
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>ADMINISTRATOR</title>
	<?php include_once('../include/include_css_file.php'); ?>
	<style>
			a.two:hover {/*font-size:110%;*/color:#210e46;}
			.btn-align{
				text-align: left;
				color: #fff;
			}
			.btn-align a{
				color: #fff;
					display: block;
			}
			a.two:hover {
			    color: #fff;
			}
			.btn-info {
			    color: #fff;
			    background-color: #5b95de;
			    border-color: #5b95de;
			}
			.btn-info.active, .btn-info.focus, .btn-info:active, .btn-info:focus, .btn-info:hover, .open>.dropdown-toggle.btn-info {
			    color: #fff;
			    background-color: #5b95de;
			    border-color: #5b95de;
			}
			.btn-align a:before{
				content:'\203A';
				font-size: 22px;padding-right:10px;
				text-align: center;
				
			}
			.bio-graph-heading_new {
			    background: #6883a3;
			    color: #fff;
			    text-align: center;	
			    padding: 7px 36px;
			    border-radius: 4px 4px 0 0;
			    -webkit-border-radius: 4px 4px 0 0;
			    font-size: 30px;
			    font-weight: 600;
			}
		</style>
</head>

<body>
<section id="container">
  <?php include_once('../include/include_top_menu.php'); ?>
      <!--sidebar start-->
      <?php include_once('../include/left_menu.php'); ?>
      <!--sidebar end-->
      <!--main content start-->
	   <section id="main-content">
          <section class="wrapper">
		            
			<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
						  <h3><?=$form?></h3>
						</header>	
						<div class="">
							<ul class="breadcrumb">
								<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
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
					<header class="panel-heading">
						<?=$form?>
					</header>	
	
				<div class="panel-body">
					<div class="row">
						<div class="col-sm-12">
							<?php 
								$user_access_permission = [];
								$quserdata = $dbcon->query("SELECT * FROM `users` WHERE `user_id` = '".$_SESSION['user_id']."'");
								$recorduserData = $quserdata->fetch_assoc();
								if(isset($recorduserData['user_access_permission']) && $recorduserData['user_access_permission'] != ''){
									$user_access_permission = explode(",",$recorduserData['user_access_permission']);
								}

								$quserdata1 = $dbcon->query("SELECT * FROM `menu_master_access`");


								foreach($quserdata1 as $menu){
										if($menu['menu_name'] == 'SERVICE MASTER' || $menu['menu_name'] == 'PRODUCTION MASTER' || $menu['menu_name'] == 'FINANCE MASTER' || $menu['menu_name'] == 'COMPANY MASTER' ){
												$arrayReport_1[] = $menu['id'];
												$arrayReport_2[] = $menu['menu_name'];
												$arrayReport = array_combine($arrayReport_1,$arrayReport_2);
										}
								}
								//$arrayReport = array('479'=>'SERVICE MASTER','480'=>'PRODUCTION MASTER','481'=>'FINANCE 
								foreach ($arrayReport as $key=>$arrayValue) { ?>
									<section class="panel">
										<header class="panel-heading">
											<div class="bio-graph-heading_new" style="font-style: normal;">
												<?=$arrayValue?>
											</div>
										</header>
										<div class="panel-body">
											<ul class="sub ulpad0">
												<?php
												 $querymenu1re="SELECT menumaster.*,menuaccess.route_path_name	,menuaccess.id AS accessdata, CASE WHEN parent_id = 0 THEN menumaster.id ELSE parent_id END AS sort FROM menu_master_access AS menumaster
														LEFT JOIN menu_master_access_routes AS menuaccess ON menuaccess.access_id = menumaster.id
														WHERE menumaster.status=0 and menumaster.parent_id='".$key."' and menuaccess.access_type in('R','V')  group by menu_name ORDER BY sort, menumaster.menu_name";
														// echo $querymenu1re;
												$result_menu1re=$dbcon->query($querymenu1re);
												while($rel_menu1re=mysqli_fetch_assoc($result_menu1re))
													{
													if(in_array($rel_menu1re['accessdata'], $user_access_permission)){
												?>
													<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
														<li class="btn btn-shadow btn-info btn-lg btn-block btn-align">
															<a  class="two"  href="<?=ROOT.strtolower($rel_menu1re['route_path_name'])?>"><?=ucwords(strtolower($rel_menu1re['menu_name']))?></a>
														</li>
													</div>
												<?php }} ?>
											</ul>
										</div>
									</section>
							<?php } ?>
									
						</div>
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
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>  
    <!--<script src="js/count.js"></script>-->
</body>
</html>