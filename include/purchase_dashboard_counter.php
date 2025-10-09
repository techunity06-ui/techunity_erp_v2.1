<?php 
   $start_date= date('d-m-Y', strtotime('first day of last month'));
   $end_date=date("d-m-Y");
   $user_ids = check_user_chein($dbcon,$_SESSION['user_id'],1);
   
   ?>
<!--<link href="assets/morris.js-0.4.3/morris.css" rel="stylesheet" />-->
<div class="" id="purchase_dashboard">
<div class="row">
   <div class="col-md-12">
      <section class="panel1">
         <div class="panel-body">
            <div class="row state-overview">
               <div class="icons icons12">
                  <div class="icon1 terques">
                     <i class="fa fa-money" aria-hidden="true"></i>
                  </div>
                  <div class="">
                     <h1 class="count" id="business_achieved_counts">182.05</h1>
                     <p style="color: #4c4e4e;">Spend</p>
                  </div>
               </div>
               <div class="icons icons12">
                  <div class="icon1 yellow">
                     <i class="fa fa-users" aria-hidden="true"></i>
                  </div>
                  <div class="">
                     <h1 class="count" id="opportunity_onhand_counts">2.70</h1>
                     <p style="color: #4c4e4e;">Suppliers</p>
                  </div>
               </div>
               <div class="icons icons12">
                  <div class="icon1 pink">
                     <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                  </div>
                  <div class="">
                     <h1 class="count" id="pending_quotation_counts">190</h1>
                     <p style="color: #4c4e4e;">Transactions</p>
                  </div>
               </div>
               <div class="icons icons12">
                  <div class="icon1 success">
                     <i class="fa fa-bar-chart-o" aria-hidden="true"></i>
                  </div>
                  <div class="">
                     <h1 class="count" id="lost_opportunity_counts">343</h1>
                     <p style="color: #4c4e4e;">PO Count</p>
                  </div>
               </div>
               <div class="icons icons12">
                  <div class="icon1 mustard">
                     <i class="fa fa-book" aria-hidden="true"></i>
                  </div>
                  <div class="">
                     <h1 class="count" id="hot_leads_counts">254</h1>
                     <p style="color: #4c4e4e;">Invoice Count</p>
                  </div>
               </div>
            </div>
         </div>
      </section>
   </div>
</div>
<div class="row">
   <div class="col-md-6">
      <section class="panel">
           <header class="panel-heading label_heading_size" >
             Spend By Category Level :
         </header> 
         <div class="panel-body" >
            <div class="row state-overview">
               <div class="col-md-12 col-sm-6">
                  <section class="panel">
                     <div class="row">
                        <form>
                           <div class="col-md-12">
                              <div id="lead_source_container" style="height: 300px; width: 100%;"></div>
                           </div>
                        </form>
                     </div>
                  </section>
               </div>
            </div>
         </div>
      </section>
   </div>
   <div class="col-md-6">
      <section class="panel">
        <header class="panel-heading label_heading_size" >
             Spend By Category Level 2 :
         </header>
         <div class="panel-body" >
            <div class="row state-overview">
                  <section class="panel">
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">ALLU. CASTING</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">14.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:60%">
                                  <span class="sr-only">34.866</span>
                                </div>
                              </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">ELECTRICAL</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">21.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:45%">
                                  <span class="sr-only">21.01</span>
                                </div>
                              </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">MISLLANIOUS</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">14.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:30%">
                                  <span class="sr-only">14.16</span>
                                </div>
                              </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">PNEUMATIC</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">11.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:21%">
                                  <span class="sr-only">14.16</span>
                                </div>
                              </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">SEMI FINISH</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">6.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:15%">
                                  <span class="sr-only">14.16</span>
                                </div>
                              </div>
                        </div>
                     </div>
                  </section>
            </div>
         </div>
      </section>
   </div>
</div>
<div class="row">
   <div class="col-md-6">
     <section class="panel">
        <header class="panel-heading label_heading_size">
             Spend By Category Level 3 :
         </header>
         <div class="panel-body" >
            <div class="row state-overview">
                  <section class="panel">
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">MACHINE SHOP</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">43.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:60%">
                                  <span class="sr-only">34.866</span>
                                </div>
                              </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">BELT & PULLY</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">32.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:21%">
                                  <span class="sr-only">21.01</span>
                                </div>
                              </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">LINEAR & BLOCK</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">12.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:25%">
                                  <span class="sr-only">14.16</span>
                                </div>
                              </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">PLASTIC</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">10.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:11%">
                                  <span class="sr-only">14.16</span>
                                </div>
                              </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">MACHINE</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">18.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:35%">
                                  <span class="sr-only">14.16</span>
                                </div>
                              </div>
                        </div>
                     </div>
                  </section>
            </div>
         </div>
      </section>
   </div>
    <div class="col-md-6">
     <section class="panel">
        <header class="panel-heading label_heading_size">
             Spend By Category Level 4 :
         </header>
         <div class="panel-body" >
                  <section class="panel">

                     <div style="height:250px; overflow:auto;overflow-x: hidden;" class="row" id="list_table_div">
                          <table class="display table table-bordered table-striped" id="vendor_table">
                             <thead>
                                <tr >
                                   <th class="purchase_table">Category Level 4</th>
                                   <th class="purchase_table">Spend</th>
                                   <th class="purchase_table">Transaction</th>
                                   <th class="purchase_table">Supplier</th>
                                </tr>
                             </thead>
                             <tbody>
                                <tr>
                                    <td>ROLLER PRESS</td>
                                    <td>166.76</td>
                                    <td>1654</td>
                                    <td>4</td>
                                </tr>
                                <tr>
                                    <td>SPINDLE MOULDER</td>
                                    <td>216.76</td>
                                    <td>4221</td>
                                    <td>4</td>
                                </tr> 
                                <tr>
                                    <td>AUTOMATIC EDGE BANDER</td>
                                    <td>126.76</td>
                                    <td>1982</td>
                                    <td>5</td>
                                </tr> 
                                <tr>
                                    <td>COLD PRESS</td>
                                    <td>321.76</td>
                                    <td>2113</td>
                                    <td>11</td>
                                </tr>
                                <tr>
                                    <td>MULTIBORING </td>
                                    <td>11.76</td>
                                    <td>2</td>
                                    <td>2</td>
                                </tr>
                                <tr>
                                    <td>CGEARBOX</td>
                                    <td>121.76</td>
                                    <td>213</td>
                                    <td>6</td>
                                </tr>
                                <tr>
                                    <td>HEATER</td>
                                    <td>921.76</td>
                                    <td>3113</td>
                                    <td>21</td>
                                </tr>
                                <tr>
                                    <td>MISLLANIOUS</td>
                                    <td>21.76</td>
                                    <td>113</td>
                                    <td>2</td>
                                </tr>    
                             </tbody>
                          </table>      
                  </section>
         </div>
      </section>
   </div>
