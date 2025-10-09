<?php
session_start();

include('../include/urlfile.php');
$form = "Drawing";
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = $infopage['filename'];
$countryid = '101';
$stateid = '1';
$cityid = '1';
$currency_id = $_SESSION['currency_id'];
$conversion_rate = $_SESSION['currency_rate'];
$vendor_reference = '';
$quotation_no = '';
$quotation_date = 'd-m-Y';
$companyConfiguration = getCompanyConfiguration($dbcon);
//check permission for process type add
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
   ADMINISTRATOR_DRAWING_CREATE,
   ADMINISTRATOR_DRAWING_UPDATE
]);
$branch_id = $_SESSION['branch_id'];
$drawing_id = '';
if (strpos($_SERVER['REQUEST_URI'], "drawingedit") == true) {

   $back = "po_list";
   $mode = "Edit";
   $direct_add = '0';
   $request = 0;
   if (!in_array(ADMINISTRATOR_DRAWING_UPDATE, $bulkAccessArray)) {
      header("Location: " . DOMAIN . "permission_access");
   }
   $drawing_id = $dbcon->real_escape_string($_REQUEST['id']);
   //$query="select dr.*,im.file_name,im.drawing_image_id  from tbl_drawing as dr left join tbl_drawing_image as im on dr.drawing_id=im.drawing_id where dr.drawing_id=$drawing_id";

   $query = "select dr.* from tbl_drawing as dr where dr.drawing_id=$drawing_id";
   $rel = mysqli_fetch_assoc($dbcon->query($query));

   $revsql = "SELECT * FROM `tbl_revision` where drawing_id='" . $drawing_id . "' ORDER BY `tbl_revision`.`revision_id` DESC LIMIT 1";
   $rev_rel = mysqli_fetch_assoc($dbcon->query($revsql));
   $revison_number_val = $rev_rel['revision_number'];

   $vender_id = $rel['vender_id'];
   $revision_id = $rel['revision_id'];
} else {
   $back = "drawing_list";
   $mode = "Add";
   $direct_add = '0';
   $request = 0;
   if (!in_array(ADMINISTRATOR_DRAWING_CREATE, $bulkAccessArray)) {
      header("Location: " . DOMAIN . "permission_access");
   }
   $revsql = "SELECT * FROM `tbl_revision` where drawing_id='0' ORDER BY `tbl_revision`.`revision_id` DESC LIMIT 1";
   $rev_rel = mysqli_fetch_assoc($dbcon->query($revsql));
}
$company_config = getCompanyConfiguration($dbcon);
$sales_party_show = $company_config['sales_party_show'];

//echo $purchaseorder_id;
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <title>DRAWING</title>
   <?php include_once($include . 'include_css_file.php'); ?>
</head>

