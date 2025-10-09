<?php 
session_start();
include('../include/urlfile.php');

$qry = "select * from tbl_grn where grn_status = 0 and ref_type = 2";
$res = $dbcon->query($qry);
$i = 1;

$str = '<table style="border:1px solid black;text-align:center">
            <tbody> 
                <tr style="border:1px solid black;text-align:center">
                <th style="border:1px solid black;text-align:center">sr</th>
                    <th style="border:1px solid black;text-align:center">grn_trn_id</th>
                    <th style="border:1px solid black;text-align:center">grn_trn_sub_id</th>
                    <th style="border:1px solid black;text-align:center">po_trn_id</th>
                    <th style="border:1px solid black;text-align:center">rp_id</th>
                    <th style="border:1px solid black;text-align:center">product_qty</th>
                    <th style="border:1px solid black;text-align:center">c_qty</th>
                </tr>';
while($rw = brp_mysqli_fetch_array($res)){

 $query = "select * from tbl_grn_trn where grn_trn_status = 0 and ref_type = 2 and grn_id  = " . $rw['grn_id'];
// echo "</br></br>";
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);

    while($row = brp_mysqli_fetch_array($result)){
        $info2['status'] = 2;
         $updateid =   update_record('tbl_grn_sub_trn', $info2, "purchaseordertrn_id=" . $row['purchaseordertrn_id'] . " and grn_trn_id = " . $row['grn_trn_id'], $dbcon);


            grn_po_sub_trn_new($dbcon, $row['grn_trn_id'], $row['purchaseordertrn_id']);
            
       
    }
}
$str .= '</tbody></table>';

echo $str;



