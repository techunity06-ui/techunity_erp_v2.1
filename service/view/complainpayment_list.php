<?php 
	session_start();
	include('../include/urlfile.php');
	$incPath = $path.'include/';
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Complain Payment";
	if(empty($_SESSION['start']))
	{
		//$start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
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
<?php include_once($incPath.'include_css_file.php');?>
</head>
<body>
  <section id="container" >
      <?php include_once($incPath.'include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once($incPath.'left_menu.php');?>
      <!--sidebar end-->
      <!--main content start-->
           <section id="main-content">
          <section class="wrapper">
			<?php 
				include_once($incPath.'quick_link.php');
				?>				
		<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <section class="panel">
					  <header class="panel-heading">
						<h3 style="float:left;"> <?=$mode .' '.$form?></h3>
						<?php include_once($incPath."head_menu.php") ?>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li ><a href="<?=ROOT.'invoicepaymentreceipt_list'?>">Complain Payment list</a></li>
							 
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
					<div class='col-md-5'>
					<div class="form-group">
                                  <label class="control-label col-md-4">Choose Date</label>
                                  <div class="col-md-8">
                                       <div class="input-group date form_datetime-component">
										<input type="hidden" id="from_date"  value="<?=$start?>">
										 <input type="hidden" id="to_date"  value="<?=$end?>">
         					 		        <input type="text" id="rep_date"  onChange="reload_data();;" class="form-control datepikerdemo" value="">
											<span class="input-group-btn">
											<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
											</span>
                                      </div>
                                  </div>
                              </div>
						</div>	
					
					 <span class="tools pull-right">
					<a href="<?=ROOT.SERVICE_ROOT.'complainpayment'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>					
				 </span>
				 
					</header>	
					 <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  <thead>
				  <tr>
					  <th>Sr. NO.</th>
					  <th>Date</th>
					  <th>Payment No</th>
					  <th>Reference No</th>
					  <th>Customer Name</th>
					  <th>Mode</th>
					  <th>Amount</th>
					  <th class="hidden-phone">Action</th>					  
				  </tr>
				  </thead>
				  <tbody>
				  </tbody>				 
				  </table>
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
	<?php include_once($incPath.'footer.php');
		//include_once($include1.'modal_use_credit_amount.php');
	?>
      <!--footer end-->
  </section>
  
    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($incPath.'include_js_file.php');?>   
   <script src="<?=ROOT?><?=SERVICE_ROOT?>js/app/complainpayment.js?<?=time()?>"></script>
    <!--<script src="js/count.js"></script>-->
	
<script>
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