<body>
   <section id="container" class="sidebar-closed">
      <?php include_once($include . 'include_top_menu.php'); ?>
      <?php include_once($include . 'left_menu.php'); ?>
      <section id="main-content">
         <section class="wrapper">
            <?php//include_once('../include/equick_link.php');
            ?>
            <div class="row">
               <div class="col-lg-12">
                  <section class="panel">
                     <header class="panel-heading">
                        <h3><?= $mode . ' ' . $form ?></h3>
                     </header>
                     <div class="">
                        <ul class="breadcrumb">
                           <li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
                           <li><a href="<?= ROOT . 'masters_list' ?>"> Masters List</a></li>
                           <li><a href="<?= ROOT . ADMINISTRATION_ROOT . 'drawing_list' ?>"><?= $form ?></a></li>
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
                        <form class="form-horizontal" id="purchaseorder_add" action="javascript:;" method="post" name="purchaseorder_add" enctype="multipart/form-data">
                           <div class="row">
                              <div class="col-md-12">
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Drawing Number*</label>
                                       <div class="col-md-8 col-xs-11">
                                          <input id="drawing_number" name="drawing_number" type="text" class="form-control" title="Drawing Number" value="<?= $rel['drawing_number'] ?>" placeholder="Drawing Number" <?= (($drawing_id != '') ? 'disabled' : '') ?>>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Drawing Title *</label>
                                       <div class="col-md-8 col-xs-11">
                                          <input id="drawing_title" name="drawing_title" type="text" class="form-control" title="Drawing Title" value="<?= $rel['drawing_title'] ?>" placeholder="Drawing Title">
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Select Vendor</label>
                                       <div class="col-md-8 col-xs-11">
                                          <?php//=getcust($dbcon,$vender_id);
                                          ?>
                                          <select class="select2" name="vender_id" id="vender_id" onChange="get_so_no(this.value)" title="Select Vender">
                                             <?= getcust($dbcon, $vender_id, $sales_party_show); ?>
                                          </select>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-12">
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label"> Revision</label>
                                       <div class="col-md-8 col-xs-11">
                                          <!--  <select class="select2" name="revision_id" id="revision_id"  required title="Select Revision">
                                             <?= getrevision($dbcon, $revision_id); ?> 
                                             </select> -->
                                          <input id="revision_number" name="revision_number" type="text" class="form-control" title="Revision" placeholder="Revision" value="<?= $revison_number_val ?>" readonly>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Drawing Size </label>
                                       <div class="col-md-8 col-xs-11">
                                          <input id="drawing_size" name="drawing_size" type="text" class="form-control" title="Drawing Size" value="<?= $rel['drawing_size'] ?>" placeholder="Drawing Size">
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Drawing Scale </label>
                                       <div class="col-md-8 col-xs-11">
                                          <input id="drawing_scale" name="drawing_scale" type="text" class="form-control" title="Drawing Scale" value="<?= $rel['drawing_scale'] ?>" placeholder="Drawing Scale">
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-12">
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Drawing Location</label>
                                       <div class="col-md-8 col-xs-11">
                                          <input id="drawing_location" name="drawing_location" type="text" class="form-control" title="Drawing Location" value="<?= $rel['drawing_location'] ?>" placeholder="Drawing Location">
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">SO No. </label>
                                       <div class="col-md-8 col-xs-11">
                                          <select class="select2" name="sales_order_id" id="sales_order_id" title="SO No.">
                                             <?= getsalesorder($dbcon, $rel['vender_id'], $rel['sales_order_id']); ?>
                                          </select>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Remark. </label>
                                       <div class="col-md-8 col-xs-11">
                                          <textarea class="form-control" name="remark" id="remark"><?= $rel['remark'] ?></textarea>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-12">
                                 <?php if ($companyConfiguration['branch_wise_manage'] == '1') { ?>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label"> Branch </label>
                                          <div class="col-md-8">

                                             <select class="branch_validate" name="branch_id" id="e_branch_id" required>
                                                <?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
                                                <?= getBranchBox_new($dbcon, $branch, 'all'); ?>
                                             </select>
                                             <?php //echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'','4','8','',''); 
                                             ?>
                                          </div>
                                       </div>
                                    </div>
                                 <?php } ?>
                              </div>
                              <div class="col-md-12">
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Drawing Image Name </label>
                                       <div class="col-md-8 col-xs-11">
                                          <input id="image_name" name="image_name" type="text" class="form-control" title="Drawing Size" value="" placeholder="Drawing Image Name">
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Upload Image</label>
                                       <div class="col-md-8 col-xs-11">
                                          <input type="file" name="dr_file" id="dr_file" class="form-control">
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <button type="button" onClick="save_drawing_image('purchaseorder_add')" class="btn btn-primary" id="save_image" name="save_image">Add Drawing Image</button>
                                 </div>
                                 <div class="col-md-12 mtop20">
                                    <div id='drawing_image_list'>

                                    </div>
                                 </div>
                              </div>

                              <div class="row">
                                 <button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
                                 <a href="<?= ROOT . ADMINISTRATION_ROOT . 'drawing_list' ?>" type="button" class="btn btn-danger">Cancel</a>
                                 <div class="col-md-3"></div>
                              </div>
                              <!--Vendor row end-->
                              <input type='hidden' name='mode' id='mode' value='<?= $mode ?>' />
                              <input type="hidden" name="temp_img_type" id="rev_img_type" value="1">
                              <input type='hidden' name='eid' id='eid' value='<?= $drawing_id; ?>' />
                              <input type='hidden' name='back' id='back' value='<?= $back; ?>' />
                              <input type='hidden' name='revision_id' id='revision_id' value='<?= $rev_rel['revision_id'] ?>' />
                              <?
                              if ($direct_add == '1') {
                              ?>
                                 <input type="hidden" name="po_ref_id" id="po_ref_id" value="<?= $rel['drawing_id'] ?>" />
                              <?php} ?>
                           </div>
                        </form>
                        <br>
                        <br>

                        <!-- Tab Section Start By Umair -->
                        <?phpif ($mode == "Edit") { ?>
                           <section class="panel" style="margin-top: 15px">
                              <header class="panel-heading tab-bg-dark-navy-blue ">
                                 <ul class="nav nav-tabs">
                                    <li class="active">
                                       <a data-toggle="tab" href="#tab_revision" aria-expanded="true">Revisions</a>
                                    </li>

                                 </ul>
                              </header>
                              <div class="panel-body">
                                 <div class="tab-content">
                                    <div id="tab_revision" class="tab-pane active">
                                       <form class="form-horizontal" id="revision_add" action="javascript:;" method="post" name="revision_add">
                                          <section class="panel">
                                             <div class="panel-body bio-graph-info">
                                                <div class="row">
                                                   <div class="col-md-6">
                                                      <h1>Add Revision</h1>
                                                      <div class="row">
                                                         <div class="col-md-12">
                                                            <div class="col-md-6">
                                                               <div class="form-group">
                                                                  <label class="col-md-4 control-label" style="white-space:nowrap;">Revision No. *</label>
                                                                  <div class="col-md-8">
                                                                     <input type="text" class="form-control" id="revision_no" name="revision_no" title="Enter Revision Number" placeholder="Revision Number" value="" required>
                                                                  </div>
                                                               </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                               <div class="form-group">
                                                                  <label class="col-md-4 control-label" style="white-space: nowrap;">Revision Date *</label>
                                                                  <div class="col-md-8 col-xs-11">
                                                                     <input id="revision_date" name="revision_date" type="text" class="form-control default-date-picker" title="Revision Date" value="<?php echo date("d-m-Y"); ?>" placeholder="Revision Date" required>

                                                                  </div>
                                                               </div>
                                                            </div>
                                                         </div>
                                                         <div class="col-md-12">
                                                            <div class="col-md-12">
                                                               <div class="form-group">
                                                                  <label class="col-md-2 control-label">Remark </label>
                                                                  <div class="col-md-10 col-xs-11">
                                                                     <textarea class="form-control" id="revision_remark" name="revision_remark"></textarea>
                                                                  </div>
                                                               </div>
                                                            </div>
                                                         </div>
                                                         <div class="col-md-12">
                                                            <div class="col-md-5">
                                                               <div class="form-group">
                                                                  <label class="col-md-6 control-label">Revision Image Name* </label>
                                                                  <div class="col-md-6 col-xs-11">
                                                                     <input id="r_image_name" name="r_image_name" type="text" class="form-control" title="Drawing Size" value="" placeholder="Drawing Image Name">
                                                                  </div>
                                                               </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                               <div class="form-group">
                                                                  <label class="col-md-4 control-label">Upload Image*</label>
                                                                  <div class="col-md-8 col-xs-11">
                                                                     <input type="file" name="revision_file" id="revision_file" class="form-control">
                                                                  </div>
                                                               </div>
                                                            </div>
                                                            <div class="col-md-2">
                                                               <button type="button" class="btn btn-primary" id="save_r_image" onClick="save_drawing_image('revision_add')" name="save_r_image">Add Revision Image</button>
                                                            </div>
                                                         </div>

                                                      </div>
                                                      <div class="col-md-12 mtop20">
                                                         <div id='revision_img_data'>

                                                         </div>
                                                      </div>

                                                      <div class="row">
                                                         <input type="hidden" name="temp_img_type" id="rev_img_type" value="2">
                                                         <input type="hidden" name="drawing_id_ref" id="drawing_id_ref" value="<?= $rel['drawing_id'] ?>">
                                                         <input type="hidden" name="mode" id="add_revision" value="add_revision">
                                                         <button type="submit" class="btn btn-success" id="bsave" name="bsave">Add Revision</button>
                                                         <div class="col-md-3"></div>
                                                      </div>
                                                      <br><br>
                                                      <div class="row">
                                                         <div id="revision_data_div" style="height:200px; overflow:auto;overflow-x: hidden;"></div>
                                                      </div>
                                                   </div>
                                                   <div class="col-md-6">
                                                      <div id="dr_history"></div>
                                                   </div>
                                                </div>
                                             </div>
                                          </section>
                                       </form>
                                    </div>

                                 </div>
                              </div>
                           </section>

                        <?php} ?>
                        <!-- Tab Section -->


                     </div>

                  </section>
               </div>
            </div>
            <!--state overview end-->
         </section>
      </section>
      <!--main content end-->

      <div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
         <div class="modal-dialog custom-width">
            <div class="modal-content">
               <div class="modal-header">
                  <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h3 style="margin-top:-6px !important;">View Images</h3>
               </div>
               <div class="modal-body form">
                  <div class="form-group">
                     <!-- <div id="drawing_image_list"></div> -->
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
      <!--footer start-->
      <?php //include_once('../include/add_vender.php');
      ?>
      <?php include_once($include . 'footer.php'); ?>
      <!--footer end-->
   </section>
   <!-- js placed at the end of the document so the pages load faster -->
   <?php include_once($include . 'include_js_file.php'); ?>
   <script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/drawing.js?<?= time() ?>"></script>

   <!--
         <script src="<?= ROOT ?>js/app/state_mst.js?<?= time() ?>"></script>
         <script src="<?= ROOT ?>js/app/city_mst.js?<?= time() ?>"></script>
         <script src="<?= ROOT ?>js/app/customer.js?<?= time() ?>"></script>
         -->
   <script>
      $(".select2").select2({
         width: '100%'
      });

      /*$("#product_id").select2({
         width: '86%'
      });*/
      $('.default-date-picker').datepicker({
         format: 'dd-mm-yyyy',
         autoclose: true
      });

      $(".form_datetime").datetimepicker({
         format: 'dd-mm-yyyy hh:ii',
         autoclose: true,
         todayBtn: true,
         pickerPosition: "bottom-left"

      });

      function add_customer_purchase() {
         $("#bs-example-modal-lg").modal("show");
         $("#cat_id").val('1');
      }

      function consinee_change(val) {
         if (val == '1') {
            $('#consignee_id').select2("val", "");
            $('#consignee').hide();
         } else {
            $('#consignee').show();
         }
      }
      $(".branch_validate").select2({
         width: '100%'
      }).on('change', function() {
         $(this).valid();
      });
   </script>
   <?
   //echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
   //echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
   if ($mode == "Add") {
      echo "<script>show_data();</script>";
   } else {
      echo "<script>load_revision_no();</script>";
   }
   if ($direct_add == '1') {
      /*echo "<script>entry_po_req_data(".$rel['purchaseorder_id'].");</script>";
            echo "<script>
                  $('#po_type_status').attr('style','pointer-events: none;').attr('readonly','readonly');
            </script>";*/
   }
   ?>
</body>

</html>