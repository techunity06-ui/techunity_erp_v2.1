<?
session_start();
include('../include/urlfile.php');

$query = "select po.purchaseorder_id, po.prev_purchaseorder_id, po.vender_id, ptrn.purchaseordertrn_id from tbl_purchaseorder as po 
left join tbl_purchaseordertrn as ptrn on ptrn.purchaseorder_id = po.purchaseorder_id
where po.status=0 and ptrn.po_ref_id !=''";
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);

while($row = brp_mysqli_fetch_array($result)){
	$query_notdone = "select * from tbl_purchaseorder_req_trn where purchaseordertrn_id=".$row['purchaseordertrn_id'];
	$re_notdone = $dbcon->query($query_notdone);
	$cnt_notdone = brp_mysqli_num_rows($re_notdone);
    	
    	if($cnt_notdone ==0){
    		update_poreq_status_clone($dbcon, $row['purchaseorder_id'], $row['prev_purchaseorder_id'],$row['vender_id']);
		}			
}
//echo "Clone Run Successfully....!!";
function update_poreq_status_clone($dbcon, $po_id, $prev_purchaseorder_id,$vender_id)
{
    if(!empty($prev_purchaseorder_id)){
       $query_pre = "select * from tbl_purchaseordertrn where purchaseorder_id=".$prev_purchaseorder_id;
        $resi_pre = $dbcon->query($query_pre);
        while($pre_po = brp_mysqli_fetch_array($resi_pre)){
            $inf['purchaseordertrn_req_status'] = 2;
            update_record('tbl_purchaseorder_req_trn', $inf, "purchaseordertrn_id=" . $pre_po['purchaseordertrn_id'], $dbcon);
        } 
    }
    
    $que_po = "select * from tbl_purchaseordertrn where temptrn_ref_id!='' and purchaseorder_id=" . $po_id;
    $resi = $dbcon->query($que_po);
    while ($re_po = brp_mysqli_fetch_array($resi))
    {
    	
		$infos1['purchaseordertrn_req_status'] = 2;
        update_record('tbl_purchaseorder_req_trn', $infos1, "purchaseordertrn_id=" . $re_po['purchaseordertrn_id'], $dbcon);

        $infos2['po_trn_req_status'] = 0;
        update_record('tbl_purchasetrntemp', $infos2, "purchaseordertrn_id in (" . $re_po['temptrn_ref_id'] . ")", $dbcon);

        $used_qty = $re_po['product_conv_qty'];
        $used_base_qty = $re_po['product_qty'];

        $que_sub = "select * from tbl_purchasetrntemp where purchaseordertrn_id in (" . $re_po['temptrn_ref_id'] . ")";

        $resub = $dbcon->query($que_sub);
        while ($re_sub = brp_mysqli_fetch_array($resub))
        {
            $query_p = "select IFNULL(0,sum(used_qty)) as used_qty,IFNULL(0,sum(used_base_qty)) as used_base_qty from tbl_purchaseorder_req_trn where purchaseordertrn_req_status=0 and req_id=" . $re_sub['purchaseordertrn_id'];

            $rels = brp_mysqli_fetch_array($dbcon->query($query_p));
            
            $pending_qty = $re_sub['product_qty'] - $rels['used_qty'];
            $pending_base_qty = $re_sub['product_base_qty'] - $rels['used_base_qty'];
            
            if (round_up($used_qty, 5) > 0)
            {
                if (round_up($pending_qty, 5) > 0)
                {
                    if ((round_up($pending_qty, 5) <= round_up($used_qty, 5)) || (round_up($pending_base_qty, 5) <= round_up($used_base_qty, 5)))
                    {
                        $info1['req_id'] = $re_sub['purchaseordertrn_id'];
                        $info1['purchaseordertrn_id'] = $re_po['purchaseordertrn_id'];
                        $info1['rp_id'] = $re_sub['po_ref_id'];
                        $info1['vender_id'] = $vender_id;
                        $info1['used_qty'] = round_up($pending_qty, 5);
                        $info1['used_base_qty'] = round_up($pending_base_qty, 5);
                        $info1['base_unit'] = $re_sub['base_unit_id'];
                        $info1['conv_unit'] = $re_sub['unit_id'];
                        $info1['user_id'] = $_SESSION['user_id'];
                        $info1['company_id'] = $_SESSION['company_id'];

                        $ins_id = add_record('tbl_purchaseorder_req_trn', $info1, $dbcon, $re_sub['branch_id']);
                        $used_qty = $used_qty - $pending_qty;
                        $used_base_qty = $used_base_qty - $pending_base_qty;

                        $infos['po_trn_req_status'] = 1;
                        update_record('tbl_purchasetrntemp', $infos, "purchaseordertrn_id=" . $re_sub['purchaseordertrn_id'], $dbcon);
                    }
                    else
                    {
                        $info1['req_id'] = $re_sub['purchaseordertrn_id'];
                        $info1['purchaseordertrn_id'] = $re_po['purchaseordertrn_id'];
                        $info1['rp_id'] = $re_sub['po_ref_id'];
                        $info1['vender_id'] = $vender_id;
                        $info1['used_qty'] = round_up($used_qty, 5);
                        $info1['used_base_qty'] = round_up($pending_base_qty, 5);
                        $info1['base_unit'] = $re_sub['base_unit_id'];
                        $info1['conv_unit'] = $re_sub['unit_id'];
                        $info1['user_id'] = $_SESSION['user_id'];
                        $info1['company_id'] = $_SESSION['company_id'];

                        $ins_id = add_record('tbl_purchaseorder_req_trn', $info1, $dbcon, $re_sub['branch_id']);
                        $used_qty = $used_qty - $pending_qty;
                        $used_base_qty = $used_base_qty - $pending_base_qty;
                    }
                }
            }
        }
    	
    }
}



