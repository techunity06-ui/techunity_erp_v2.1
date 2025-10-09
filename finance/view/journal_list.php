<?php 
	session_start();
	$path = '../../';
	$include1 = '../include/';
	$include = '../../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
        $branch_id = $_SESSION['branch_id'];
	$form="Journal Voucher";
	if(empty($_SESSION['start']))
	{
		$start = date('1-m-Y');
		$end = date("d-m-Y");
	}
	else
	{
		$start = $_SESSION['start'];
		$end = $_SESSION['end'];
	}
        
        $bulkAccessArray = canCheckPermissionAccess($dbcon, [
            FINANCE_JOURNAL_CREATE,
            FINANCE_JOURNAL_LIST,
            FINANCE_JOURNAL_EDIT,
            FINANCE_JOURNAL_DELETE
        ]);
        if(!in_array(FINANCE_JOURNAL_LIST,$bulkAccessArray)){
            header("Location: ".DOMAIN."permission_access");
        }
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>JOURNAL VOUCHER LIST</title>
		<?php include_once($include.'include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
								  <h3><?=$mode.' '.$form?> List</h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li><a href="<?=ROOT.FINANCE_ROOT.'journal_list'?>"><?=$form?> list</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading respadlr0">
									<div class='col-lg-5 col-md-7 col-xs-12 '>
										<div class="form-group">
											<label class="control-label col-lg-4 col-md-4 col-xs-12 respad-l0">Choose Date</label>
											<div class=" col-lg-8 col-md-8 col-xs-12 respad-r0">
												<div class="input-group date form_datetime-component">
													<input type="hidden" id="from_date"  value="<?=$start?>">
													<input type="hidden" id="to_date"  value="<?=$end?>">
													<input type="text" id="rep_date"  onChange="reload_data();" class="form-control datepikerdemo" value="">
													<span class="input-group-btn">
														<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
													</span>
												</div>
											</div>
										</div>
									</div>
             <div class="col-md-5">
                 <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'reload_data()'); ?>
             </div>
             <?php if(in_array(FINANCE_JOURNAL_CREATE,$bulkAccessArray)){ ?>
                 <span class="tools pull-right respadr_15">
                         <a href="<?=ROOT.FINANCE_ROOT.'journal_entry'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>
                 </span>
             <?php } ?>
								</header>	
								<div class="panel-body">
									<div class="adv-table">
										<table  class="display table table-bordered table-striped" id="purchase-table">
											<thead>
												<tr>
													<th>Journal Entry No</th>
													<th>Journal Entry Date</th>
													<th>Ledger Name</th>
													<th> Amount </th>
													<th> Remark </th>
													<th class="hidden-phone">Action</th></tr>
											</thead>
											<tbody></tbody>				 
										</table>
										<style>
										  @media screen and (max-width:992px){
											#purchase-table td:before{
													color:red
												}
											#purchase-table td:nth-of-type(1):before { content: "Journal Entry No:"; }
											#purchase-table td:nth-of-type(2):before { content: "Journal Entry Date:"; }
											#purchase-table td:nth-of-type(3):before { content: "Action:"; }
											
											}
										</style>
									</div>
								</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include.'include/footer.php');?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.FINANCE_ROOT?>js/app/journal_entry.js?<?=time()?>"></script>
		<script>
                        $(".select2").select2({
                            width: '100%'
                        });
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
				  // 'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				   //'Last 7 Days': [moment().subtract(6, 'days'), moment()],
				   //'Last 30 Days': [moment().subtract(29, 'days'), moment()],
				   'This Month': [moment().startOf('month'), moment().endOf('month')],
				   'Last 3 Month': [moment().subtract(3, 'months'), moment().endOf('month')],
				   'Last 6 Month': [moment().subtract(6, 'months'), moment().endOf('month')],
				   'Last 1 Year': [moment().subtract(12, 'months'), moment().endOf('month')],
				   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
				}
			}, cb);
			$('.date-set').click(function(){
				   $('.datepikerdemo').trigger('click');
			});
		</script>
	</body>
</html>
