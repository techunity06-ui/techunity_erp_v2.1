<?php
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include("../include/function_database_query.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$form="Customer Advance ";

$cust_id = $_REQUEST['id'];

$ca_entries = array();
//if($cust_id){
        $excess_qry = "select rep.receipt_id,rep.receipt_date as ref_date,rep.receipt_no as ref_no,excess_id as ref_id,excess_amount as ref_amount,rep.payment_mode_id,
            (select IFNULL(sum(total_amount),0) as qty from tbl_receipt_trn as trn where status=0 and payment_type=2 and inv.excess_id=trn.excess_id) as pay_amount,inv.cdate 
            FROM tbl_excess as inv 
            left join tbl_receipt as rep on rep.receipt_id=inv.receipt_id 
            where inv.status=0 and excess_type=1 AND inv.cust_id= ".$cust_id." and inv.excess_amount>(select IFNULL(sum(total_amount),0) as qty from tbl_receipt_trn as trn where status=0 and payment_type=2 and inv.excess_id=trn.excess_id)";
        $result = mysqli_query($dbcon,$excess_qry);
        $excess_data = mysqli_fetch_all($result,MYSQLI_ASSOC);
        //p($excess_data,FALSE);
        
//}
        

?>
<!DOCTYPE html>
<html lang="en">
    <head>
            <?php include_once('../include/include_css_file.php');?>
    </head>
    <body>
        <section id="container">
        <?php include_once('../include/include_top_menu.php');?>
        <?php include_once('../include/left_menu.php');?>
            <section id="main-content">
                <section class="wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <section class="panel">
                                <header class="panel-heading"><h3><?=$mode.' '.$form?></h3></header>	
                                <div class="">
                                    <ul class="breadcrumb">
                                        <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                        <li><?=$form?> Report</li>
                                    </ul>
                                </div>
                            </section>
                        </div>	
                    </div>
                    <div class="row">			
                        <div class="col-sm-12">
                            <section class="panel">
                                <header class="panel-heading"><?=$group_name?> REPORT</header>	
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-12"  style="margin-top:10px;">
                                            <table  class="display table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Sr. No.</th>
                                                        <th>Receipt No.</th>
                                                        <th>Payment Mode</th>
                                                        <th>Amount</th>
                                                        <th>Date</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                            <?php
                                            if($excess_data && !empty($excess_data)){
                                                foreach ($excess_data as $key => $excess) { 
                                                    $ledger_name = $dbcon->query("select l_name from tbl_ledger where l_id=".$excess["payment_mode_id"])
                                                        ->fetch_object()->l_name;
                                                    
                                                    $edit_btn = '<a class="btn btn-xs btn-info" data-original-title="Adjust Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'receiptedit/'.$excess["receipt_id"].'" target="_blank"><i class="fa fa-pencil"></i></a></button>';
                                                    ?>
                                                    <tr>
                                                        <td><?= ($key+1) ?></td>
                                                        <td><?= $excess["ref_no"] ?></td>
                                                        <td><?= $ledger_name  ?></td>
                                                        <td><?= indian_number($excess["ref_amount"],2); ?></td>
                                                        <td><?= date('d-m-Y', strtotime($excess["ref_date"])) ?></td>
                                                        <td><?= $edit_btn ?></td>
                                                    </tr>
                                                <?php 
                                                }
                                            }
                                            ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </section>
            </section>
        <?php include_once('../include/footer.php');?>
        </section>
        <?php include_once('../include/include_js_file.php');?>  
    </body>
</html>

