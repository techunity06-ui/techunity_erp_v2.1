<?php 
	session_start();
	include('../include/urlfile.php');
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
		<title>PURCHASE REPORT</title>
		<?php include_once($include.'/include_css_file.php');?>
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
		<section id="container" >
			<?php include_once($include.'/include_top_menu.php');?>
			<?php include_once($include.'/left_menu.php');?>
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
										$querymenu1re1="SELECT menumaster.*, CASE WHEN parent_id = 0 THEN id ELSE parent_id END AS sort FROM menu_master_access as menumaster WHERE menumaster.status=0 and menu_path='#purchase_report_list' ORDER BY sort, id";
										$result_menu1re1=$dbcon->query($querymenu1re1);		
										while($rel_men=mysqli_fetch_assoc($result_menu1re1))
										{
									?>
									<section class="panel">
										<header class="panel-heading">
											<div class="bio-graph-heading_new">
												<?=$rel_men['menu_name']?>
											</div>
										</header>	
							
									<div class="panel-body">
									
									<ul class="sub ulpad0">
										<?php 	
										$querymenu1re="SELECT menumaster.*,menuaccess.route_path_name	,menuaccess.id AS accessdata, CASE WHEN parent_id = 0 THEN menumaster.id ELSE parent_id END AS sort FROM menu_master_access AS menumaster
											LEFT JOIN menu_master_access_routes AS menuaccess ON menuaccess.access_id = menumaster.id
										 	WHERE menumaster.status=0 and menumaster.parent_id='".$rel_men['id']."' ORDER BY sort, menumaster.id";
										$result_menu1re=$dbcon->query($querymenu1re);		
										//echo $rel_menure['menu_id'];
										while($rel_menu1re=mysqli_fetch_assoc($result_menu1re))
										{
										?> 
											<?php if(in_array($rel_menu1re['accessdata'], $user_access_permission)){ ?>
												<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
												<li class="btn btn-shadow btn-info btn-lg btn-block btn-align">
												<a  class="two"  href="<?=ROOT.PURCHASE_ROOT.strtolower($rel_menu1re['route_path_name'])?>" target="_blank"><?=ucwords(strtolower($rel_menu1re['menu_name']))?></a></li>
												</div>
											<?php } ?>
									<?php } ?>
									</ul>
									</div>
									</section>
								<?php} ?>
								
							
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include.'/footer.php');?>
		</section>
		<?php include_once($include.'/include_js_file.php');?>   
	</body>
</html>
