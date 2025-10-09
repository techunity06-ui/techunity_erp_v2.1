<?php 
	session_start();
	include('../include/urlfile.php');
	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Drawing";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
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
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	ADMINISTRATOR_DRAWING_LIST,
        ADMINISTRATOR_DRAWING_CREATE
    ]);

    if(!in_array(ADMINISTRATOR_DRAWING_CREATE,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
	$companyConfiguration=getCompanyConfiguration($dbcon);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>DRAWING LIST</title>
<?php include_once($include.'include_css_file.php');?>

</head>
<body>
  <section id="container" >
      <?php include_once($include.'include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once($include.'left_menu.php');?>
      <!--sidebar end-->
      <!--main content start-->
           <section id="main-content">
			<section class="wrapper">
			
			<?php//include_once('../include/equick_link.php');?>
     		<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <section class="panel">
						<header class="panel-heading">
						  <h3><?=$mode.' '.$form?> List</h3>

						  
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
							  <li class="active"><?=$form?> list</li>
							 
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

				  		<div class='col-lg-4 col-md-4 col-xs-9'>
							    <div class="form-group">
                                  <label class="control-label col-lg-4 col-md-4 col-xs-3">Choose Date</label>
                                  <div class=" col-lg-8 col-md-8 col-xs-9">
                                       <div class="input-group date form_datetime-component">
									<?php 
									  //$start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
									?>
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
						<div class="col-md-4">
						
						<select class="select2" name="branch_id" id="branch_id" onchange="reload_data()" required <?php if($companyConfiguration['branch_wise_manage']=='0'){ ?>disabled<?php } ?>>
	                    								<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
														<?=getBranchBox_new($dbcon, $branch,'all');?>
	                							</select>
						
                            <?php //echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'reload_data()','4','6'); ?>
                        </div>
						<div class="col-md-4">
							<?php if(in_array(ADMINISTRATOR_DRAWING_CREATE,$bulkAccessArray)){ ?>	
								<span class="tools pull-right">
									<a href="<?=ROOT.ADMINISTRATION_ROOT.'drawing'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>					
								</span>
							<?php } ?>
						</div>
				 
					</header>	
					 <div class="panel-body">
					 
				<div class="adv-table">
				  <table class="display table table-bordered table-striped" id="dynamic-table">
						<thead>
						  <tr>
							<th>Drawing Number</th>
							<th>Drawing Tital</th> 
							<th>Vendor Name</th>
							<th>CDate</th>
							<th>Status</th>
							<th>Approve Status</th>
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
      <div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog custom-width">
               <div class="modal-content">
                  <div class="modal-header">
                  <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                     <h3 style="margin-top:-6px; important!">View Images</h3>
                  </div>
                  <div class="modal-body form">
                     <div class="form-group">
                       <!-- <div id="drawing_image_list"></div>-->
                        <div id="revision_image_list"></div>
                     </div>   
                  </div>
                  <div class="modal-footer">
                     <input type="hidden" name="edit_id" id="edit_id" value="" />
                     <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
                     
                  </div>
               </div>
            </div>
         </div>
      <!--main content end-->
      <!--footer start-->
	<?php include_once($include.'footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
   <script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/drawing.js?<?=time()?>"></script>
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
$(".select2").select2({
	width: '100%'
});
  $(".branch_validate").select2({
width: '100%'
}).on('change', function() {
$(this).valid();
});
</script>

  </body>
</html>