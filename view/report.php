<?php 
	session_start();
	/*var_dump($_SESSION);exit;*/
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Purchase Order";
	if(empty($_SESSION['start']))
	{
		$start=date('1-m-Y');
		$end=date("d-m-Y");
	}
	else
	{
		$start=$_SESSION['start'];
		$end=$_SESSION['end'];
	}
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>MIS REPORT</title>
		<?php include_once('../include/include_css_file.php');?>
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

			.btn-align a:before{
				content:'\203A';
				font-size: 22px;padding-right:10px;
				text-align: center;
				
			}
			.bio-graph-heading_new {
				background: #41cac0;
				color: #fff;
				text-align: center;
				font-style: italic;
				padding: 16px 36px;
				border-radius: 4px 4px 0 0;
				-webkit-border-radius: 4px 4px 0 0;
				font-size: 30px;
				font-weight: 600;
			}
		</style>
	</head>
	<body>
		<section id="container" >
			<?php include_once('../include/include_top_menu.php');?>
			<?php include_once('../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12"></div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
									<?php 
										$user_access_permission = [];
										$quserdata = $dbcon->query("SELECT * FROM `users` WHERE `user_id` = '".$_SESSION['user_id']."'");
										$recorduserData = $quserdata->fetch_assoc();
										if(isset($recorduserData['user_access_permission']) && $recorduserData['user_access_permission'] != ''){
											$user_access_permission = explode(",",$recorduserData['user_access_permission']);
										}

										$quserdata1 = $dbcon->query("SELECT * FROM `menu_master_access` WHERE status=0 and parent_id=0");
										foreach($quserdata1 as $menu){
												if($menu['menu_name'] == 'CRM REPORTS LIST' || $menu['menu_name'] == 'INVENTORY REPORTS LIST' || $menu['menu_name'] == 'PRODUCTION REPORTS LIST' || $menu['menu_name'] == 'FINANCE REPORTS LIST' || $menu['menu_name'] == 'PURCHASE REPORTS LIST' || $menu['menu_name'] == 'SERVICE REPORTS LIST'){
														$arrayReport_1[] = $menu['id'];
														$arrayReport_2[] = $menu['menu_name'];
														$arrayReport = array_combine($arrayReport_1,$arrayReport_2);
												}
										}
										//$arrayReport = array('163'=>'CRM REPORTS','196'=>'STOCK REPORTS','193'=>'PRODUCTION REPORTS','199'=>'FINANCE REPORTS','192'=>'PURCHASE REPORTS', '198'=>'SERVICE REPORTS');
										foreach ($arrayReport as $key=>$arrayValue) { 
											$root = '';
											if($arrayValue == 'PURCHASE REPORTS LIST'){
												$root = PURCHASE_ROOT;
											}

											?>
											
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
															 	WHERE menumaster.status=0 and menumaster.parent_id='".$key."' ORDER BY sort, menumaster.id";
														$result_menu1re=$dbcon->query($querymenu1re);
														while($rel_menu1re=mysqli_fetch_assoc($result_menu1re))
															{
															if(in_array($rel_menu1re['accessdata'], $user_access_permission)){
														?>
															<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
																<li class="btn btn-shadow btn-info btn-lg btn-block btn-align">
																	<a  class="two"  href="<?=ROOT.$root.strtolower($rel_menu1re['route_path_name'])?>" target="_blank"><?=ucwords(strtolower($rel_menu1re['menu_name']))?></a>
																</li>
															</div>
														<?php }} ?>
													</ul>
												</div>
											</section>
									<?php } ?>
									
						</div>
					</div>
				</section>
			</section>
			<?php include_once('../include/footer.php');?>
		</section>
		<?php include_once('../include/include_js_file.php');?>   
	</body>
</html>
