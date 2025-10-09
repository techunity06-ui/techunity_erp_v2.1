<?php 
	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Category Wise Min Max Planning";
	$product_category=$dbcon->real_escape_string($_REQUEST['product_category']);
	//echo $type;
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
	$branch_id = $_SESSION['branch_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
		<title>MIN MAX PLANNING</title>
		<?php include_once($include.'include_css_file.php');?>
</head>
<body>
  <section id="container" >
     <?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
      <!--sidebar end-->
      <!--main content start-->
           <section id="main-content">
          <section class="wrapper">
			<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
							
							<!-- <input type="hidden" class="form-control" name="product_category" id="product_category" value="<?=$product_category;?>" /> -->
							
							<h3><?=$form?> List</h3>
							
						</header>
						<div class="">
							<ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active"><?=$form?> List</li>
							</ul>
						</div>
					</section>
				  <!--breadcrumbs end -->
					  </div>
			  		  </div>
			  <!--state overview start-->
		  <div class="row ">			
			<div class="col-sm-12">
				<section class="panel">
					<div class="col-md-12 mtop20">
						<div class="col-md-6">
						
							<?php echo getBranchBox($dbcon, $branch_id,"", false, false, 'show_data();'); ?>
						
						</div>
													<div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="product_category" class="col-md-4 control-label">Select Category</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <select class="select2" disabled name="product_category" id="product_category">
                                                                <?= get_all_category($dbcon, @$product_category); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
					</div>
											<div class="col-md-12 mtop20">
												<div class="col-md-6">
												<div class="form-group">
                                                <label class="col-md-4 control-label">Series * </label>
                                                <div class="col-md-8 col-xs-11">
                                                   <select <?= $disable ?> class="select2" name="invoicetype_id" id="invoicetype_id" onchange="load_docno(this.value)" required>
                                                        <option value="">--Select Series--</option>
                                                        <?=get_invoice_type_list($dbcon,55,$rel['invoicetype_id'])?>
                                                   </select>
                                                </div>
												</div>
												</div>
                                                <div class="col-md-6">
                                                	<div class="form-group">
                                                        <label for="product_category" class="col-md-4 control-label">Doc. No.</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <input id="doc_no" name="doc_no" type="text" class="form-control" title="Document No" value="" placeholder="" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 text-right mtop20">
										<button class="btn btn-success btn-flat" onclick="request_product_qty();">Request All</button>
									</div>
                        </div>
				<div class="panel-body">
				  <div class="adv-table">
					  <table  class="display table table-bordered table-striped" id="dynamic-table">
						  <thead>
							  <tr>
							  	  <th class="nosort">  <input type="checkbox" onclick="checkAll();"  name="chk[]"/></th>
								  <th>Product Name</th>	
								  <th>Reorder Qty</th>	
								  <th>Min.Qty</th>
								  <th>Request Qty</th>
								  <th>Current Qty</th>
								  <!-- <th class="hidden-phone">Action</th>					   -->
							  </tr>
						  </thead>
						  <tbody id="data_table">
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
	<?php include_once($include.'footer.php');?>
      <!--footer end-->
  </section>
    <!-- js placed at the end of the document so the pages load faster -->
		<?php include_once($include.'include_js_file.php');?>    
   <script src="<?php echo ROOT.INVENTORY_ROOT; ?>js/app/category_wise_min_max.js"></script>
    <!--<script src="js/count.js"></script>-->
	
	<script>
		$(".select2").select2({
			width: '100%'
		});
	</script>



  </body>
</html>
