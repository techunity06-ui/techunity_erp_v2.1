<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	$form="HRMS Masters";		
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php'); ?>
</head>
<body>
<section id="container">
  <?php include_once('../../include/include_top_menu.php'); ?>
      <!--sidebar start-->
      <?php include_once('../../include/left_menu.php'); ?>
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
								<li><a href="<?=ROOT . HRMS_ROOT .'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
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
					<?php
						//Get Crm Masters Fixed id 
						$pqry="select menu_id,page_name,menu_name from tbl_menu where status=0";
						$menu_rs = $dbcon->query($pqry);
						$menu_rs1 = $dbcon->query($pqry);
					?>
					<div class="col-md-12">
						<div class="col-md-6">
							<ul class="nav">
								<?php while($menu_rel = $menu_rs->fetch_assoc()){ ?>
									<?php if($menu_rel['page_name'] == 'hrms/hrms_emp_type'){ ?> 
										<li> 
											<a href="<?php echo ROOT.$menu_rel['page_name'] ?>"><i class="fa fa-angle-right" aria-hidden="true"></i><?=$menu_rel['menu_name']?></a> 
										</li>
									<?php } ?>
									<?php if($menu_rel['page_name'] == 'hrms/branch_mst'){ ?> 
										<li> 
											<a href="<?php echo ROOT.$menu_rel['page_name'] ?>"><i class="fa fa-angle-right" aria-hidden="true"></i><?=$menu_rel['menu_name']?></a> 
										</li>
									<?php } ?>
									<?php if($menu_rel['page_name'] == 'hrms/hrms_department'){ ?> 
										<li> 
											<a href="<?php echo ROOT.$menu_rel['page_name'] ?>"><i class="fa fa-angle-right" aria-hidden="true"></i><?=$menu_rel['menu_name']?></a> 
										</li>
									<?php } ?>
									<?php if($menu_rel['page_name'] == 'hrms/hrms_designation'){ ?> 
										<li> 
											<a href="<?php echo ROOT.$menu_rel['page_name'] ?>"><i class="fa fa-angle-right" aria-hidden="true"></i><?=$menu_rel['menu_name']?></a> 
										</li>
									<?php } ?>
									<?php if($menu_rel['page_name'] == 'hrms/hrms_emp_grade'){ ?> 
										<li> 
											<a href="<?php echo ROOT.$menu_rel['page_name'] ?>"><i class="fa fa-angle-right" aria-hidden="true"></i><?=$menu_rel['menu_name']?></a> 
										</li>
									<?php } ?>
									<?php if($menu_rel['page_name'] == 'hrms/group_list'){ ?> 
										<li> 
											<a href="<?php echo ROOT.$menu_rel['page_name'] ?>"><i class="fa fa-angle-right" aria-hidden="true"></i><?=$menu_rel['menu_name']?></a> 
										</li>
									<?php } ?>
									<?php if($menu_rel['page_name'] == 'hrms/hrms_emp_health_insurance'){ ?> 
										<li> 
											<a href="<?php echo ROOT.$menu_rel['page_name'] ?>"><i class="fa fa-angle-right" aria-hidden="true"></i><?=$menu_rel['menu_name']?></a> 
										</li>
									<?php } ?>
								<?php } ?>	
							</ul>
						</div>
						<div class="col-md-6">
							<ul class="nav">
								<?php while($menu_rel1 = $menu_rs1->fetch_assoc()){ ?>
									<?php if($menu_rel1['page_name'] == 'hrms/hrms_shift_type'){ ?> 
										<li> 
											<a href="<?php echo ROOT.$menu_rel1['page_name'] ?>"><i class="fa fa-angle-right" aria-hidden="true"></i><?=$menu_rel1['menu_name']?></a> 
										</li>
									<?php } ?>
									<?php if($menu_rel1['page_name'] == 'hrms/hrms_letter_head_list'){ ?> 
										<li> 
											<a href="<?php echo ROOT.$menu_rel1['page_name'] ?>"><i class="fa fa-angle-right" aria-hidden="true"></i><?=$menu_rel1['menu_name']?></a> 
										</li>
									<?php } ?>
									<?php if($menu_rel1['page_name'] == 'hrms/hrms_sms_template_list'){ ?> 
										<li> 
											<a href="<?php echo ROOT.$menu_rel1['page_name'] ?>"><i class="fa fa-angle-right" aria-hidden="true"></i><?=$menu_rel1['menu_name']?></a> 
										</li>
									<?php } ?>
									<?php if($menu_rel1['page_name'] == 'hrms/hrms_email_template_list'){ ?> 
										<li> 
											<a href="<?php echo ROOT.$menu_rel1['page_name'] ?>"><i class="fa fa-angle-right" aria-hidden="true"></i><?=$menu_rel1['menu_name']?></a> 
										</li>
									<?php } ?>
									<?php if($menu_rel1['page_name'] == 'hrms/hrms_settings_list'){ ?> 
										<li> 
											<a href="<?php echo ROOT.$menu_rel1['page_name'] ?>"><i class="fa fa-angle-right" aria-hidden="true"></i><?=$menu_rel1['menu_name']?></a> 
										</li>
									<?php } ?>
								<?php } ?>		
							</ul>
						</div>
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
	<?php include_once('../../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php');?>  
    <!--<script src="js/count.js"></script>-->
</body>
</html>