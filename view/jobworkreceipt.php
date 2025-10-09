<?php 
   session_start();
   include_once("../config/config.php");
   include_once("../config/session.php");
   include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
   $_SESSION['contents']=''; 
   $form="Jobwork";
   $mode="Print";
   $jobworkMainId=$dbcon->real_escape_string($_REQUEST['id']);
   $query="select jobwork.*,country.country_name,state.state_name,cust.stateid,state.gst_state_code, city.city_name, cust.l_name, cust.m_address, cust_pincode,cust_mobile,gst_no from tbl_jobwork_main as jobwork 
   left join tbl_ledger as cust on cust.l_id=jobwork.vendor_id
   left join country_mst as country on country.countryid=cust.countryid
   left join state_mst as state on state.stateid=cust.stateid
   left join city_mst as city on city.cityid=cust.cityid
   where jobwork.jobwork_main_id=$jobworkMainId";
   $rel=brp_mysqli_fetch_assoc($dbcon->query($query));
   $cons_gst_no=$rel['gst_no'];
   $cons_pan_no=$rel['pan_no'];
   $cons_state_name=$rel['state_name'];
   $cons_gst_state_code=$rel['gst_state_code'];

   	
   	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
   	$set_head=brp_mysqli_fetch_assoc($dbcon->query($set));	
   	$order_date='';$lr_date='';$dispatch_date='';
   	if($rel['jobwork_date']!="1970-01-01" && $rel['jobwork_date']!="0000-00-00")
   	{
   		$order_date=date('d-m-Y',strtotime($rel['jobwork_date']));
   	}
   	
   /* Check Discount is On or off Start */
   	if($set_head['show_disc']=='1'){
   		$colspan=5;
   		$dynamicwidth=25;
   	}else{
   		$colspan=6;
   		$dynamicwidth=30;
   	}

   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <?php include_once('../include/include_css_file.php');?>
      <style>
         body {
         color: #000000;
         }
         .con ul 
         {
         padding-left:0px;
         }
         .con ul li 
         {
         margin-left:22px;
         list-style: disc !important;
         }
         /*td, th {
         padding: 0px 5px !important;
         }*/
      </style>
   </head>
   <body>
      <section id="container" >
         <?php include_once('../include/include_top_menu.php');?>
         <!--sidebar start-->
         <?php include_once('../include/left_menu.php');?>
         <!--sidebar end-->
         <!--main content start-->
         <section id="main-content">
            <section class="wrapper">
               <div class="row">
                  <div class="col-lg-12">
                     <!--breadcrumbs start -->
                     <section class="panel">
                        <header class="panel-heading">
                           <h3><?=$mode.' '.$form?></h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li><a href="<?=ROOT.'pending_job_work_list'?>">Jobwork List</a></li>
                           </ul>
                        </div>
                     </section>
                     <!--breadcrumbs end -->
                  </div>
               </div>
               <!--state overview start-->
               <div class="row">
                  <div class="col-md-12">
                     <section class="panel">
                        <div class="panel-body">
                           <center>
                              <div class="col-md-1"></div>
                              With Logo
                              <br/>
                              <label class="col-md-2 control-label"> Print</label>
                              <div class="col-md-4 col-xs-11">
                                 <form class="form-horizontal" role="form" id="print_form" action="javascript:;" method="post" name="print_form">
                                    <select class="form-control" name="print_status" id="print_status" <?if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
                                       <option value="">Select Print</option>
                                       <option value="1" <?if($_REQUEST['printstatus']=='1'){ echo "selected";}?>>ORIGINAL</option>
                                       <option value="2" <?if($_REQUEST['printstatus']=='2'){ echo "selected";}?>>DUPLICATE</option>
                                       <option value="3" <?if($_REQUEST['printstatus']=='3'){ echo "selected";}?>>TRIPLICATE</option>
                                       <option value="4" <?if($_REQUEST['printstatus']=='4'){ echo "selected";}?>>EXTRA</option>
                                    </select>
                                 </form>
                              </div>
                              <div class="col-md-1">
                                 <input type="checkbox" class="form-control"  name="logo" id="logo" value="1">
                              </div>
                              <div class="col-md-4">
                                 <button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
                                 <a href="<?=ROOT.'invoice_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
                                 <!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to Pdf" onClick="make_pdf()" />-->
                              </div>
                           </center>
                           <div class="col-md-12"></div>
                           <label class="col-md-3 control-label"></label>
                           <div class="col-lg-4">
                           </div>
                           <input type="hidden" name="typename" id="typename" value="<?=$rel['module_name']?>">
                           <?php ob_start(); ?>
                           <div class="col-lg-12 table-responsive" id="receipt_print">
                              <div class="col-md-12" style=" margin-top:10px;" id="print1">
                                 <!-- Fixed Logo Table Start -->
                                 <table width="100%" class="maintable" border="0" style="" id="table_head">
                                    <tr style="border:none;">
                                       <td width="100%" style="border:none;">
                                          <!--<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>-->
                                          <h1 align="center"><?=$set_head['company_name']?></h1>
                                          <h4 align="center" style="padding:top:8px;"><?=$set_head['logo_content']?></h4>
                                          <h4 align="center"><?=$set_head['address']?></h4>
                                          <h4 align="center"><?if($set_head['website']){?>Email: <?=$set_head['website']?><?}?> 
                                             <?if($set_head['contact_no']){?>(M) <?=$set_head['contact_no']?><?}?>
                                          </h4>
                                       </td>
                                    </tr>
                                 </table>
                                 <!-- Fixed Logo Table End -->
                                 <!-- Multipage Table Start -->	
                                 <?php$hed=13+$cnt1 ?>
                                 <table width="100%" class="maintable" style="font-size: 11px;" id="invoice_type" >
                                    <thead>
                                       <tr>
                                          <th colspan="<?=$hed?>" style="padding:0px !important;">
                                             <table style="font-size:10px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
                                                <thead>
                                                   <tr>
                                                      <td style="" colspan="2"> </td>
                                                      <td colspan="3" style="text-align:center;"> 
                                                         <strong class="typetitle" style="font-size:14px;">
                                                         <?//=$rel['module_name']?>
                                                         JOBWORK RECEIPT
                                                         </strong>
                                                      </td>
                                                      <td width="10%" style="text-align:right;"> 
                                                         <strong style="font-size:9px">
                                                         <b class="data_title">ORIGINAL FOR RECIPIENT</b>
                                                         </strong>
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td colspan="2"  style="vertical-align:top;border:0.5px #ccc solid;border-right:none;"><strong>Jobwork No </strong>
                                                      </td>
                                                      <td width="28%" colspan="" style="vertical-align:top;border-bottom:0.5px #ccc solid; border-right:0.5px #ccc solid;border-top:0.5px #ccc solid">: <strong><?=$rel['jobwork_no']?></strong>
                                                      </td>
                                                     	
                                                      <td rowspan="3" colspan="4" width="0%" style="vertical-align:top;border:0.5px #ccc solid;order-right:0.5px #ccc solid;">
                                                         <b style="font-size:12px;">Vendor Details : </b><br/>
                                                         <strong><?=$rel['l_name']?></strong>
                                                         <span style="font-weight:normal;">  <br/>
                                                         <?=$rel['m_address']?>
                                                         <br>
                                                         <?=$rel['city_name']?>, <?=$rel['state_name']?>, <?=$rel['country_name']?>
                                                         <?phpif(!empty($rel['cust_pincode']))
                                                            {	?>
                                                         -  <?=$rel['cust_pincode']?>
                                                         <?php} ?></span>
                                                         <!--<br>
                                                            Mobile no : <?=$rel['cust_mobile']?>-->
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td colspan="2"  style="vertical-align:top;border-bottom:0.5px #ccc solid;border-left:0.5px #ccc solid;"><strong>Jobwork Date </strong>
                                                      </td>
                                                      <td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid" colspan="">: <?=$order_date?>
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td colspan="2" style="vertical-align:top;border-bottom:0.5px #ccc solid;border-left:0.5px #ccc solid;"><strong>Vehicle No </strong></td>
                                                      <td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid" colspan="">: <?=$rel['vehicle_no']?>
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td colspan="4" style="border-right:0.5px #ccc solid;border-left:0.5px #ccc solid;"></td>
                                                      <td colspan="2" style="border-right:0.5px #ccc solid;"><strong>GSTIN: <?=$cons_gst_no?> 
                                                         </strong>
                                                      </td>
                                                   </tr>
                                                   <tr>
                                                      <td colspan="2" width="25%" style="border-left:0.5px #ccc solid;border-bottom:0.5px #ccc solid;font-weight:normal;"></td>
                                                      <td colspan="2" width="23.5%" style="border-right:0.5px #ccc solid;text-align:left;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;font-weight:normal;"></td>
                                                      <td style="text-align:left;border-bottom:0.5px #ccc solid;font-weight:normal;">State : <?=$cons_state_name?></td>
                                                      <td style="text-align:left;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;font-weight:normal;"></td>
                                                   </tr>
                                                </thead>
                                             </table>
                                          </th>
                                       </tr>
                                       <tr>
                                          <th rowspan="2" width="3%" style="text-align:center;border:0.5px #ccc solid;border-top: none;"><strong>SR.<br/> NO.</strong></th>
                                          <th rowspan="2" width="<?=$dynamicwidth?>%" style="text-align:center;border:0.5px #ccc solid;border-top: none;" >
                                             <strong>Product Name</strong>
                                          </th>
                                          <th rowspan="2" width="8%" style="text-align:center;border:0.5px #ccc solid;border-top: none;">
                                             <strong>Process Name</strong>
                                          </th>
                                          <th rowspan="2" width="7%" style="text-align:center;border:0.5px #ccc solid;border-top: none;">
                                             <strong>QTY.</strong>
                                          </th>
                                          <th rowspan="2" width="7%" style="text-align:center;border:0.5px #ccc solid;border-top: none;">
                                             <strong>Unit</strong>
                                          </th>
                                          <th rowspan="2" width="7%" style="text-align:center;border:0.5px #ccc solid;border-top: none;">
                                             <strong>Rate</strong>
                                          </th>
                                          <th rowspan="2" width="4%" style="text-align:center;border:0.5px #ccc solid;border-top: none;">
                                             <strong>Amount</strong>
                                          </th>
                                       </tr>
                                       <tr>
                                       </tr>
                                    </thead>
                                    <tbody style="border: 1px solid;">
                                       <?php
                                          $qry="select jw.*,prom.product_name, prc.process_name, um.unit_name FROM `tbl_jobwork` as jw 
												left join product_mst as prom on jw.j_product_id = prom.product_id
												left join process_mst as prc on jw.j_pr_process_id = prc.process_id
												left join unit_mst as um on jw.process_unit = um.unitid
												where jw.jobwork_main_id='".$jobworkMainId."'
												ORDER BY `jw`.`jobwork_id` ASC";
                                          /*$qry="select * FROM `tbl_invoicetrn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id where trancation_status=0 and product.product_type not in(3) and invoice_id=".$rel['invoice_id'];*/
                                          $result=$dbcon->query($qry);		
                                          $i=1;$total=0;$discount=0;$totalqty=0;
                                          $cnt=brp_mysqli_num_rows($result);
                                          while($row=brp_mysqli_fetch_assoc($result))
                                          {
                                          		$amount = $row['used_qty']*$row['pr_rate'];
                                          		$total = $total+$amount;
                                          ?>
                                       <tr style="height:30px">
                                          <td style="text-align:center;vertical-align:center;border-right:0.5px #ccc solid;border-left:0.5px #ccc solid;">
                                             <?phpecho $i;?>
                                          </td>
                                          <td style="vertical-align:center;border-bottom-color:#FFFFFF; border-right:0.5px #ccc solid;" >
                                             <strong><?=stripcslashes($row['product_name'])?></strong>
                                          </td>
                                          <td style="border-bottom-color:#FFFFFF; border-right:0.5px #ccc solid;vertical-align:center;text-align:center" >
                                             <?=stripcslashes($row['process_name'])?>
                                          </td>
                                          <td style="text-align:center;vertical-align:center;border-bottom-color:#FFFFFF; border-right:0.5px #ccc solid;white-space:nowrap;" >
                                             <?=$row['used_qty']?>	
                                          </td>
                                          <td style="text-align:center;vertical-align:center;border-bottom-color:#FFFFFF; border-right:0.5px #ccc solid;" >
                                             <?=$row['unit_name']?>
                                          </td>
                                          <td style="text-align:center;vertical-align:center;border-bottom-color:#FFFFFF; border-right:0.5px #ccc solid;" >
                                             <?=number_format($row['pr_rate'],2,".","")?>
                                          </td>
                                          <td style="text-align:right;vertical-align:center;border-bottom-color:#FFFFFF;border-right:0.5px #ccc solid;">
                                             <?=number_format($amount,2,".","")?>
                                          </td>
                                       </tr>
                                       <?php
                                          

                                          $get_p_ref_id = "select p_ref_id from tbl_allocate_process where p_id='".$row['j_alloc_process_id']."' and pr_process_type='2' ";
                                           $rs_ref_result=$dbcon->query($get_p_ref_id);		
                                           $response_data=brp_mysqli_fetch_assoc($rs_ref_result);
                                           $p_ref_id = $response_data['p_ref_id'];

                                           $materials_sql = "select rp.rp_id,rp.sp_id,rp.sr_no,rp.rp_po_qty,prom.product_name  FROM `tbl_request_product` 	as rp left join product_mst as prom on rp.rp_pid = prom.product_id
         												where rp.perent_id='".$p_ref_id."' and rp.company_id= '".$_SESSION['company_id']."' ";
         										   $materials_exec=$dbcon->query($materials_sql);
         										   $materials_count=mysqli_num_rows($materials_exec);

         										   if($materials_count > 0){ ?>
										   		<tr style="height:30px">
                                                   <td style="text-align:left;vertical-align:top;border-bottom-color:#FFFFFF; border:0.5px #ccc solid;font-weight:bold" ></td>
		                                          	<td colspan="6" style="text-align:left;vertical-align:top;border-bottom-color:#FFFFFF; border-right:0.5px #ccc solid;border-top:0.5px #ccc solid;border-left:0.5px #ccc solid;font-weight:bold" >Issue Materials:</td>
		                                          </tr>

                                           	<?php	
                                           	$k=1;
                                           	while($materials_data=brp_mysqli_fetch_assoc($materials_exec)){		
	                                        ?>
	                                          <tr style="height:30px;border-bottom:0.5px #ccc solid;">
		                                          <td style="text-align:center;vertical-align:center;border-right:0.5px #ccc solid;border-left:0.5px #ccc solid;">
		                                            
		                                          </td>
		                                          <td style="vertical-align:center;border-bottom-color:#FFFFFF; " >
		                                             <strong><?phpecho $i.".".$k ;?> </strong> <?=stripcslashes($materials_data['product_name'])?>
		                                          </td>
		                                          <td style="border-bottom-color:#FFFFFF; vertical-align:center;text-align:center" ></td>
		                                          <td style="text-align:center;vertical-align:center;border-bottom-color:#FFFFFF; white-space:nowrap;" >
		                                             <?=$materials_data['rp_po_qty']?>	
		                                          </td>
		                                          <td style="text-align:right;vertical-align:center;border-bottom-color:#FFFFFF; " ></td>
		                                          <td style="text-align:right;vertical-align:center;border-bottom-color:#FFFFFF;" ></td>
		                                          <td style="text-align:right;vertical-align:center;border-bottom-color:#FFFFFF;"></td>
		                                       </tr>	
	                                          
	                                       	<?php 
	                                       		$k++;
	                                       		} 
	                                       	} $i++;
	                                       		/*echo '<tr style="height:30px">
		                                          <td style="border-right:0.5px #ccc solid;border-left:0.5px #ccc solid;"></td>
		                                          <td style="border-right:0.5px #ccc solid;"></td>
		                                          <td style="border-right:0.5px #ccc solid;"></td>
		                                          <td style="border-right:0.5px #ccc solid;"></td>
		                                          <td style="border-right:0.5px #ccc solid;"></td>
		                                          <td style="border-right:0.5px #ccc solid;"></Td>
		                                          <td style="border-right:0.5px #ccc solid;"></td>
		                                       </tr>';*/
		                                       
	                                       	 }
	                                          $pr=13-$cnt;
	                                          
	                                          for($j=0; $j<$pr; $j++)
	                                          {
	                                          ?>	
                                       <tr style="height:30px">
                                          <td style="border-right:0.5px #ccc solid;border-left:0.5px #ccc solid;"></td>
                                          <td style="border-right:0.5px #ccc solid;"></td>
                                          <?phpif($set_head['show_disc']=='1'){?>
                                          <td style="border-right:0.5px #ccc solid;"></td>
                                          <?}?>
                                          <td style="border-right:0.5px #ccc solid;"></td>
                                          <td style="border-right:0.5px #ccc solid;"></td>
                                          <td style="border-right:0.5px #ccc solid;"></Td>
                                          <td style="border-right:0.5px #ccc solid;"></td>
                                       </tr>
                                       <?php } ?>
                                       <tr style="height:20px">
                                          <td style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;border-left:0.5px #ccc solid; text-align:right;" colspan="6"><strong>Total</strong></td>
                                          <td style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;text-align:right;"><strong><?//=number_format($total,2,".","")?><?=number_format($total,2,".","")?></strong></td>
                                       </tr>
                                       <tr>
                                          <td colspan="<?=$hed?>" style="padding: 0px !important;border:0.5px #ccc solid">
                                             <table class="footer-table" width="100%">
                                                <?
                                                   
                                                   
                                                   
                                                   ?>
                                                <tr height="20px">
                                                   <td width="61.6%" style="border-right:0.5px #ccc solid;border-top:0.5px #ccc solid;vertical-align:top;" colspan="5" rowspan="3">
                                                   	Remark:
                                                   	<br><?=(($rel['remark']!='0')?$rel['remark']:"")?>
                                                   </td>
                                                   <td colspan="3" width="28.7%" style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;text-align:left">
                                                      Total Amount
                                                   </td>
                                                   <td colspan="2" style="text-align:right; border-top:0.5px #ccc solid;" width="10%"><?=number_format($total,2,".","")?></td>
                                                </tr>
                                                <?php
                                                   $r=round($total)-$total; 
                                                   ?>
                                                <tr height="20px">
                                                   <td colspan="3" style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;text-align:left">Round off :</td>
                                                   <td colspan="2" style="text-align:right; border-top:0.5px #ccc solid;border-right:0.5px #ccc solid; "><?=number_format($r,2,".","")?></td>
                                                </tr>
                                                <tr height="20px">
                                                   
                                                   <td colspan="3" style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;text-align:left"><strong>Grand Total</strong> :</td>
                                                   <td colspan="2" style="text-align:right; border-top:0.5px #ccc solid;border-right:0.5px #ccc solid; "><strong><?=number_format($total,0,".","").'.00'?></strong></td>
                                                </tr>
                                                <tr height="35px">
                                                   <td colspan="<?=5+$colspan?>" style="border:0.5px #ccc solid;border-left:none;"></td>
                                                </tr>
                                                <tr>
                                                   <td colspan="<?=$colspan?>" style="vertical-align:top;border:0.5px #ccc solid;
                                                      border-right:none;border-left:none;font-size:10px;text-align:left" class="con">
                                                      <?phpif(!empty($set_head['conditions'])){ ?>
                                                      <strong>Terms and Conditions:</strong><br> <?=$set_head['conditions']?>
                                                      <?php} ?>	<br/><br/>
                                                      <!--<span style="vertical-align:bottom;">E & O.E.</span>-->
                                                   </td>
                                                   <td colspan="5" style=" border:0.5px #ccc solid;border-left:none;vertical-align:top;">
                                                      <center>
                                                         For, <strong> <span style="font-size:10px;text-decoration:bold;">
                                                         <?=$set_head['company_name']?></span></strong>
                                                      </center>
                                                      <br><br><br><br>
                                                      <center style="vertical-align:bottom;">Authorised Signatory</center>
                                                   </td>
                                                </tr>
                                             </table>
                                          </td>
                                       </tr>
                                    </tbody>
                                 </table>
                                 <!-- Multipage Table End -->		
                                 <!--<center><span style="float:left;">E.& O. E.</span>This is a Computer Generated Invoice</center>-->
                              </div>
                              <div id="print2" style="margin-top:0in;"></div>
                              <div id="print3" style="margin-top:0in;"></div>
                           </div>
                           <?php  
                              $contents = ob_get_contents();
                              $_SESSION['contents']=$contents;
                              $_SESSION['file_name']='invoice-#';
                              $_SESSION['invoice_no']=$rel['invoice_no'];
                              $_SESSION['page_size']='A4';
                              echo "<script> function make_pdf()
                              { window.open('".ROOT."export/print','_blank');
                              }</script>";  
                              ?>
                        </div>
                     </section>
                  </div>
               </div>
               <!--state overview end-->
            </section>
         </section>
         <!--main content end-->
         <!--footer start-->
         <?php include_once('../include/footer.php');?>
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once('../include/include_js_file.php');?>   
      <script src="<?=ROOT?>js/app/invoice.js"></script>
      <!--<script src="js/count.js"></script>-->
      <script type="text/javascript"> 
         function print_receipt()
         {
         	var originalContents = document.body.innerHTML;
         	//var duplicate = $("#invoiceprint").clone().prepend("<hr style='border-color:#000; border-style:solid; margin:10px 0' />").appendTo("#invoiceprint");
         	var printContents = document.getElementById('receipt_print').innerHTML;     
             document.body.innerHTML = printContents;
             window.print();
             document.body.innerHTML = originalContents;
         }
         
         function PrintMe(DivID) {
         
         if($('#print_status').val()=='')
         {
         alert('Select PrintType');
         }
         else
         {
         
         
         if($('#print_status').val()<=3)
         {	
         for(var i=1;i<$('#print_status').val();i++)
         {	
         	if($("#invoice").val()==2)
         	{
         		$("#print"+i+" .data_title").html('Performance');
         		$("#type").html("Performance Invoice");
         	}
         	if($("#invoice").val()==1)
         	{
         		$("#print"+i+" .data_title").html('ORIGINAL FOR RECIPIENT');
         		$("#type").html($("#typename").val());
         	}
         	if(i<$('#print_status').val())
         	{
         		$("#print"+i).after('<div class="page"></div>');
         	}
         	$("#print"+(i+1)).html($("#print1").clone());
         	if((i+1)==2)
         	{
         		$("#print"+(i+1)+" .data_title").html('DUPLICATE FOR SUPPLIER');
         	}
         	if((i+1)==3)
         	{
         		$("#print"+(i+1)+" .data_title").html('TRIPLICATE FOR TRANSPORTER');
         	}
         	
         }
         }
         else
         {
         	$("#print1 .data_title").html('EXTRA');
         }
           //var duplicate = $("#receipt_data").clone().appendTo("#receipt_duplicate");
           var disp_setting="toolbar=yes,location=no,";
           disp_setting+="directories=yes,menubar=yes,";
           disp_setting+="scrollbars=yes,width=800, height=600, left=100, top=25";
           var content_vlue = document.getElementById(DivID).innerHTML;
           var docprint=window.open("","",disp_setting);
           docprint.document.open();
           docprint.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"');
           docprint.document.write('"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
           docprint.document.write('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">');
           docprint.document.write('<head><title><?phpecho TITLE;?></title>');
           docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/style.css" media="all"/>');
           docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/bootstrap.min.css" media="all"/>');
           docprint.document.write('<style type="text/css">');
         	if ($('input[name=logo]:Checked').val() == "1") {
         	    $('#table_head').show();
         		$('#table_foot').show();
         		docprint.document.write('@media print{ @page { size:A4; margin: 0.2in <?=$set_head['letter_head_right_margin']?>in 0.2in <?=$set_head['letter_head_left_margin']?>in; } }   ');
         	}
         	else
         	{
         		docprint.document.write('@media print{ @page { size:A4; margin: <?=$set_head['letter_head_top_margin']?>in <?=$set_head['letter_head_right_margin']?>in <?=$set_head['letter_head_bottom_margin']?>in <?=$set_head['letter_head_left_margin']?>in; } }  #table_head, #table_foot { display:none }');
         		//$('#invoice_type').css('margin-top','1.7in');	
         	}
          
           docprint.document.write('body { font-family:Tahoma;color:#000;font-size:10px;}');
           docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } tr { page-break-inside: avoid } .maintable tbody tr { border-bottom:0 px #ccc solid; }');
           docprint.document.write(' .maintable table { page-break-inside:auto } .maintable tr{ page-break-inside:avoid; page-break-after:auto } .maintable thead { display:table-header-group }  .maintable tfoot tr{ /*display:table-footer-group;*/ page-break-inside:avoid; page-break-before:always; } footer-table{ page-break-inside:avoid; page-break-before:always;  }</style>');
           docprint.document.write('</head><body onLoad="self.print()">');
           docprint.document.write(content_vlue);
           docprint.document.write('</body></html>');
           docprint.document.close();
           docprint.focus();
         	$('#table_head').show();
         	//$('#invoice_type').css('margin-top','0px');
         
           }
           location.reload();
         }
      </script>
   </body>
</html>