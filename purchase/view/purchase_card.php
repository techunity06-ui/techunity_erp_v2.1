<?php 
   session_start();
   
   $path = "../../";
   $include = "../../include";
   
   include_once($path."config/config.php");
   include_once($path."config/session.php");
   
   include_once(COMMON_FUNCTION_PATH."common_functions.php");
   include_once($include."/function_database_query.php");
   $form="Purchase Card";
    $infopage = pathinfo( __FILE__ );
   $_SESSION['page']=$infopage['filename'];
   //echo "<br/>";
   //echo $_SERVER['HTTP_REFERER'];
   //echo "<br/>";
   //echo $_SERVER['REQUEST_URI'];
   
   $currency_id=$_SESSION['currency_id'];
   $quotation_date='d-m-Y';

   $disabled = 'disabled';$product_type=''; $purchase_type = '';$readonly = '';
   if(strpos($_SERVER[REQUEST_URI], "pcedit")==true)
   {
    $disabled = 'disabled';
    $readonly = 'readonly';
   	$back="purchase_card_list";
   	$mode="Edit";$direct_add='0';$request=0;
   	$purchasecard_id=$dbcon->real_escape_string($_REQUEST['id']);
   	$query="select pc.*, pm.product_name from tbl_purchasecard as pc left join product_mst as pm ON pc.product_id = pm.product_id where purchasecard_id=$purchasecard_id";
   	$rel=mysqli_fetch_assoc($dbcon->query($query));	
   
   	$purchasecard_date = date('d-m-Y',strtotime($rel['purchasecard_date']));
    $affected_date = date('d-m-Y',strtotime($rel['affected_date']));
   	$po_type_status=$rel['po_type_status'];
   	$vender_id=$rel['vender_id'];
    $product_type=$rel['product_type'];
    $currency_id = $rel['currency_id'];
    $product_name = $rel['product_name'];
    $purchase_type = $rel['purchase_type'];

    $product_id = $rel['product_id'];
    
    $_SESSION['selected_purchase_vendor'] = $vender_id;
    $_SESSION['purchase_type'] = $purchase_type;
    $_SESSION['selected_purchase_item'] = $rel['product_id'];
    $_SESSION['selected_product_name'] = $rel['product_name'];
    $_SESSION['selected_product_type'] = $rel['product_type'];

   }
   else if(strpos($_SERVER[REQUEST_URI], "po_req")==true)
   {
   	//po_req_list
   	$back="po_req_list";
   	$mode="Add";$direct_add='1';$request=1;
   	$vender_id=$dbcon->real_escape_string($_REQUEST['id']);
   
   	$purchasecard_date=date('d-m-Y');
   	$po_type_status='1';
   
   }
   else
   {
    $disabled = '';
   	$back="purchase_card_list";
   	$mode="Add";$direct_add='0';$request=0;
   	$purchasecard_date=date('d-m-Y');
    $affected_date = date('d-m-Y');
   	$po_type_status='';
    $vender_id = $_SESSION['selected_purchase_vendor'];
    $purchase_type = $_SESSION['purchase_type']; 
    $product_type = $_SESSION['selected_product_type'];
    $product_id = $_SESSION['selected_purchase_item'];
    $product_name = $_SESSION['selected_product_name'];
    //selected_purchase_item
   }
  
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <?php include_once($include.'/include_css_file.php');?>
   </head>
   <body>
      <section id="container" class="sidebar-closed">
         <?php include_once($include.'/include_top_menu.php');?>
         <?php include_once($include.'/left_menu.php');?>
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
                              <li><a href="<?=$_SESSION['purchase_card_main_list']?>"><?=$form?> List</a></li>
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
                              <a href="<?=ROOT.PURCHASE_ROOT.'purchase_card'?>"><button class="btn btn-success btn-flat">Add Purchase Card</button></a>
                           </span>
                        <?php } ?>
                        </header>
                        <div class="panel-body">
                           <form class="form-horizontal" role="form" id="purchasecard_add" action="javascript:;" method="post" name="purchasecard_add">
                              <div class="row">
                                 <div class="col-md-12">
                                    <div class="col-md-4">
                                    <div class="form-group">
                                          <label class="col-md-4 control-label">Select Type</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="form-control" name="purchase_type" id="purchase_type" onChange="change_supply_type(this.value)" required title="Select Purchase Type" <?=$disabled?>>
                                                <option value=""> Select Type</option>
                                                <option value="0" <?php if($purchase_type=='0'){echo "selected";} ?>>Vendor Wise</option>
                                                <option value="1" <?php if($purchase_type=='1'){echo "selected";} ?>>Product Wise</option>
                                             </select>
                                          </div>
                                       </div>
                                 </div>
                                 <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">Purchase Card No </label>
                                          <div class="col-md-8 col-xs-11">
                                             <input id="purchasecard_no" name="purchasecard_no" type="text" class="form-control" title="Date" value="<?=$rel['purchasecard_no']?>" placeholder="Purchase Order No" <?=$readonly?>>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">Purchase Card Date </label>
                                          <div class="col-md-8 col-xs-11">
                                             <input id="purchasecard_date" name="purchasecard_date" type="text" class="form-control default-date-picker" title="Date" value="<?php echo $purchasecard_date; ?>" placeholder="Purchase Card Date" <?=$readonly?>>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-12">
                                      

                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">Select Vendor</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2 vendor_class vendor_specified_class" name="vender_id" id="vender_id" onChange="get_common_details('po_listing_info',this.value,'<?=$purchasecard_id?>');" required title="Select Vender" <?=$disabled?>>
                                             <?=getcust($dbcon,'58');?> 
                                             </select>
                                          </div>
                                       </div>
                                    </div>


                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">Product Type</label>
                                          <div class="col-md-8 col-xs-11">
                                              <select class="select2 vendor_class item_specified_class" name="product_type" id="product_type" onChange="load_product(this.value);" title="Select Product Type" <?=$disabled?>>
                                               <?=getproducttype($dbcon,$product_type);?>
                                               </select>
                                             </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                                <label class="col-md-4 control-label">Select Product </label>
                                                <div class="col-md-8 col-xs-11">
                                                <select class="select2 vendor_class item_specified_class" title="Select product" name="product_id" onChange="get_items_details('po_items', this.value);get_common_details('po_listing_info',this.value,'<?=$purchasecard_id?>');" id="product_id" <?=$disabled?>>
                                                  <?php if($purchasecard_id!=''){ ?>
                                                    <option value="<?=$product_id?>"><?=$product_name?></option>
                                                  <?php }else{ ?>
                                                    <option value="<?=$product_id?>"><?=$product_name?></option>
                                                  <?php } ?>
                                               </select>
                                       </div>
                                       </div>
                                    </div>           
                                 </div>


                                 <div class="col-md-12">

                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">Select Currency</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2" name="currency_id" id="currency_id"  required title="Select Currency" <?=$disabled?>>
                                             <?=getcurrency($dbcon,$currency_id);?> 
                                             </select>
                                             
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">New Affected Date </label>
                                          <div class="col-md-8 col-xs-11">
                                             <input id="affected_date" name="affected_date" type="text" class="form-control default-date-picker" title="Date" value="<?php echo $affected_date; ?>" placeholder="Affected Date" <?=$readonly?>>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">Qtn No </label>
                                          <div class="col-md-8 col-xs-11">
                                             <input id="quotation_no" name="quotation_no" type="text" class="form-control" title="Date" value="<?=$rel['quotation_no']?>" placeholder="Quotation No" <?=$readonly?>>
                                          </div>
                                       </div>
                                    </div>
                            		<!-- <div class="col-md-4">
                            			<div class="form-group">
                                          <label class="col-md-4 control-label">Rate</label>
                                          <div class="col-md-8 col-xs-11">
                                            	<input id="currency_rate" name="currency_rate" type="number" class="form-control" title="Rate" value="<?=$rel['currency_rate']?>" placeholder="Rate" <?=$readonly?>>
                                          </div>
                                       </div>
                            		</div> -->
                                 <!-- <div class="col-md-4">
                                    <div class="form-group">
                                          <label class="col-md-4 control-label">Rate Tolerance (%)</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input id="p_rate" name="rate_tolerance" type="number" class="form-control" title="Rate Tolerance" maxlength="2" value="<?=$rel['rate_tolerance']?>" placeholder="Rate Tolerance" <?=$readonly?>>
                                          </div>
                                       </div>
                                 </div> -->
                                 </div>
                                 <div class="col-md-12">
                                    <!-- <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">GRate</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input id="g_rate" name="g_rate" type="number" class="form-control" title="GRate" value="<?=$rel['g_rate']?>" placeholder="GRate" <?=$readonly?>>
                                          </div>
                                       </div>
                                    </div> -->
                                    <!-- <div class="col-md-4">
                                    	<div class="form-group">
		                                          <label class="col-md-4 control-label" >Disc (%)</label>
		                                          <div class="col-md-8 col-xs-11">
		                                            <input id="discount_percentage" name="discount_percentage" type="number" class="form-control" title="Mobile No." value="<?=$rel['discount_percentage']?>" placeholder="Disc Per." <?=$readonly?>>
		                                          </div>
		                                  </div>
                                    </div> -->
                                    
                                 </div>
                                
                                 <div class="col-md-12">
                                    
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label" >Quotation date </label>
                                          <div class="col-md-8 col-xs-11">
                                             <input id="quotation_date" name="quotation_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$rel['quotation_date']?>" placeholder="Date" <?=$readonly?>>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       
                                    </div>
                                </div>
                             </div>

                             <!-- Tab Section Start By Umair -->
                             <section class="panel" style="margin-top: 15px">
                          	 <header class="panel-heading tab-bg-dark-navy-blue ">
                              <ul class="nav nav-tabs">
                                 <li class="active">
                                      <a data-toggle="tab" href="#po_listing_info" onClick="get_common_details('po_listing_info')" aria-expanded="true">Listing Information</a>
                                  </li>
                                  <li>
                                      <a data-toggle="tab" href="#po_items" onClick="get_items_details('po_items')" aria-expanded="true">Items</a>
                                  </li>
                                   <li class="">
                                      <a data-toggle="tab" href="#po_vendor_details" onClick="get_vendor_details('po_vendor_details')" aria-expanded="false">Vendor Details</a>
                                  </li>
                                  <li class="">
                                      <a data-toggle="tab" href="#po_billing_terms" onClick="get_vendor_details('po_billing_terms')" aria-expanded="false">Billing Terms</a>
                                  </li>
                                  <li class="">
                                      <a data-toggle="tab" href="#po_terms_cond" aria-expanded="false">Terms & Condition</a>
                                  </li>
                                  <li class="">
                                      <a data-toggle="tab" href="#po_report" onClick="get_common_details('po_report')" aria-expanded="false">Report</a>
                                  </li>
                                  
                              </ul>
                          </header>
                          <div class="panel-body">
                              <div class="tab-content">
                                  <div id="po_listing_info" class="tab-pane active" >Please select vendor or product first.</div>
                                  <div id="po_items" class="tab-pane"></div>
                                  <div id="po_vendor_details" class="tab-pane"></div>
                                  <div id="po_billing_terms" class="tab-pane"></div>
                                  <div id="po_terms_cond" class="tab-pane">
                                  	<div class="row">
	                                  	 <div class="form-group">
	                                          <label class="col-md-3 control-label">Terms Condition</label>
	                                          <div class="col-md-9 col-xs-11">
	                                             <textarea class="form-control" placeholder="Terms Condition" name="terms_condition" id="terms_condition" ><?=$rel['terms_condition']?></textarea>
	                                          </div>
	                                      </div>
	                                 </div>
                                  </div>
                                  <div id="po_report" class="tab-pane"></div>
                              </div>
                          </div>
                      </section>
                      <!-- Tab Section -->
                             <div class="row">
                              <?php if($purchasecard_id==''){ ?>
                                 <button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
                               <?php } ?>
                                 <a href="<?=ROOT.PURCHASE_ROOT.'purchase_card_list'?>" type="button" class="btn btn-danger">Cancel</a>
                                 <div class="col-md-3"></div>
                              </div>
                              <!--Vendor row end-->	
                              <input type='hidden' name='po_request' id='po_request' value='<?=$request?>' />
                              <input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
                              <input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
                              <input type='hidden' name='eid' id='eid' value='<?=$purchasecard_id;?>' />	
                              <input type='hidden' name='back' id='back' value='<?=$back;?>' />	
                              <?
                                 if($direct_add=='1'){
                                 ?>		
                              <input type="hidden" name="po_ref_id" id="po_ref_id" value="<?=$rel['purchasecard_id']?>" />
                              <?	} ?>	
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
         <?php include_once($include.'/add_cust.php');?>
         <?php //include_once('../include/add_vender.php');?>
         <?php include_once($include.'/add_product.php');?>
         <?php include_once($include.'/add_city.php');?>
         <?php include_once($include.'/add_state.php');?>
         <?php include_once($include.'/add_payterms.php');?>
         <?php include_once($include.'/add_placesupally.php');?>
         <?php include_once($include.'/add_modedispatch.php');?>
         <?php include_once($include.'/footer.php');?>
         <!--footer end-->
      </section>
      <!-- Model -->
         <div class="modal colored-header info" id="ModalProductAccount" role="dialog" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog custom-width">
              <div class="modal-content">
                <div class="modal-header">
                <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h3 style="margin-top:-6px; important!">Edit <?=$form?></h3>
                </div>
                <div class="modal-body form">
                <form id="FormEditCurrency" role="form" method="post" novalidate>       
                  <div class="form-group">
                    <label class="control-label">Currency Name</label>
                    <input type="text" name="edit_currency_name"  id="edit_currency_name" class="form-control">
                  </div>  
                </div>
                <div class="modal-footer">
                  <input type="hidden" name="edit_id" id="edit_id" value="" />
                  <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
                 <!--  <button class="btn btn-info btn-flat" type="submit">Update Currency</button> -->
                </div>
                </form>
              </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
          </div>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once($include.'/include_js_file.php');?>   
      <script src="<?=ROOT.PURCHASE_ROOT?>js/app/purchase_card.js?<?=time()?>"></script>
      <script src="<?=ROOT?>js/app/payment_terms.js?<?=time()?>"></script>
      <script src="<?=ROOT?>js/app/mode_disptch.js?<?=time()?>"></script>
      <script src="<?=ROOT?>js/app/product_mst.js?<?=time()?>"></script>
      <!--
         <script src="<?=ROOT?>js/app/state_mst.js?<?=time()?>"></script>
         <script src="<?=ROOT?>js/app/city_mst.js?<?=time()?>"></script>
         <script src="<?=ROOT?>js/app/customer.js?<?=time()?>"></script>
         -->
      <script>
         $(".select2").select2({
         	width: '100%'
         });
         /*CKEDITOR.replace( 'terms_condition', {
         	enterMode: CKEDITOR.ENTER_BR
         });*/
         CKEDITOR.replace( 'terms_condition', {
              toolbarGroups: [
                  { name: 'mode' },
                  { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
                  { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
                  { name: 'links' },
                  { name: 'insert' },
                  { name: 'forms' },
                  { name: 'tools' },
                  { name: 'document',       groups: [ 'mode', 'document', 'doctools' ] },
                 /* { name: 'others' },*/
                  '/',
                  { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
                  { name: 'styles' },
                  { name: 'colors' },
                  { name: 'about' },
                   '/',
                  { name: 'basicstyles', groups: [ 'clipboard' ] },
              ],    
              on: {
                  pluginsLoaded: function() {
                      var editor = this,
                          config = editor.config;

                      var tags = [];
                      /*tags[0]=["[contact_name]", "Name"];
                      tags[1]=["[contact_email]", "Email"];
                      tags[2]=["[contact_user_name]", "User Name"];  */

                      $.ajax({
                        type: "POST",
                        url: root_domain+purchase_domain+'app/purchase_card/',
                        data: { mode : "get_insert_tags_data" },
                        success: function(response)
                        {
                          tags = jQuery.parseJSON(response);
                        }
                      });  
                      
                      editor.ui.addRichCombo( 'my-combo', {
                          label: 'Insert Merge Fields',
                          title: 'Insert Merge Fields',
                          toolbar: 'basicstyles',
                  
                          panel: {               
                              css: [ CKEDITOR.skin.getPath( 'editor' ) ].concat( config.contentsCss ),
                              multiSelect: false,
                              attributes: { 'aria-label': 'My Dropdown Title' }
                          },
                          

                          init: function() {    
                              this.startGroup( 'Insert Fields' );
                              /*this.add( 'bar', 'Bar!' ); 
                              this.add( 'foo', 'Foo!' );
                              this.add( 'bar', 'Bar!' ); 
                              this.add( 'foo', 'Foo!' );
                              this.add( 'bar', 'Bar!' ); 
                              this.add( 'foo', 'Foo!' );
                              this.add( 'bar', 'Bar!' ); 
                              this.add( 'foo', 'Foo!' );
                              this.add( 'bar', 'Bar!' );                    
                              this.add( 'foo', 'Foo!' );
                              this.add( 'bar', 'Bar!' ); 
                              this.add( 'foo', 'Foo!' );
                              this.add( 'bar', 'Bar!' ); 
                              this.add( 'foo', 'Foo!' );
                              this.add( 'bar', 'Bar!' ); 
                              this.add( 'foo', 'Foo!' );
                              this.add( 'bar', 'Bar!' ); 
                              this.add( 'foo', 'Foo!' );
                              this.add( 'bar', 'Bar!' ); */
                                
                              for (var this_tag in tags){
                                this.add(tags[this_tag][0], tags[this_tag][1]);
                              }

                             /* this.startGroup( 'My Dropdown Group #2' );
                              this.add( 'ping', 'Ping!' );
                              this.add( 'pong', 'Pong!' );    */                
                              
                          },
              
                          onClick: function( value ) {
                              editor.focus();
                              editor.fire( 'saveSnapshot' );
                              editor.insertHtml( value );
                              editor.fire( 'saveSnapshot' );
                          }
                      } );        
                  }        
              }
          } );
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

         if($('#eid').val()==''){
           change_supply_type('<?=$purchase_type?>');
         }
         function change_supply_type(str){
            if(str=='0'){
               $('.item_specified_class').attr('disabled', 'disabled');
               $('.vendor_specified_class').removeAttr("disabled");
            }else if(str=='1'){
               $('.vendor_specified_class').attr('disabled', 'disabled');
               $('.item_specified_class').removeAttr("disabled");
            }else{
               //$('.vendor_class').attr('disabled', 'disabled');
            }
         }

       
      </script>
      <?
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