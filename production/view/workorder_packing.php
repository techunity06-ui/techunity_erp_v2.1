<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Workorder Packing";
	$branch_id = $_SESSION['branch_id'];
	$company_config = getCompanyConfiguration($dbcon);
	$getspecialConfiguration=getspecialConfiguration($dbcon);

	if($getspecialConfiguration['creative_fastners_permission'] == '0'){
		header("Location: ".DOMAIN."permission_access");
	}

	$work_order_id = $dbcon->real_escape_string($_REQUEST['id']);

  $display = "display:none;";

	$query = "SELECT sp_id,po_req_no FROM tbl_set_main_process WHERE sp_status != 2 AND finish_status = 1 AND packing_status = 0";
	$result = $dbcon->query($query); 


	$sp_query = "SELECT sp.product_id,p.product_base_unit,p.product_conv_unit FROM tbl_set_main_process as sp LEFT JOIN product_mst as p on p.product_id = sp.product_id WHERE sp.sp_status != 2 AND sp.sp_id = " . $work_order_id;
	$pr_row = brp_mysqli_fetch_assoc($dbcon->query($sp_query));

	$product_id = $pr_row['product_id'];

    $query1 = "SELECT workorder_packing_id FROM tbl_workorder_packing WHERE status != 2 AND workorder_id = " .$work_order_id;
   $result1 = $dbcon->query($query1); 

   if(brp_mysqli_num_rows($result1) > 0){
     $display = "display:block;";
   }

?>
<!DOCTYPE html>
<html lang="en">

<head>
   <?php include_once($include . 'include_css_file.php'); ?>
