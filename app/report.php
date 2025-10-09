<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../include/coman_function.php");
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
	
}</style>
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
			
			<?php //include_once('../include/equick_link.php');?>
     		<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <!--<section class="panel">
					  <header class="panel-heading">
						  <h3><?=$mode.' '.$form?> List</h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active"><?=$form?> list</li>
							 
						  </ul>
						 </div>
					</section>-->
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--state overview start-->
		  <div class="row">			
			<div class="col-sm-12">
				<section class="panel">
				  <header class="panel-heading">
				 
				  </header>	
					 <div class="panel-body">
					        <ul class="pad_l_0 ulpad0">		 
            <?php 
			 /* $querymenure="select * from tbl_menu as menu inner join tbl_permission as per on per.menu_id=menu.menu_id inner join tbl_usertype as type on type.usertype_id=per.usertype_id where menu.status=0 and pid=0 and per.usertype_id=".$_SESSION['user_type']." order by menuorder";
			$result_menure=$dbcon->query($querymenure);		
			while($rel_menure=mysqli_fetch_assoc($result_menure))
			{
				if(!empty($rel_menure['page_name']))
				{
				?>
					<li>
						<a class="" href="<?=ROOT.strtolower($rel_menure['page_name'])?>">
							<i class="fa <?=strtolower($rel_menure['fa_icon'])?>"></i>
					<span style="font-size:14px"><?=ucwords(strtolower($rel_menure['menu_name']))?></span>
					</a>
				<?php 
				}
				else
				{
				?>
			      <li class="sub-menu">
			  <a href="javascript:;" >
					<i class="fa <?=strtolower($rel_menure['fa_icon'])?>"></i>
					<span style="font-size:14px"><?=ucwords(strtolower($rel_menure['menu_name']))?></span>
					</a>*/
					?>
					<ul class="sub ulpad0">
				<?php 	
					 $querymenu1re="select * from tbl_menu as menu inner join tbl_permission as per on per.menu_id=menu.menu_id inner join tbl_usertype as type on type.usertype_id=per.usertype_id where menu.status=0 and pid=7 and per.usertype_id=".$_SESSION['user_type']." order by menuorder";
					$result_menu1re=$dbcon->query($querymenu1re);		
					echo $rel_menure['menu_id'];
					while($rel_menu1re=mysqli_fetch_assoc($result_menu1re))
					{
				?> 
				    <div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
					<li class="btn btn-shadow btn-info btn-lg btn-block btn-align">
					<!--<i class="fa fa-angle-right" style="font-size:20px;font-color:#337ab7;"></i>-->
					<a  class="two"  href="<?=ROOT.strtolower($rel_menu1re['page_name'])?>" target="_blank"><?=ucwords(strtolower($rel_menu1re['menu_name']))?></a></li>
					</div>
				<?php } ?>
				</ul>
				<!--</li>-->
				<?php //}?>
					
            <!--</li>-->	
			<?php 	//} ?>
				 	
				<!--<li>
                 	   <a class="" href="<?=ROOT.'changepassword/'.$_SESSION['user_id'] ?>">
                         <i class="fa fa-cog"></i>
                          <span style="font-size:14px">Change Password</span>
						</a>
                </li>
				
				<li>
                      <a href="javascript:;" onclick="change_company()">
                          <i class="fa fa-sign-in"></i>
                          <span style="font-size:14px">Change Company</span>
                      </a>
                  </li>-->
				</ul>
				  
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
  <!-- <script src="js/app/po.js"></script>-->
    <!--<script src="js/count.js"></script>-->
	<script>
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
