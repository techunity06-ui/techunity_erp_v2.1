<?php 
   session_start();
 	 include('../include/urlfile.php');
   $form="Current FG Stock";
   $infopage = pathinfo( __FILE__ );
   /*$pro_id = '362';
   $unit_id = '3';
   $qty = get_current_stock_new($dbcon,$pro_id,$unit_id);
  // $reserve_qty = reserve_stock($dbcon,$pro_id,$unit_id);
   echo $qty;die;*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <title>CURRENT STOCK</title>
<?php include_once($include.'include_css_file.php');?>

</head>
<body>
<section id="container">
<?php include_once($include.'include_top_menu.php');?>
<!--sidebar start-->
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
<!--                 <span class="tools pull-right">
                     <a href="<=ROOT.PRODUCTION_ROOT.'report_list'?>"><button type="button" class="btn btn-info"><i class="fa fa-long-arrow-left" aria-hidden="true"></i> Report List</button></a> 
                  </span>-->
                  
                  <h3 style=""><?=$form?> </h3>
               </header>   
               <div class="">
                  <ul class="breadcrumb">
                     <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                     <li><a href="<?=ROOT.PRODUCTION_ROOT.'production_report_list'?>"> Report List</a></li>
                     <li><?=$form?></li>
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
                   
             
               <div class="col-md-5">
                  <div class="form-group">
                  <label class="control-label col-md-4">Product</label>
                     <div class="col-md-7">
                        <select class="select2" id="product_id" name="product_id[]" onchange="generate_chart_report()" placeholder="Choose Products" multiple="multiple">
                           <?=getproduct($dbcon,"");?>
                        </select>
                     </div>
                  </div>
               </div>
               <div class="clearfix"></div>
               </header>   
               <div class="clearfix"></div>
               <div class="row">
                  <div class="col-md-12 margin_row">
                     <div class="col-md-2">
                        <button class="btn btn-dark btn-flat" onClick="clear_lead_by_source_report();" style="margin-right:20px;"><i class="fa fa-remove"></i> Clear Chart</button>
                     </div>
                     <div class="col-md-8">
                        <div id="report_current_fg_stock" style="width: 900px; height: 400px;"></div>
                     </div>
                     <div class="col-md-2">
                     </div>
                  </div>
               </div>
               <div class="panel-body">
                  <div class="adv-table">
                        <table class="display table table-bordered table-striped" id="po-req-table">
                           <thead>
                             <tr>
                              <th>#</th>
                              <th>Product Name</th>
                              <th>Qty</th>
                             </tr>
                           </thead>
                           <tbody id="product_qty"></tbody>           
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
<?php
   include_once('../include/footer.php');
?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
      <script src="<?=ROOT.PRODUCTION_ROOT?>js/app/current_fg_stock.js?<?=time()?>"></script>
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
</script>

</body>
</html>
