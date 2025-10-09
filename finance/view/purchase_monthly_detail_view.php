<?php
session_start();
$path = '../../';
$include = '../../include/';
$include1 = '../include/';
include_once($path."config/config.php");
include_once($path."config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

$form="Monthly Purchase Details";

$ledger_id = $_REQUEST['ledger_id'];
$month = $_REQUEST['month'];
$type = isset($_REQUEST['type'])?$_REQUEST['type']:'';
/* $dates = get_financial_year();
$v=extract($dates);
 $year = date('Y');
if(!in_array($month, array(1,2,3))){
    $year = $year -1;
} */

$datemo=explode("-",$month);
//print_r($datemo);
$month_m=$datemo['0'];
$year_y=$datemo['1'];

      
//$date = new DateTime($start_date);
//$start_date = $date->format($year.'-'.$month.'-d');
//$end_date = $date->format($year.'-'.$month.'-t');

$start_date=$_SESSION['balance_sheet_start_date'];
$end_date=$_SESSION['balance_sheet_end_date'];

//$start_date = date('2020-11-01');
//$end_date = date("Y-m-d");
$where_date = (isset($end_date) && !empty($end_date)) ? " between '".$start_date."' and '".$end_date."'" : " < '".$start_date."'" ;

//echo $where_date;
$ca_entries = array();
if($ledger_id){
   $ledger_name = $dbcon->query("SELECT l_name as ledger_name FROM `tbl_ledger` WHERE `l_id` = ".$ledger_id)->fetch_object()->ledger_name;
}

$group_id = get_id_detail($dbcon,'tbl_ledger','l_id',$_REQUEST['ledger_id'],'l_group');

?>
<!DOCTYPE html>
<html lang="en">
    <head>
            <?php include_once($include.'include_css_file.php');?>
    </head>
    <body>
        <style type="text/css">
        .link_dash
	{
		border-bottom:dotted blue thin;
	}

        </style>
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
										<li><a href="<?=ROOT.FINANCE_ROOT.'purchase_monthly_view'?>"> Monthly Purchase Report</a>  </li>
										<li><?=$form?> Report</li>
                                    </ul>
                                </div>
                            </section>
                        </div>	
                    </div>
                    <div class="row">			
                        <div class="col-sm-12">
                            <section class="panel">
                                <header class="panel-heading"><?=$ledger_name?></header>	
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-12"  style="margin-top:10px;">
                                            <table  class="display table table-bordered table-striped">
                                                <thead>
                                                    <tr>
							<th width="15%" style="text-align:center">Date</th>
							<th width="46%" style="text-align:center">Particulars</th>
                                                        <th width="12%" style="text-align:center">Vch Type</th>
                                                        <th width="12%" style="text-align:center">Vch No</th>
							<th width="10%" style="text-align:center">Debit Amount</th>
							<th width="15%" style="text-align:center">Credit Amount</th>
                                                    </tr>
                                                </thead>
            <?php
				  $ca_qry = "SELECT pbill.order_date,pbill.po_id,pbill.order_no,pbill.g_total,ledger.l_name FROM tbl_pono as pbill left join tbl_ledger as ledger on ledger.l_id=pbill.vender_id where month(pbill.order_date)=".$month_m." and year(pbill.order_date)= ".$year_y." and pbill.status=0 and pbill.company_id = ".$_SESSION[company_id]."  Order by pbill.order_date ASC";

                   // echo $ca_qry;exit;
        
                $result = mysqli_query($dbcon, $ca_qry);
                $ca_result = mysqli_fetch_all($result,MYSQLI_ASSOC);

                //echo '<pre>';        print_r($ca_result); exit;
				$total_main=0;
                if($ca_result){
                    foreach ($ca_result as $value)
					{
                        //echo '<pre>';        print_r($ca_result); //exit;
                        $str.='<tr>
                                    <td data-label="DATE" style="text-align:left">'.date('d/m/Y',strtotime($value["order_date"])).'</td>
	<td  style="text-align:left"><a style="color: inherit;" href="'.ROOT.PURCHASE_ROOT.'purchaseedit/'.$value['po_id'].'">'.$value["l_name"].'</a></td>
									
									<td  style="text-align:left">Purchase</td>
									<td  style="text-align:right">'.$value["order_no"].'</td>
									<td  style="text-align:center">0</td>
									<td  style="text-align:right">'.$value["g_total"].'</td>
									
									
									
									</tr>';
									
									$total_main = $total_main +$value["g_total"];
                                    
                        
                    }
                }
				 $str.='<tr>
                                    <td colspan="4" style="text-align:right">Total</td>
									<td colspan="" style="text-align:center">0</td>
									<td colspan="" style="text-align:right">'.$total_main.'</td>
						</tr>';
                echo $str; ?>
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

