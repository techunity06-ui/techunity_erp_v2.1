<?php 
session_start();
include('../include/urlfile.php');
$form="CRM Masters";		
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>CRM MASTER</title>
	<?php include_once($include.'include_css_file.php'); ?>
</head>
<body>
	<section id="container">
		<?php include_once($include.'include_top_menu.php'); ?>
		<!--sidebar start-->
		<?php include_once($include.'left_menu.php'); ?>
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
									<?
							//Get Crm Masters Fixed id 
									$user_access_permission = [];
									$quserdata = $dbcon->query("SELECT * FROM `users` WHERE `user_id` = '".$_SESSION['user_id']."'");
									$recorduserData = $quserdata->fetch_assoc();
									if(isset($recorduserData['user_access_permission']) && $recorduserData['user_access_permission'] != ''){
										$user_access_permission = explode(",",$recorduserData['user_access_permission']);
									}	
									$pqry="SELECT menumaster.*, CASE WHEN parent_id = 0 THEN id ELSE parent_id END AS sort FROM menu_master_access as menumaster WHERE menumaster.status=0 and menu_path='#crm-masters' ORDER BY sort, id";
									$result_menu1re1=$dbcon->query($pqry);		
									while($rel_men=mysqli_fetch_assoc($result_menu1re1)){
										
										$menu_qry="SELECT menumaster.*,menuaccess.route_path_name	,menuaccess.id AS accessdata, CASE WHEN parent_id = 0 THEN menumaster.id ELSE parent_id END AS sort FROM menu_master_access AS menumaster
										LEFT JOIN menu_master_access_routes AS menuaccess ON menuaccess.access_id = menumaster.id
										WHERE menumaster.status=0 and menumaster.parent_id='".$rel_men['id']."' GROUP BY menumaster.id ORDER BY sort, menumaster.id";
										$menu_rs=$dbcon->query($menu_qry);
										while($menu_rel=mysqli_fetch_assoc($menu_rs)){
											?>
											<?php if(in_array($menu_rel['accessdata'], $user_access_permission)){ ?>
												<div class="col-md-6 report-section">
													<ul class="nav"> 
														<li> 
															<a href="<?=ROOT.$menu_rel['route_path_name']?>"><i class="fa fa-angle-right" aria-hidden="true"></i> <?=$menu_rel['menu_name']?></a> 
														</li> 
													</ul>
												</div>
											<?php} ?>	
										<?php}} ?>	
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
			<?php include_once($include.'footer.php');?>
			<!--footer end-->
		</section>

		<!-- js placed at the end of the document so the pages load faster -->
		<?php include_once($include.'include_js_file.php');?>  
		<!--<script src="js/count.js"></script>-->
	</body>
	</html>