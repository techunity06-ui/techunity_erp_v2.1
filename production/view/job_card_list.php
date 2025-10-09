<?php 
	session_start();
	include('../include/urlfile.php');	
	// error_reporting(E_ALL);
	$form="Job Card";
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
	//echo rand(0,100);

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    PRODUCTION_JOBCARD_LIST_SLUG_VIEW, PRODUCTION_JOBCARD_LIST_SLUG_CREATE
	]);

	if(!in_array(PRODUCTION_JOBCARD_LIST_SLUG_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
	$branchid=$_SESSION['branch_id'];
	$branch_style="";
	if($branchid!=0){
		$branch_style="Display:none;";
	}
	$companyConfiguration=getCompanyConfiguration($dbcon);
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>JOBCARD LIST</title>
		<?php include_once('../../include/include_css_file.php');?>
		<link href="<?= ROOT ?>assets/sweetalert2/sweetalert2.min.css" rel="stylesheet">
		<!-- <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/> -->
		<style>
.icons{
    width: 14.5%;
    float: left;
    margin: 30px 7px 25px;
    text-align: center;
	position:relative;

}
.icons12{
background-color:#fff;
padding-top:15px;
    border: 8px;
}
 .icons p{
 text-align:center;
 font-size:15px;
 font-weight:600;
 padding-top:5px;
 font-color:white
 
 }
 
 .icon1 fa{

 }
 .icon1.success{background-color: #5cb85c;}
 .icon1.primary{background-color: #0275d8;}
 .icon1.warning{background-color: #f0ad4e;}
 .icon1.info{background-color: #5bc0de;}
 .icon1.danger{background-color: #d9534f;}
 .icon1.terques{background-color: #6ccac9;}
 .icon1.yellow{background-color: #f8d347;}
 .icon1.pink{background-color:#E5649A;}
 .icon1.mustard{background-color:#F0BD23;}
 .icon1.success,.icon1.primary,.icon1.warning,.icon1.danger,.icon1.info,.icon1.terques,.icon1.yellow,.icon1.pink,.icon1.mustard{
    width: 120px;
    height:100px;
    border-radius: 8px;
	text-align:center;
	margin:0 auto
 }
 .icon1.success i,.icon1.primary i,.icon1.warning i,.icon1.danger i,.icon1.info i,.icon1.terques i,.icon1.yellow i,.icon1.pink i,.icon1.mustard i{
 text-align:center;
 color:#fff;
     padding-top: 27%;
	font-size: 37px;
 }
 @media (max-width:767px){
.icons {
    width: 47%;
    float: left;
    margin: 30px 4px 25px;
	position:relative;
}

}
@media (min-width:768px) and (max-width:980px)
 {
 .icons12{
background-color:#fff;
padding-top:20px;
padding-bottom:20px;
   border-radius: 8px;
}
 .icons {
    width: 17%;
    float: left;
    margin: 30px 4px 25px;
    text-align: center;
    position: relative;
}

 }
.icons .badge {
    position: absolute;
    right: 25px;
    top: 0px;
    z-index: 100;
}

.error {
				font-weight: bold;
				color: #ef1717;
				
				font-size: 16px;
			}
			 #process_left,#process_right{
   margin: 5px;
    border: 1px solid #cccccc;
    list-style: none;
    padding-left: 0;
    height: 200px;
    overflow: auto;
    /* width: 250px; */
    border-radius: 5px;
  }
.mb-5{
	margin-bottom: 5px;
}
  ul li{
    cursor: pointer;
    padding: 5px 10px;
  }


  .selected{
    background-color: blue;
    color: white;
     margin: 2px;
  }

  .bigBtn{
    height: 50px;
    width: 55px;
    margin-top: 35px;
    margin-left: -5px;
    font-size: 20px;
    font-weight: 900;
  }

</style>
	</head>
	<body>
		<section id="container" >
			<?php include_once('../../include/include_top_menu.php');?>
			<?php include_once('../../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$form?> List</h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li class="active"><?=$form?> list</li>
									</ul>
								</div>
							</section>
							<section class="panel">
			<div class="row">
			  <div class="col-lg-12 centeral-align">
				  <div class="icons">
			
				<div class="icon1 success" >
				<p style="color:white;padding-top:10px;">Total Job Card</p>
					<h3 style="font-size:20px;color:white;padding-top:5px;">10<span id="tpurchaseamount" style="font-size:20px;color:white;"></span> </h3>
				</div>
				
			</div>
				<!--<div class="icons">	 	
				<div class="icon1 info" >
					
						<p style="color:white;padding-top:10px;">Total Job<br> Card Value</p>
					
						<h3 style="font-size:20px;color:white;padding-top:5px;">Rs.74367<span id="tpurchasetax" style="font-size:20px;color:white"></span></h3>
				
				</div>
				</div>-->
			<div class="icons">	 	
				<div class="icon1 danger" >
					
						<p style="color:white;padding-top:10px;">Total Job Card Panding</p>
					
						<h3 style="font-size:20px;color:white;padding-top:5px;">5<span id="tpaidamount" style="font-size:20px;color:white"></span></h3>
				
				</div>
				</div>
				<div class="icons">	 	
				<div class="icon1 warning" >
					
						<p style="color:white;padding-top:10px;">Total Job Card Outstanding</p>
					
						<h3 style="font-size:20px;color:white;padding-top:5px;">5<span id="toutstanding" style="font-size:20px;color:white"></span></h3>
				
				</div>
				</div>
				</div>	
             </div>
					</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									<div class='col-md-4'>
										<div class="form-group">
											<label class="control-label col-md-3" style="white-space:nowrap;">Choose Date</label>
											<div class="col-md-9">
												<div class="input-group date form_datetime-component">
													<input type="hidden" id="from_date" value="<?=$start?>">
													<input type="hidden" id="to_date" value="<?=$end?>">
													<input type="text" id="rep_date" onChange="reload_data();" class="form-control datepikerdemo" value="">
													<span class="input-group-btn">
														<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
													</span>
												</div>
											</div>
										</div>
									</div>	
									<div class="col-md-6">
										<div class="col-md-3">
											<label for="po_type_status1" class="external-event label label-primary ui-draggable" style="position: relative;cursor:pointer;">All</label>
											<input id="po_type_status1" name="po_type_status" type="radio" onClick="reload_data();" class="" title="All" value="1,3">
										</div>
										<div class="col-md-3">
											<label for="po_type_status3" class="external-event label label-warning ui-draggable" style="position: relative;cursor:pointer;">Pending</label>
											<input id="po_type_status3" name="po_type_status" checked onClick="reload_data();" type="radio" class="" title="Pending" value="1" />
										</div>
										<div class="col-md-3">
											<label for="po_type_status2" class="external-event label label-success ui-draggable" style="position: relative;cursor:pointer;">Done</label>
											<input id="po_type_status2" name="po_type_status" onClick="reload_data();" type="radio" class="" title="Done" value="3" />
										</div>
									</div>
									<?php if(in_array(PRODUCTION_JOBCARD_LIST_SLUG_CREATE,$bulkAccessArray)){ ?>
									<span class="tools pull-right">
										<a href="<?=ROOT.PRODUCTION_ROOT.'job_card_add'?>" ><button class="btn btn-success btn-flat" >Create Job Card</button></a>
									</span>	
									<?php } ?>		 
								</header>	
								<div class="panel-body">
									<div class="col-md-12" style="margin-top: 10px;<?=$branch_style?>">
										<?php if($companyConfiguration['branch_wise_manage']==1){?>
									<div class="col-md-6">
										<?php echo getBranchBox($dbcon, $branch_id, '', false, true, 'reload_data(this.value)','3','9'); ?>	
									</div>
							<?php }else{ ?>
                                       <input type="hidden" name="branch_id" id="branch_id" value="<?=$companyConfiguration['default_branch_id']?>" />
                                    <?php } ?>
									</div>
									<div class="adv-table">
										<table class="display table table-bordered table-striped" id="po-req-table">
											<thead>
												<tr>
													<th>#</th>
													<th>Job Card No</th>
													<th>Job Card Date</th>
													<th>WorkOrder No</th>
													<th>Sales Order No</th>
													<th>Product Name</th>
													<th>Product Category</th>
													<th>Total Qty</th>
													<th>Priority</th>
													<!-- <th>Unit</th>
													<th>Progress Bars</th> -->
													<?php if($_SESSION['branch_id']==0){ ?>
														<th>Branch Name</th>
													<?php } ?>
													<th class="hidden-phone">Action</th>
												</tr>
											</thead>
											<tbody></tbody>
										</table>
									</div>
								</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once('../../include/footer.php');?>
		</section>
		<style>
			.ui-dialog .ui-dialog-content {
				background-color: #5495ce !important;
				color: #FFF !important;
				font-size : 16px !important;
				}
				.ui-dialog .ui-dialog-titlebar {
				background: none !important;
				background-color: #5495ce !important;
				color: #FFF !important;
				font-size : 20px !important;
				}
				.ui-widget-content {
				background: none !important;
				background-color: #5495ce !important;
				color: #FFF !important;
				font-size : 20px !important;
				}
		</style>
		<?php include_once('../../include/include_js_file.php');?>   
		<script type='text/javascript' src='<?= ROOT ?>assets/sweetalert2/sweetalert2.all.min.js'></script>
		<?php include_once($include1.'job_card_vendor_change_modal.php');?> 
		<?php include_once($include1.'job_card_process_transfer_modal.php');?>  
		<?php include_once($include1.'job_card_multi_process_transfer_modal.php');?>   
		<?php include_once($include1.'bom_document_view_model.php');?>
		<?php include_once($include1.'wo_process_add_model.php');?>  
		
		<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/job_card_list.js?<?=time()?>"></script>
		 <script src="<?=ROOT?>js/advanced-form-components.js"></script>

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
			$(".select2").select2({
				width: '100%'
			});
		</script>
	</body>
</html>