function grn_po_sub_trn_clone($dbcon, $grn_trn_id, $purchaseordertrn_id, $job_work_po_trn_id = 0)
{
    /* Code By Umair: 30/10/2020
    Comment: I have commented the below query and change the query to mange the tollerance.
    */
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

        // $grn_po_updt['used_qty'] = $row['used_qty'] + $row1['product_qty'];
        $grn_po_updt['used_grn_qty'] = $row['used_grn_qty'] + $row1['product_qty'];
        $grn_po_updt['used_grn_conv_qty'] = $row['used_grn_conv_qty'] + $row1['product_conv_qty'];
        if ($grn_po_updt['used_grn_qty'] > $row['product_qty'] && $grn_po_updt['used_grn_conv_qty'] > $row['product_conv_qty'])
        {
            $grn_po_updt['used_grn_qty'] = $row['product_qty'];
            $grn_po_updt['used_grn_conv_qty'] = $row['product_conv_qty'];
        }

        $update_id = update_record('tbl_purchaseordertrn', $grn_po_updt, "purchaseordertrn_id=" . $purchaseordertrn_id, $dbcon);

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

        // var_dump('1---> '.$qty);

        if ($qty != 0)
        {
            if ($qty != "")
            {

                if ($qty >= $poqty || $usedQty >= $minium_tollerance_qty)
                {

                     $que = "select * from product_mst as ta where product_id=" . $row['product_id'];
                    $rs_di = $dbcon->query($que);
                    $re = brp_mysqli_fetch_assoc($rs_di);

                   /* if ($re['product_conv_unit'] == $row['unit_id'])
                    {
                        $type = "base_unit";
                        if ($qty >= $poqty)
                        {
                            $con_stock = $poqty;
                        }
                        else
                        {
                            $con_stock = $qty;
                        }
                        // $con_stock=$poqty;
                        $base_stock = ($con_stock / $row1['product_conv_qty']) * $row1['product_qty'];
                        // $base_stock=convert_stock_new($dbcon,$poqty,$re['product_id'],$type);
                        var_dump('2===>'.$qty);
                    }
                    else
                    {*/
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
                        // $con_stock=convert_stock_new($dbcon,$poqty,$re['product_id'],$type);
                       
                    // }

                       /* var_dump('2---> '.$base_stock);
                        var_dump('3---> '.$con_stock);*/

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

                    /*$que_1="select * from tbl_purchaseorder_req_trn where purchaseordertrn_req_status =  0 and purchaseordertrn_id=".$row['purchaseordertrn_id'];*/

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

                                    //var_dump($base_stock);
                                    $mm = purchaseorder_delivery_datewise_used_qty_update($dbcon, $row['purchaseordertrn_id'], $used_base_qty, $re['product_base_unit'], $used_conv_qty, $re['product_conv_unit']);
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
                        
                        //var_dump($base_stock);
                        $mm = purchaseorder_delivery_datewise_used_qty_update($dbcon, $row['purchaseordertrn_id'], $base_stock, $re['product_base_unit'], $con_stock, $re['product_conv_unit']);
                    }

                    // if ($qty > 0) {
                    // $used_conv_qty = $qty;
                    // $used_base_qty = ($used_conv_qty / $row1['product_conv_qty']) * $row1['product_qty'];
                    // $info2['product_qty'] = $used_base_qty;
                    // $info2['product_conv_qty'] = $used_conv_qty;
                    // $info2['rp_id'] = '';
                    // $tbl_grn_trn_id = add_record('tbl_grn_sub_trn', $info2, $dbcon);
                    // //var_dump($base_stock);
                    // $mm = purchaseorder_delivery_datewise_used_qty_update($dbcon, $row['purchaseordertrn_id'], $used_base_qty, $re['product_base_unit']);
                    // }
                    //return $mm;
                    /* $info['used_status'] = 1;
                    $updateid=update_record('tbl_purchaseordertrn', $info,"purchaseordertrn_id=".$row['purchaseordertrn_id'] , $dbcon);
                    
                    $query3="select count(purchaseordertrn_id) as cou from tbl_purchaseordertrn as po where status=0 and used_status=0 and purchaseorder_id =".$row['purchaseorder_id'];
                    $rs_product3=$dbcon->query($query3);
                    $row3=brp_mysqli_fetch_array($rs_product3);
                    if($row3['cou']<=0){
                    $info4['used_status'] = 1;
                    $updateid=update_record('tbl_purchaseorder', $info4,"purchaseorder_id=".$row['purchaseorder_id'], $dbcon);
                } */
                    // $qty = $qty - $poqty;

            }
            else
            {
               
                $que = "select * from product_mst as ta where product_id=" . $row['product_id'];
                $rs_di = $dbcon->query($que);
                $re = brp_mysqli_fetch_assoc($rs_di);

                /*if ($re['product_conv_unit'] == $row['unit_id'])
                {
                    $type = "base_unit";
                    $con_stock = $qty;
                        // $base_stock=convert_stock_new($dbcon,$qty,$re['product_id'],$type);
                    $base_stock = ($con_stock / $row1['product_conv_qty']) * $row1['product_qty'];

                    var_dump('7===>'.$qty);
                }
                else
                {*/
                    $type = "conv_unit";
                    $base_stock = $qty;
                        // $con_stock=convert_stock_new($dbcon,$qty,$re['product_id'],$type);
                    $con_stock = ($base_stock / $row1['product_qty']) * $row1['product_conv_qty'];

                    
                // }

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

                            $mm = purchaseorder_delivery_datewise_used_qty_update($dbcon, $row['purchaseordertrn_id'], $base_stock, $re['product_base_unit'], $con_stock, $re['product_conv_unit']);
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

                    $mm = purchaseorder_delivery_datewise_used_qty_update($dbcon, $row['purchaseordertrn_id'], $base_stock, $re['product_base_unit'], $con_stock, $re['product_conv_unit']);
                }

                $qty = $qty - $qty;

                
            }
        }
    }
    purchase_order_grn_used_status_update($dbcon, $row['purchaseordertrn_id']);
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
    /*$used_conv_qty = $qty;
    $used_base_qty = ($used_conv_qty / $row1['product_conv_qty']) * $row1['product_qty'];
*/

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
        // $qty = $qty - $qty;
        // var_dump($info2);

    $mm = purchaseorder_delivery_datewise_used_qty_update($dbcon, $purchaseordertrn_id, $used_base_qty, $row1['unit_id'], $used_conv_qty, $row1['product_conv_unit']);
    purchase_order_grn_used_status_update($dbcon, $row1['purchaseordertrn_id']);
}

    //return $mm;
    //return $query1;

}

?>