function grn_po_sub_trn_new($dbcon, $grn_trn_id, $purchaseordertrn_id, $job_work_po_trn_id = 0)
{
    echo "</br></br>";
     $query1 = "select po.*, `pm`.`tolerance`, `pm`.`maximum_tolerance`, `pm`.`minimum_tolerance`
    from tbl_grn_trn as po
    left join product_mst as pm ON `po`.`product_id` = `pm`.`product_id`
    where `po`.`grn_trn_status` IN (0,3) and `po`.`grn_trn_id` =" . $grn_trn_id;
    $rs_product1 = $dbcon->query($query1);
    $row1 = brp_mysqli_fetch_array($rs_product1);
    $qty = $row1['product_qty'];
    
    $min_toll = $row1['minimum_tolerance'];
    $max_toll = $row1['maximum_tolerance'];
    
     $query = "select * from tbl_purchaseordertrn as po where purchaseordertrn_status=0 and purchaseordertrn_id in (" . $purchaseordertrn_id . ")";
    $rs_product = $dbcon->query($query);

    while ($row = brp_mysqli_fetch_array($rs_product))
    {

        $query2 = "select IFNULL(sum(product_qty),0) as used_qty from tbl_grn_sub_trn as po where status=0 and purchaseordertrn_id =" . $row['purchaseordertrn_id'];
        $rs_product2 = $dbcon->query($query2);
        $row2 = brp_mysqli_fetch_array($rs_product2);
        $poqty = $row['product_qty'] - $row2['used_qty'];

        // Calculate tollearance minimum and maximum
        $min_toll_qty = ($row['product_qty'] * $min_toll) / 100;
        $minium_tollerance_qty = $row['product_qty'] - $min_toll_qty;

        $max_toll_qty = ($row['product_qty'] * $max_toll) / 100;
        $maximum_tollerance_qty = $row['product_qty'] + $max_toll_qty;

        if ($row2['used_qty'] == '')
        {
            $usedQty = $qty;
        }
        else
        {
            $usedQty = $row2['used_qty'] + $qty;
        }

        if ($qty != 0)
        {
            if ($qty != "")
            {

                if ($qty >= $poqty || $usedQty >= $minium_tollerance_qty)
                {

                     $que = "select * from product_mst as ta where product_id=" . $row['product_id'];
                    $rs_di = $dbcon->query($que);
                    $re = brp_mysqli_fetch_assoc($rs_di);

                        $type = "conv_unit";
                        if ($qty >= $poqty)
                        {
                            $base_stock = $poqty;
                        }
                        else
                        {
                            $base_stock = $qty;
                        }
                        // $base_stock=$poqty;
                    $con_stock = ($base_stock / $row1['product_qty']) * $row1['product_conv_qty'];
                 
                    $info2['product_id'] = $row['product_id'];
                    $info2['grn_trn_id'] = $grn_trn_id;
                    $info2['purchaseordertrn_id'] = $row['purchaseordertrn_id'];
                    $info2['returnable_trn_id'] = $row['returnable_trn_id'];

                    $info2['product_base_unit'] = $re['product_base_unit'];

                    $info2['product_conv_unit'] = $re['product_conv_unit'];
                    $info2['job_work_po_trn_id'] = $job_work_po_trn_id;
                    $info2['cdate'] = date("Y-m-d H:i:s");
                    $info2['user_id'] = $_SESSION['user_id'];
                    $info2['company_id'] = $_SESSION['company_id'];
                    $info2['branch_id'] = $row['branch_id'];

                     $que_1 = "select req.*,(select IFNULL(sum(product_qty),0) from tbl_grn_sub_trn  where status = 0 and rp_id = req.rp_id) as grn_qty from tbl_purchaseorder_req_trn as req where req.purchaseordertrn_req_status = 0 and req.purchaseordertrn_id=" . $row['purchaseordertrn_id'] . " order by rp_id";

                    $rs_di_1 = $dbcon->query($que_1);
                    if (brp_mysqli_num_rows($rs_di_1) > 0)
                    {
                        while ($re_1 = brp_mysqli_fetch_assoc($rs_di_1))
                        {

                            $used_conv_qty = 0;
                            $used_base_qty = 0;
                            if ($base_stock > 0)
                            {
                                if ($re_1['used_base_qty'] - $re_1['grn_qty'] > 0)
                                {
                                    // var_dump('4---> '.($re_1['used_qty'] - $re_1['grn_qty'])); 
                                    if ($base_stock >= ($re_1['used_base_qty'] - $re_1['grn_qty']))
                                    {
                                        $used_base_qty = $re_1['used_base_qty'] - $re_1['grn_qty'];
                                        $info2['rp_id'] = $re_1['rp_id'];
                                    }
                                    else
                                    {
                                        $used_base_qty = $base_stock;
                                        $info2['rp_id'] = 0;
                                    }
                                    $info2['rp_id'] = $re_1['rp_id'];

                                    $used_conv_qty = ($used_base_qty / $row1['product_qty']) * $row1['product_conv_qty'];

                                    $info2['product_qty'] = $used_base_qty;
                                    $info2['product_conv_qty'] = $used_conv_qty;
                                    $base_rate = 0;
                                    $conv_rate = 0;
                                    if ($row['rate_unit'] == $re['product_conv_unit'])
                                    {
                                        $conv_rate = $row['product_rate'];
                                        $base_rate = convert_rate($dbcon, $conv_rate, $row['product_id'], "base_unit");
                                    }
                                    else
                                    {
                                        $base_rate = $row['product_rate'];
                                        $conv_rate = convert_rate($dbcon, $base_rate, $row['product_id'], "conv_unit");
                                    }

                                    $info2['material_rate'] = $used_base_qty * $base_rate;
                                    $info2['process_pus_material_rate'] = $used_base_qty * $base_rate;

                                    $info2['material_conv_rate'] = $used_conv_qty * $conv_rate;
                                    $info2['process_pus_material_conv_rate'] = $used_conv_qty * $conv_rate;

                                    $con_stock = $con_stock - $used_conv_qty;
                                    $base_stock = $base_stock - $used_base_qty;
                                   /* var_dump('5---> '.$qty);
                                    var_dump('6---> '.$poqty);*/
                                    $qty = $qty - $used_base_qty;
                                    $poqty = $poqty - $used_base_qty;
                           
                                    $tbl_grn_trn_id = add_record('tbl_grn_sub_trn', $info2, $dbcon);

                                    if($tbl_grn_trn_id && $info2['rp_id'] > 0){

                                        $res_stock_added = check_reserve_stock_status($dbcon,$grn_trn_id,$info2['product_id'],$info2['rp_id']);
                                        if($res_stock_added == '0'){

                                            $st_qry = "SELECT stock_id FROM tbl_stock_trn WHERE stock_status != 2 AND stock_flage = 1 AND ref_name = 'tbl_grn_trn' and ref_id = " . $grn_trn_id . " and  product_id = " . $info2['product_id'];

                                            $st_row = brp_mysqli_fetch_assoc($dbcon->query($st_qry));
                                            grn_sub_trn_wise_reserv_stock_add($dbcon,$info2['product_qty'],$info2['product_base_unit'],date("Y-m-d"),"",$info2['rp_id'],$st_row['stock_id'],0);    
                                        }
                                        
                                    }

                                }
                            }
                        }
                    }
                    else
                    {
                        $info2['product_qty'] = $base_stock;
                        $info2['product_conv_qty'] = $con_stock;

                        if ($row['rate_unit'] == $re['product_conv_unit'])
                        {
                            $conv_rate = $row['product_rate'];
                            $base_rate = convert_rate($dbcon, $conv_rate, $row['product_id'], "base_unit");
                        }
                        else
                        {
                            $base_rate = $row['product_rate'];
                            $conv_rate = convert_rate($dbcon, $base_rate, $row['product_id'], "conv_unit");
                        }

                        $info2['material_rate'] = $base_stock * $base_rate;
                        $info2['process_pus_material_rate'] = $base_stock * $base_rate;

                        $info2['material_conv_rate'] = $con_stock * $conv_rate;
                        $info2['process_pus_material_conv_rate'] = $con_stock * $conv_rate;
                        
                        $tbl_grn_trn_id = add_record('tbl_grn_sub_trn', $info2, $dbcon);

                        $qty = $qty - $base_stock;
                        
                    }

            }
            else
            {
               
                $que = "select * from product_mst as ta where product_id=" . $row['product_id'];
                $rs_di = $dbcon->query($que);
                $re = brp_mysqli_fetch_assoc($rs_di);

                    $type = "conv_unit";
                    $base_stock = $qty;
                        // $con_stock=convert_stock_new($dbcon,$qty,$re['product_id'],$type);
                    $con_stock = ($base_stock / $row1['product_qty']) * $row1['product_conv_qty'];

                $info2['product_id'] = $row['product_id'];
                $info2['grn_trn_id'] = $grn_trn_id;
                $info2['purchaseordertrn_id'] = $row['purchaseordertrn_id'];

                $info2['product_base_unit'] = $re['product_base_unit'];

                $info2['product_conv_unit'] = $re['product_conv_unit'];
                $info2['cdate'] = date("Y-m-d H:i:s");
                $info2['user_id'] = $_SESSION['user_id'];
                $info2['company_id'] = $_SESSION['company_id'];
                $info2['branch_id'] = $row['branch_id'];

                $que_1 = "select * from tbl_purchaseorder_req_trn where purchaseordertrn_req_status =  0 and purchaseordertrn_id = " . $row['purchaseordertrn_id'];
             
                $rs_di_1 = $dbcon->query($que_1);
                if (brp_mysqli_num_rows($rs_di_1) > 0)
                {
                    while ($re_1 = brp_mysqli_fetch_assoc($rs_di_1))
                    {
                        $used_conv_qty = 0;
                        $used_base_qty = 0;
                        if ($base_stock > 0)
                        {
                            if ($base_stock >= $re_1['used_base_qty'])
                            {
                                $used_base_qty = $re_1['used_base_qty'];
                            }
                            else
                            {
                                $used_base_qty = $base_stock;
                                $info2['rp_id'] = 0;
                            }
                            $info2['rp_id'] = $re_1['rp_id'];

                            $used_conv_qty = ($used_base_qty / $row1['product_qty']) * $row1['product_conv_qty'];

                            $info2['product_qty'] = $used_base_qty;
                            $info2['product_conv_qty'] = $used_conv_qty;

                            $con_stock = $con_stock - $used_conv_qty;
                            $base_stock = $base_stock - $used_base_qty;

                            if ($row['rate_unit'] == $re['product_conv_unit'])
                            {
                                $conv_rate = $row['product_rate'];
                                $base_rate = convert_rate($dbcon, $conv_rate, $row['product_id'], "base_unit");
                            }
                            else
                            {
                                $base_rate = $row['product_rate'];
                                $conv_rate = convert_rate($dbcon, $base_rate, $row['product_id'], "conv_unit");
                            }

                            $info2['material_rate'] = $used_base_qty * $base_rate;
                            $info2['process_pus_material_rate'] = $used_base_qty * $base_rate;

                            $info2['material_conv_rate'] = $used_conv_qty * $conv_rate;
                            $info2['process_pus_material_conv_rate'] = $used_conv_qty * $conv_rate;

                            $tbl_grn_trn_id = add_record('tbl_grn_sub_trn', $info2, $dbcon);

                        }
                    }
                }
                else
                {
                    $info2['product_qty'] = $base_stock;
                    $info2['product_conv_qty'] = $con_stock;

                    if ($row['rate_unit'] == $re['product_conv_unit'])
                    {
                        $conv_rate = $row['product_rate'];
                        $base_rate = convert_rate($dbcon, $conv_rate, $row['product_id'], "base_unit");
                    }
                    else
                    {
                        $base_rate = $row['product_rate'];
                        $conv_rate = convert_rate($dbcon, $base_rate, $row['product_id'], "conv_unit");
                    }

                    $info2['material_rate'] = $base_stock * $base_rate;
                    $info2['process_pus_material_rate'] = $base_stock * $base_rate;

                    $info2['material_conv_rate'] = $con_stock * $conv_rate;
                    $info2['process_pus_material_conv_rate'] = $con_stock * $conv_rate;

                    $tbl_grn_trn_id = add_record('tbl_grn_sub_trn', $info2, $dbcon);

                }

                $qty = $qty - $qty;

                
            }
        }
    }

}

if ($qty > 0)
{
    $info2['product_id'] = $row1['product_id'];
    $info2['grn_trn_id'] = $grn_trn_id;
    $info2['purchaseordertrn_id'] = $purchaseordertrn_id;

    $rs_product = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($rs_product);

    $info2['product_base_unit'] = $row1['unit_id'];

    $info2['product_conv_unit'] = $row1['product_conv_unit'];
    $info2['job_work_po_trn_id'] = '';
    $info2['cdate'] = date("Y-m-d H:i:s");
    $info2['user_id'] = $_SESSION['user_id'];
    $info2['company_id'] = $_SESSION['company_id'];
    $info2['branch_id'] = $row1['branch_id'];
  

    $used_base_qty = $qty;
    $used_conv_qty = ($used_base_qty / $row1['product_qty']) * $row1['product_conv_qty'];

    $info2['product_qty'] = $used_base_qty;
    $info2['product_conv_qty'] = $used_conv_qty;
    $info2['rp_id'] = '';

    if ($row['rate_unit'] == $row1['product_conv_unit'])
    {
        $conv_rate = $row['product_rate'];
        $base_rate = convert_rate($dbcon, $conv_rate, $row['product_id'], "base_unit");
    }
    else
    {
        $base_rate = $row['product_rate'];
        $conv_rate = convert_rate($dbcon, $base_rate, $row['product_id'], "conv_unit");
    }

    $info2['material_rate'] = $used_base_qty * $base_rate;
    $info2['process_pus_material_rate'] = $used_base_qty * $base_rate;

    $info2['material_conv_rate'] = $used_conv_qty * $conv_rate;
    $info2['process_pus_material_conv_rate'] = $used_conv_qty * $conv_rate;

    $tbl_grn_trn_id = add_record('tbl_grn_sub_trn', $info2, $dbcon);

}
}

function check_reserve_stock_status($dbcon,$grn_trn_id,$product_id,$rp_id){

    $qry = "select count(stock_id) as status from tbl_reserve_stock where ref_name='grn' and stock_flage = 1 and stock_status != 2 and product_id = " . $product_id . " and ref_id = " . $grn_trn_id . " and request_id = " . $rp_id; 

    $rw = brp_mysqli_fetch_assoc($dbcon->query($qry));

    return $rw['status']; 
}



?>