</head>
<body>
   <section id="container" class="sidebar-closed">
      <?php include_once($include . 'include_top_menu.php'); ?>
      <?php include_once($include . 'left_menu.php'); ?>
      <link href="<?= ROOT ?>assets/sweetalert2/sweetalert2.min.css" rel="stylesheet">
      <section id="main-content">
         <section class="wrapper">
            <div class="row">
               <div class="col-lg-12">
                  <section class="panel">
                     <header class="panel-heading">
                        <h3><?= $form ?></h3>
                     </header>
                     <div class="">
                        <ul class="breadcrumb">
                           <li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
                           <li><a href="<?= ROOT . PRODUCTION_ROOT . 'work_order' ?>">Workorder List </a></li>
                           <li> Workorder Packing </li>
                        </ul>
                     </div>
                  </section>
               </div>
            </div>
            <div class="row">
               <div class="col-sm-12">
                  <section class="panel">
                     <header class="panel-heading">
                        New <?= $form ?>
                     </header>
                     <div class="panel-body">
                        <form class="form-horizontal" role="form" id="Workorder_packing_add" action="javascript:;" method="post" name="Workorder_packing_add">
                           <div class="row">
                              <div class="col-md-12">
                                   <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Workorder No*</label>
                                       <div class="col-md-8 col-xs-11">
                                             <input type="hidden" id="product_id" name="product_id" value="<?=$product_id?>">
                                             <input type="hidden" id="workorder_id" name="workorder_id" value="<?=$work_order_id?>">
                                          <select class="select2" readonly id="work_order_id" name="work_order_id" >
                                          	<option value=""> Select Workorder</option>
                                          	<?phpwhile ($row = brp_mysqli_fetch_assoc($result)) { 
                                          		$select = "";
                                          		if($row['sp_id'] == $work_order_id){
                                          			$select = 'selected="selected"';
                                          		}
                                          		echo "<option ".$select." value=''>".$row['po_req_no']."<option>";
                                          	 } ?>
                                          </select>
                                       </div>
                                    </div>
                                 </div>
                                  <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Workorder Stock QTY*</label>
                                       <div class="col-md-8 col-xs-11">
                                          <input type="text" class="form-control" name="wo_stock_qty" id="wo_stock_qty" readonly  value="0">
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">UNIT*</label>
                                       <div class="col-md-8 col-xs-11">
                                          <select class="select2" id="packing_unit" onchange="load_workorder_stock_qty()">
                                          	
                                          </select>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              
                              		<div class="col-md-12 mtop20">
                              		
                    <table cellspacing="10" style="border-collapse:inherit;table-layout: fixed;" id="product_list" class="display table table-bordered table-striped">
                       <tr id="field">
                          <th width="20%" class="text-center">Packing Name</th>
                          <th width="10%" class="text-center">Packing Size</th>
                          <th width="10%" class="text-center ">Box QTY</th>
                          <th width="15%" class="text-center">Total Quantity</th>
                          <th width="35%" class="text-center">Batch No</th>
                          <th width="10%" class="text-center"></th>
                       </tr>
                       <tr id="field1">
                          <td style="vertical-align:top;" width="20%">
                          	<input type="hidden" id="size" name="size" value="0">
                          
                             <select class="select2" title="Select Packing Size" name="packing_id" onchange="get_packing_size(this.value);" id="packing_id">
                                <option value="">Choose Packing Name</option>
                                <?php
	                                $query1 = "SELECT packing_id,packing_name FROM packing_mst WHERE status != 2 AND company_id = " . $_SESSION['company_id'];
									$result1 = $dbcon->query($query1); 
	                                while ($row1 = brp_mysqli_fetch_assoc($result1)) {
	                                   echo '<option  value="' . $row1['packing_id'] . '">' . $row1['packing_name'] . '</option>';
	                                }
                                ?>
                             </select>

                          </td>
                           <td style="vertical-align:top;" width="10%">
                           	<input type="text" class="form-control" readonly name="packing_size" id="packing_size">
                           </td>
                           <td style="vertical-align:top;" width="10%">
                           	<input type="text" class="form-control digitOnly" onchange="calculate_total_box_qty(this.value)" name="box_qty" id="box_qty">
                           </td>
                          
                           <td style="vertical-align:top;" width="20%">
                           	<input type="number" class="form-control numbersOnly" name="total_box_qty" id="total_box_qty" >
                           </td>
                           <td style="vertical-align:top;" width="35%">
                           		<input type="text" class="form-control" name="batch_no" id="batch_no">
                           </td>
                          
                          <td style="vertical-align:top;text-align: center;"  width="10%">
                             <input type="hidden" name="edit_id" id="edit_id">
                             <input type="button" id="addrow" class="btn btn-primary" data-original-title="Add Packing" data-toggle="tooltip" data-placement="top" onclick="add_field();" value="Add" />
                          </td>
                       </tr>
                    </table>
                  </div>
                    <div class="col-md-12 mtop20 text-right">
                                        <button type="button" class="btn btn-info pull-right" id="btn_print" name="btn_print" style="margin-right: 20px;<?= $display?>" onclick="workorder_packing_print()">Print Workorder Package </button>
                                    </div>
            		<div class="col-md-12 mtop20">
            			 <div class="adv-table" id="dynamic-table">
				             
							  </div>
                       <input type="hidden" name="mode" id="mode" value="add">
                       <input type="hidden" name="workorder_packing_id" id="workorder_packing_id" value="<?=$workorder_packing_id;?>">
                  </div>
                         

                              <div class="row mtop20">
                                 <div class="col-md-4 mtop20">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Remark </label>
                                       <div class="col-md-8 col-xs-11">
                                          <textarea class="form-control" name="remark" id="remark"></textarea>
                                       </div>
                                    </div>
                                 </div>
                                 
                              </div>
                              <div class="clearfix"></div>
                              <div class="col-md-12">
                                 <center>
                                    <button type="submit" class="btn btn-success" id="save" name="save">Generate Packing</button>
                                    <a href="<?= ROOT . PRODUCTION_ROOT . 'work_order' ?>" type="button" class="btn btn-danger">Cancel</a>
                                 </center>
                              </div>
                           </div>
                        </form>
                     </div>
                  </section>
               </div>
            </div>
         </section>
      </section>
      <?php include_once($include . 'footer.php'); ?>
   </section>
   <?php include_once($include . 'include_js_file.php'); ?>
   <?php include_once($include1 . 'workorder_package_print_modal.php'); ?>
   <script type='text/javascript' src='<?= ROOT ?>assets/sweetalert2/sweetalert2.all.min.js'></script>
   <script src="<?= ROOT . PRODUCTION_ROOT ?>js/app/workorder_packing.js?<?= time() ?>"></script>
   <script>
      $(".select2").select2({
         width: '100%'
      });
       $("#work_order_id").select2('readonly',true);
      $('.default-date-picker').datepicker({
         format: 'dd-mm-yyyy',
         autoclose: true
      });

   </script>

</body>

</html>