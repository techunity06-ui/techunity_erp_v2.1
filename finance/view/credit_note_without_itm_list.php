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
	$form="Credit Note Without Item";
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
		<title>CREDIT NOTE WITH OUT ITEM LIST</title>
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
									  <li><a href="<?=ROOT.FINANCE_ROOT.'credit_note_without_itm_list'?>"><?=$form?> list</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading respadlr0">
									
                                                                        
                                <?php if(in_array(FINANCE_JOURNAL_CREATE,$bulkAccessArray)){ ?>
                                    <span class="tools pull-right respadr_15">
                                            <a href="<?=ROOT.FINANCE_ROOT.'credit_note_without_itm'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>
                                    </span>
                                <?php } ?>
								</header>	
								<div class="panel-body">
									<div class="adv-table">
										<table  class="display table table-bordered table-striped" id="credit_note-table">
											<thead>
												<tr>
													<th>Credit Note Entry No</th>
													<th>Credit Note Entry Date</th>                                                                         <?php if(in_array(FINANCE_JOURNAL_EDIT,$bulkAccessArray) && in_array(FINANCE_JOURNAL_DELETE,$bulkAccessArray)) { ?>
													<th class="hidden-phone">Action</th>                                                           <?php } ?>
												</tr>
											</thead>
											<tbody></tbody>				 
										</table>
										<style>
										  @media screen and (max-width:992px){
											#credit_note-table td:before{
													color:red
												}
											#credit_note-table td:nth-of-type(1):before { content: "Journal Entry No:"; }
											#credit_note-table td:nth-of-type(2):before { content: "Journal Entry Date:"; }
											#credit_note-table td:nth-of-type(3):before { content: "Action:"; }
											
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
		<script src="<?=ROOT.FINANCE_ROOT?>js/app/credit_note_without_itm.js?<?=time()?>"></script>
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
