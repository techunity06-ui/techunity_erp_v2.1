<?php
session_start();
$path = '../../';
$include = '../../include/';
$include1 = '../include/';
include_once($path."config/config.php");
include_once($path."config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

$form="Sales Details By Month ";

 $query = "SELECT sum(g_total) as g_totall,MONTHNAME(invoice_date) month_name, MONTH(invoice_date) as month,YEAR(invoice_date) as year FROM tbl_invoice where invoice_status=0 GROUP BY month ORDER BY invoice_date";
$result = mysqli_query($dbcon, $query);

// Initialize empty array for the report
$report = array();

// Loop through the purchase records and group them by month
$payment_result = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);


foreach ($payment_result as $payment)
	{
		$report[$payment['month_name']]['month'] = $payment['month'];
		$report[$payment['month_name']]['year'] = $payment['year'];
		$report[$payment['month_name']]['g_totall'] = $payment['g_totall'];
	}
	 
	
    
	 

// Calculate the total purchase amount for each month
foreach ($report as $month => $purchases) {
    $total = 0;
    
        $total += $purchases['g_totall'];
   
    $report[$month]['total'] = $total;
	
}



?>
<!DOCTYPE html>
<html lang="en">
    <head>
            <?php include_once($include.'include_css_file.php');?>
            <style type="text/css">
                .border_down
                {
                    border-bottom: blue thin dotted;
                }
            </style>
    </head>
    <body>
        <section id="container">
        <?php include_once($include.'include_top_menu.php');?>
        <?php include_once($include.'left_menu.php');?>
            <section id="main-content">
                <section class="wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <section class="panel">
                                <header class="panel-heading"><h3><?=$mode.' '.$form?></h3></header>	
                                <div class="">
                                    <ul class="breadcrumb">
                                        <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="<?=ROOT.FINANCE_ROOT ?>finance_report_list">Finance Report List</a></li>
                                        

										<li><?=$form?></li>
                                      
                                    </ul>
                                </div>
                            </section>
                        </div>	
                    </div>
                    <div class="row">			
                        <div class="col-sm-12">
                            <section class="panel">
                                <header class="panel-heading"><?=$form?>Report</header>	
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-12"  style="margin-top:10px;">
                                            <table  class="display table table-bordered table-striped">
                                                <thead>
<!--                                                    <tr>
                                                        <td colspan="3" style="text-align: right;"><b>Opening Balance</b></td>
                                                        <td><?= $balance; ?></td>
                                                    </tr>-->
                                                    <tr>
							<th width="55%" style="text-align:center">Month</th>
                                                        <th width="15%" style="text-align:center">Debit</th>
							<th width="15%" style="text-align:center">Credit</th>
                                                        <th width="15%" style="text-align:center">Closing Balance</th>
                                                    </tr>
                                                </thead>
                                            <?php
                                            $ca_value = 0; $opening_balance = 0; $closing_bal = 0;$i=1;
                                            //if($ca_entries && !empty($ca_entries)){ 
                                                //echo "<pre>"; print_r($ca_entries); echo "</pre>";
                                                foreach ($report as $month => $data) {
                                                    //if($opening_balance == 0){
                                                      //  $opening_balance = $balance;
                                                    //}
                                                    $closing_bal = $opening_balance + ($data['total'] - 0);
                                                    ?>
                                                    <tr>
                                                        <td tabindex="<?= $i ?>"><a style="color: inherit;" href="sales_monthly_detail_view.php?month=<?=$data['month']?>-<?=$data['year'] ?>"><?= $month ."-". $data['year']?></a></td>
                                                       <!-- <td><a style="color: inherit;" href="ledger_form/<?=$ledger_id?>&month=<?= $amount['month']?>" target="_blank"><?= $month ?></a></td>-->
													   <td style="text-align: right;"> 0</td>
                                                        <td style="text-align: right;"> <?= indian_number($data['total'],2) ?></td>
                                                        
                                                        <td style="text-align: right;"> <?= indian_number(abs($closing_bal),2) ?></td>
                                                    </tr>
                                                <?php
                                                    $opening_balance = $closing_bal;
                                                    $debit_total += $data['total'];
                                                   // $credit_total += $amount['credit'];
                                                  
                                                }
                                            //} ?>
                                                <tr>
                                                    <td style="text-align: left;"><strong>Grand Total</strong></td>
													 <td style="text-align: right;"><strong>0</strong></td>
                                                    <td style="text-align: right;"><strong><?= indian_number($debit_total,2) ?></strong></td>
                                                   
                                                    <td style="text-align: right;"><strong><?= indian_number(abs($closing_bal),2) ?></strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </section>
            </section>
        <?php include_once($include.'footer.php');?>
        </section>
        <?php include_once($include.'include_js_file.php');?> 
        
        <script>
        	  /* Added By Jayesh 30-07-2021 For tab and enter key */   
		window.onkeyup = function(e){
		var event = e.which || e.keyCode || 0; // .which with fallback
			var current = $(':focus');
			var id = current.attr("tabIndex");
			var current_link = current.find('a:first').attr('href');
			if(event== 13)
			{
				if(typeof current_link == "undefined"){
					return false;
				}
				else{
					window.location=root_domain+finance_root_domain+current_link; // Navigate to URL	
					return false;					
				}
			}
			if(event== 27)
			{
				history.back();	
				return false;
			}			   	
		}
        /* Added By Jayesh 30-07-2021 For tab and enter key */   
        </script> 
    </body>
</html>

