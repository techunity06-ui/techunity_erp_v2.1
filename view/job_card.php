<?php 
    session_start();
    include_once("../config/config.php");
    include_once("../config/session.php");

    include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
    include_once("../include/function_database_query.php");
    $form="Job Card";
    $infopage = pathinfo( __FILE__ );
    $_SESSION['page']=$infopage['filename'];

    $vender_id = '58'; 
    $product_type = '3';
    $back="job_card";
    $mode="Add";$direct_add='0';$request=0;
    $purchasecard_date= $date = date('d-m-Y');

    $sql = "SELECT * FROM `tbl_request_product` WHERE user_id='".$_SESSION['user_id']."' AND company_id='".$_SESSION['company_id']."' AND job_card_status='1'";
    $result=$dbcon->query($sql);


?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <?php include_once('../include/include_css_file.php');?>
   </head>
   <body>
      <section id="container" class="sidebar-closed">
         <?php include_once('../include/include_top_menu.php');?>
         <?php include_once('../include/left_menu.php');?>
         <section id="main-content">
            <section class="wrapper">
               <?php//include_once('../include/equick_link.php');?>
               <div class="row">
                  <div class="col-lg-12">
                     <section class="panel">
                        <header class="panel-heading">
                           <h3><?=$mode.' '.$form?></h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li><a href="<?=$_SESSION['purchase_card_main_list']?>"><?=$form?> Wise</a></li>
                           </ul>
                        </div>
                     </section>
                  </div>
               </div>
               <div class="row">
                  <div class="col-sm-12">
                     <section class="panel">
                        <header class="panel-heading">
                           New <?=$form?>
                           <?php if($purchasecard_id!=''){ ?>
                           <span class="tools pull-right">
                           <a href="<?=ROOT.'purchase_card'?>"><button class="btn btn-success btn-flat">Add Purchase Card</button></a>
                           </span>
                           <?php } ?>
                        </header>
                        <div class="panel-body">
                           <form class="form-horizontal" role="form" id="purchasecard_add" action="javascript:;" method="post" name="purchasecard_add">
                            <input id="purchasecard_no" name="purchasecard_no" type="hidden" class="form-control" title="Date" value="" placeholder="Purchase Order No" <?=$readonly?>>
                               <section class="panel" >
                                   <div class="panel-body bio-graph-info">
                                        <div class="row">
                                                <div class="col-md-12">
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Job Card No.</label>
                                                      <div class="col-md-8">
                                                        <input type="text" class="form-control" id="job_card_no" name="job_card_no" title="Enter Job Card No."  placeholder="Job Card No." value="<?=($rel['job_card_no']?$rel['job_card_no']:'56353')?>" required >
                                                      </div>
                                                    </div>
                                                  </div>
                                                 <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Date</label>
                                                      <div class="col-md-8 col-xs-11">
                                                        <input id="job_card_date" name="job_card_date" type="text" class="form-control default-date-picker" title="Date" value="<?phpif($mode=='Add'){ echo $date;}else if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['job_card_date']));}?>" placeholder="Date">
                                                      </div>
                                                    </div>
                                                  </div>
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">R/W Qty</label>
                                                      <div class="col-md-8">
                                                        <input type="text" name="row_qty" id="row_qty" value="12" class="form-control">
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div> 

                                                <div class="col-md-12">
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Job Qty</label>
                                                      <div class="col-md-8">
                                                        <input type="text" name="job_qty" id="job_qty" value="8" class="form-control">
                                                      </div>
                                                    </div>
                                                  </div>
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Process Sheet No.</label>
                                                      <div class="col-md-8">
                                                        <input type="text" name="sheet_no" id="sheet_no" value="82334" class="form-control">
                                                      </div>
                                                    </div>
                                                  </div>
                                                 
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Reject Qty</label>
                                                      <div class="col-md-8">
                                                        <input type="text" name="reject_qty" id="reject_qty" value="5" class="form-control">
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div>  
 
                                                <div class="col-md-12">
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Is Excisable</label>
                                                      <div class="col-md-8">
                                                        <select class="select2" name="excisable" id="excisable" required title="Excisable">
                                                            <?=reverse_type_bill($dbcon, $rel['excisable']);?> 
                                                          </select>
                                                      </div>
                                                    </div>
                                                  </div>
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Pend. Job Qty</label>
                                                      <div class="col-md-8">
                                                        <input type="text" name="pending_job_qty" id="pending_job_qty" value="2" class="form-control">
                                                      </div>
                                                    </div>
                                                  </div>
                                                  <div class="col-md-4">
                                                       <div class="form-group">
                                                          <label class="col-md-4 control-label">Accepted Qty</label>
                                                          <div class="col-md-8 col-xs-11">
                                                           <input type="text" name="accepted_job_qty" id="accepted_job_qty" value="4" class="form-control">
                                                          </div>
                                                       </div>
                                                  </div> 
                                                </div>

                                                <div class="col-md-12">
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Heat/Batch No.</label>
                                                      <div class="col-md-8">
                                                        <input type="text" name="batch_no" id="batch_no" value="20909" class="form-control">
                                                      </div>
                                                    </div>
                                                  </div>
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-6 control-label">
                                                        <?php if($poid!=''){ ?>
                                                          <input type="hidden" id="select_batch" name="select_batch" value="<?=$rel['select_batch']?>">
                                                        <?php } ?>  
                                                        <input type="radio" id="select_batch" name="select_batch" style="height: 18px;width: 18px;" value="1" onchange=""  <?=($rel['select_batch']=='1')?'checked':''?>  <?=$checked?> <?=$disabled?>>
                                                      <strong>From Stock</strong></label>
                                                      <label class="col-md-6 control-label">
                                                        <input type="radio" id="" name="select_batch" style="height: 18px;width: 18px;" value="2" onchange="" <?=($rel['select_batch']=='2')?'checked':''?> <?=$disabled?>>
                                                      <strong>From JW Chn</strong></label>
                                                    </div>    
                                                  </div>
                                                </div>
                                        </div>
                                   </div>
                               </section>
                              <!-- Tab Section Start By Umair -->
                              <section class="panel" style="margin-top: 15px">
                                 <header class="panel-heading tab-bg-dark-navy-blue ">
                                    <ul class="nav nav-tabs">
                                       <li class="active">
                                          <a data-toggle="tab" href="#po_job_card" aria-expanded="true">Job Card Details</a>
                                       </li>
                                       <li class="">
                                          <a data-toggle="tab" href="#po_process" onClick="" aria-expanded="false">Process Details</a>
                                       </li>
                                       <li class="">
                                          <a data-toggle="tab" href="#po_scrap" onClick="" aria-expanded="false">Scrap Details</a>
                                       </li>
                                       <li class="">
                                          <a data-toggle="tab" href="#po_login" onclick="get_vendor_details('po_login')" aria-expanded="false">Login Details</a>
                                       </li>
                                    </ul>
                                 </header>
                                 <div class="panel-body">
                                    <div class="tab-content">
                                       <div id="po_job_card" class="tab-pane active" >
                                          <section class="panel">
                                           <div class="panel-body bio-graph-info">
                                             <h1>Job Card Details</h1>

                                              <div class="row">
                                                <div class="col-md-12">
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">W.O. No.</label>
                                                      <div class="col-md-8">
                                                        <input type="text" class="form-control" id="po_req_no" name="po_req_no" title="Enter WO. No."  placeholder="Wo. No." value="<?=($rel['po_req_no']?$rel['po_req_no']:'56353')?>" required >
                                                      </div>
                                                    </div>
                                                  </div>
                                                 
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Job Card</label>
                                                      <div class="col-md-8 col-xs-11">
                                                        <select class="select2" name="job_card_id" id="job_card_id" required title="Job Card">
                                                          <option value="Pending">Pending</option>
                                                        </select>
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div> 

                                                <div class="col-md-12">
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Item Code</label>
                                                      <div class="col-md-8">
                                                        <input type="text" class="form-control" id="item_code" name="item_code" title="Enter Item Code"  placeholder="Item Code" value="<?=($rel['item_code']?$rel['item_code']:'766498')?>" required >
                                                      </div>
                                                    </div>
                                                  </div>
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Item Description</label>
                                                      <div class="col-md-8">
                                                        <input type="text" name="item_description" id="item_description" value="Assembly Description" class="form-control">
                                                      </div>
                                                    </div>
                                                  </div>
                                                 
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Add. Description</label>
                                                      <div class="col-md-8">
                                                        <textarea class="form-control" id="add_description" name="add_description"></textarea>
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div>  

                                                <div class="col-md-12">
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Drawing Number</label>
                                                      <div class="col-md-8">
                                                        <input type="text" name="drawing_number" id="drawing_number" value="7675556" class="form-control">
                                                      </div>
                                                    </div>
                                                  </div>
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Ref. No</label>
                                                      <div class="col-md-8">
                                                        <input type="text" name="ref_no" id="ref_no" value="8768686" class="form-control">
                                                      </div>
                                                    </div>
                                                  </div>
                                                  <div class="col-md-4">
                                                       <div class="form-group">
                                                          <label class="col-md-4 control-label">Drawing Revision</label>
                                                          <div class="col-md-8 col-xs-11">
                                                             <input id="drawing_revision" name="drawing_revision" type="text" class="form-control" title="Drawing Revision" value="<?=($rel['drawing_revision']?$rel['drawing_revision']:'54')?>" placeholder="Drawing Revision">
                                                          </div>
                                                       </div>
                                                  </div> 
                                                </div>

                                                <div class="col-md-12">
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">UOM </label>
                                                      <div class="col-md-8 col-xs-11">
                                                            <select class="select2" name="product_base_unit" id="product_base_unit"  title="Select Unit" >
                                                              <?php if($mode=='Edit') { echo getunit($dbcon,$rel['product_base_unit']); } else { echo getunit($dbcon,3); } ?>
                                                            </select>
                                                      </div>
                                                    </div>
                                                  </div>
                                                  <div class="col-md-4">
                                                       <div class="form-group">
                                                          <label class="col-md-4 control-label">BOM No.</label>
                                                          <div class="col-md-8 col-xs-11">
                                                             <input id="bom_no" name="bom_no" type="text" class="form-control" title="BOM No." value="<?=($rel['bom_no']?$rel['bom_no']:'22233')?>" placeholder="Bom No.">
                                                          </div>
                                                       </div>
                                                  </div>  
                                                  <div class="col-md-4">
                                                    <div class="form-group">
                                                      <label class="col-md-4 control-label" style="">Forecast No.</label>
                                                      <div class="col-md-8">
                                                        <input id="forecast_no" name="forecast_no" type="text" class="form-control" title="Forecast No." value="<?=($rel['forecast_no']?$rel['forecast_no']:'65433')?>" placeholder="Forecast No.">
                                                      </div>
                                                    </div>
                                                  </div> 
                                                </div>

                                                <div class="col-md-12">
                                                  <div class="col-md-4">
                                                     <div class="form-group">
                                                        <label class="col-md-4 control-label">Remark</label>
                                                        <div class="col-md-8 col-xs-11">
                                                           <textarea class="form-control" name="remark" id="remark"></textarea>
                                                        </div>
                                                     </div>
                                                  </div>
                                                </div>
                                              </div>
                                           </div>
                                          </section>  
                                       </div>
                                       <div id="po_process" class="tab-pane">Process Details</div>
                                       <div id="po_scrap" class="tab-pane"> Scrap Details </div>
                                       <div id="po_login" class="tab-pane"> Login Details </div>
                                    </div>
                                 </div>
                              </section>
                              <!-- Tab Section -->
                              <!--Vendor row end--> 
                              <input type='hidden' name='po_request' id='po_request' value='<?=$request?>' />
                              <input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
                              <input type='hidden' name='eid' id='eid' value='' />  
                              <input type='hidden' name='table_id' id='table_id' value='' />  
                              <input type='hidden' name='back' id='back' value='<?=$back;?>' /> 
                           </form>
                        </div>
                     </section>
                  </div>
               </div>
               <!--state overview end-->
            </section>
         </section>
       <!--main content end-->
       <!--footer start-->
       <?php include_once('../include/add_cust.php');?>
       <?php //include_once('../include/add_vender.php');?>
       <?php include_once('../include/add_product.php');?>
       <?php include_once('../include/add_city.php');?>
       <?php include_once('../include/add_state.php');?>
       <?php include_once('../include/add_payterms.php');?>
       <?php include_once('../include/add_placesupally.php');?>
       <?php include_once('../include/add_modedispatch.php');?>
       <?php include_once('../include/footer.php');?>
       <!--footer end-->
      </section>

      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once('../include/include_js_file.php');?>   
      <script src="<?=ROOT?>js/app/payment_terms.js?<?=time()?>"></script>
      <script src="<?=ROOT?>js/app/mode_disptch.js?<?=time()?>"></script>
      <script src="<?=ROOT?>js/app/product_mst.js?<?=time()?>"></script>
      <script src="<?=ROOT?>js/app/job_card.js?<?=time()?>"></script>
      <!--
       <script src="<?=ROOT?>js/app/state_mst.js?<?=time()?>"></script>
       <script src="<?=ROOT?>js/app/city_mst.js?<?=time()?>"></script>
       <script src="<?=ROOT?>js/app/customer.js?<?=time()?>"></script>
      -->
      <script>
          $(".select2").select2({
            width: '100%',
            //minimumInputLength: 2,
          });
        
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
         
      </script>
      <?php 
         //echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
         //echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
         if($mode=="Add"){
          //echo "<script>show_data();</script>";
          echo "<script>get_series_no(16);</script>";
         }
         if($direct_add=='1'){
          /*echo "<script>entry_po_req_data(".$rel['purchaseorder_id'].");</script>";
          echo "<script>
              $('#po_type_status').attr('style','pointer-events: none;').attr('readonly','readonly');
          </script>";*/
         }
      ?>
   </body>
</html>