</div>
   <div class="row">
   <div class="col-md-6">
     <section class="panel">
        <header class="panel-heading label_heading_size">
             Supplier Search :
         </header>
         <div class="panel-body" >
            <div class="row state-overview">
                  <section class="panel">
                    <div class="main">
                      <!-- Actual search box -->
                      <div class="form-group has-feedback has-search">
                        <span class="glyphicon glyphicon-search form-control-feedback"></span>
                        <input type="text" class="form-control" placeholder="Search">
                      </div>
                    </div>   
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">Accurate Engg.</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">43.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:60%">
                                  <span class="sr-only">34.866</span>
                                </div>
                              </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">Adarsh House</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">32.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:21%">
                                  <span class="sr-only">21.01</span>
                                </div>
                              </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">Ansuya Indus.</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">54.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:25%">
                                  <span class="sr-only">14.16</span>
                                </div>
                              </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-3">  <label class="prgressbar-value" style="float: right;">Aptus Automa.</label> 
                        </div>
                        <div class="col-md-8">
                              <div class="progress">
                                <label class="prgressbar-value">10.866</label>
                                <div class="progress-bar" role="progressbar"  aria-valuemax="100" style="width:11%">
                                  <span class="sr-only">14.16</span>
                                </div>
                              </div>
                        </div>
                     </div>
                     
                  </section>
            </div>
         </div>
      </section>
   </div>
</div>
</div>
<?php include_once('../include/add_flp_hist.php');?>
<script type="text/javascript">
   $(document).ready(function() {
    Loading(true);  
    load_lead_by();
    
    Unloading();
   });
   
   function load_lead_by()
   {
  
       var chart = new CanvasJS.Chart("lead_source_container", {
        theme: "light2",
        animationEnabled: true,
        title: {
            text: ""
        },
        data: [{
            type: "doughnut",
            radius: "100%", 
            innerRadius: "50%",
            indexLabelPlacement: "outside", 
            indexLabel: "{symbol} - {y}",
            yValueFormatString: "#,##0.0\"\"",
            showInLegend: false,
            legendText: "{label} : {symbol}",
            dataPoints: [
                   {y: 34.45, symbol: "PRIMARY", label: 0},
                   {y: 7.31, symbol: "PANEL SAW", label: 0},
                   {y: 8.06, symbol: "EDGE BANDER", label: 0},
                   {y: 4.91, symbol: "COLD PRESS", label: 0},
                   {y: 1.26, symbol: "MULTIBORING ", label: 0},
                   {y: 12.91, symbol: "COLD PRESS", label: 0},
                   {y: 6.91, symbol: "AUTOMATIC EDGE BANDER", label: 0},
                   {y: 32.91, symbol: "BEAM SAW", label: 0},
                   {y: 2.91, symbol: "SPINDLE MOULDER", label: 0},
                   {y: 23.91, symbol: "FEEDER", label: 0},
                   {y: 9.91, symbol: "HOT PRESS", label: 0},
                   {y: 4.91, symbol: "FOUR SIDE MOULDER", label: 0},
                   {y: 1.91, symbol: "HYLAM SHEET", label: 0},
                   {y: 43.91, symbol: "BRASS CASTING", label: 0}
               ]
        }]
       });
       chart.render(); 
            
   }
</script>

<style type="text/css">
    #purchase_dashboard .progress-bar{
        height: 30px;
    }
    #purchase_dashboard .progress{
        height: 30px;
    }
    .prgressbar-value{
        color: #4c4e4e;
        line-height: 30px;
        font-size: 18px;
        font-weight: normal;
    }
    .label_heading_size{
        font-weight: bold;font-size: 19px;color:#000
    }
    .purchase_table {
        background-color: #337ab7;
        color: white !important;
        height: 30px;
        text-align: center;
        padding: 12px !important;
    }
    .main {
    width: 90%;
    margin: 10px auto;
}

/* Bootstrap 3 text input with search icon */
.has-search .form-control-feedback {
    right: initial;
    left: 0;
    color: #ccc;
}
.has-search .form-control {
    padding-right: 12px;
    padding-left: 34px;
}
</style>