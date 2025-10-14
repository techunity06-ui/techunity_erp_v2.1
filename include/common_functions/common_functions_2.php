<?php
function get_tree_request_jobwork($dbcon, $product_id, $parent_id, $level, $cnt, $bom, $number, $qty, $bom_trn_id, $ptype)
{
    global $counter_tree;

    if ($level == 0)
    {
        $pr_value = get_pro_field($dbcon, $product_id, 'product_name');
        echo '
        <td class="td1">' . $number . '</td>
        <td class="td2">' . $pr_value . '</td>
        <td class="td4">' . get_pro_field($dbcon, $product_id, 'product_min_stock') . '</td>
        <td class="td5">' . get_product_stock($dbcon, $product_id) . '</td>
        <td class="td5">
        20
        </td>
        <td class="td5">
        20
        </td>

        <td class="td5"><a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request(' . $counter_tree . ')"><i class="fa fa-paper-plane"></i> Allocate</a></td>
        ';
    }

    $getChildNodes = "select * from tbl_bomtrn where parent_id = '" . $bom_trn_id . "' and bom_id='$bom'";
    $resChildNodes = $dbcon->query($getChildNodes);
    if (brp_mysqli_num_rows($resChildNodes) > 0)
    {

        echo '<tr>';

        $cntt = 1;
        while ($childNode = brp_mysqli_fetch_assoc($resChildNodes))
        {
            $pro_name = get_pro_field($dbcon, $childNode['product_id'], 'product_name');

            $getChildNodes1 = "select * from tbl_bomtrn where parent_id = '" . $childNode['bom_trn_id'] . "' and bom_id='$bom'";
            $resChildNodes1 = $dbcon->query($getChildNodes1);
            if (brp_mysqli_num_rows($resChildNodes1) > 0)
            {
                $new_number = $number . '.' . $cntt;
                $counter_tree++;
                echo '<tr>
                <td  class="td1">' . $new_number . '</td>
                <td class="td2">' . $pro_name . '</td>
                <td class="td4">' . get_pro_field($dbcon, $childNode['product_id'], 'product_min_stock') . '</td>
                <td class="td5">' . get_product_stock($dbcon, $childNode['product_id']) . '</td>
                <td class="td5">
                20
                </td>
                <td class="td5">
                20
                </td>

                <td class="td5"><a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request(' . $counter_tree . ')"><i class="fa fa-paper-plane"></i> Allocate</a></td>
                </tr>';

                $level++;
                $cnt++;
                $cntt++;

                get_tree_request_jobwork($dbcon, $childNode['product_id'], $parent_id, $level, $cnt, $bom, $new_number, $childNode['product_qty'], $childNode['bom_trn_id'], $childNode['product_type']);
            }
            else
            {
                $new_number = $number . '.' . $cntt;
                $counter_tree++;
                echo '<tr>
                <td  class="td1">' . $new_number . '</td>
                <td   class="td2">' . $pro_name . '</td>
                <td class="td4">' . get_pro_field($dbcon, $childNode['product_id'], 'product_min_stock') . '</td>
                <td class="td5">' . get_product_stock($dbcon, $childNode['product_id']) . '</td>
                <td class="td5">
                20
                </td>
                <td class="td5">
                20
                </td>

                <td class="td5"><a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request(' . $counter_tree . ')"><i class="fa fa-paper-plane"></i> Allocate</a></td>
                </tr>';

                $level++;
                $cnt++;
                $cntt++;
                //get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);
                
            }

            //$cntt++;
            
        }
    }

    return $counter_tree;
}

function get_product_stock($dbcon, $productid)
{
    $query = 'SELECT pro.product_id as pid,"product" as type,product_name as pr_name,product_min_stock as min_stock,product_opening as op_stock  FROM `product_mst` as pro 

    where pro.product_id=' . $productid;

    $rows = mysqli_fetch_assoc($dbcon->query($query));

    $stock = $rows['op_stock'];

    return $stock;
}

function get_bom_resrev_stock($dbcon, $productid, $bom_id)
{
    $query = 'select sum(bomtrn.product_qty) as bomqty,bomtrn.product_id from tbl_pln_bomtrn as bomtrn 
    inner join tbl_planning_ordertrn as bom on bom.sales_ordertrn_id=bomtrn.sales_order_trn_id 
    where bomtrn.so_bom_trn_status=0 and bomtrn.product_id=' . $productid . ' group by bomtrn.product_id';
    $rows = brp_mysqli_fetch_assoc($dbcon->query($query));

    return floatval($rows['bomqty']);
}

function get_check_request($dbcon, $bom_trn_id, $bom, $planning_id)
{
    $getChildNodes = "select purchaseordertrn_id from tbl_purchaseordertrn where po_bom_trn_id ='$bom_trn_id'  and po_bom_id='$bom' and po_ref_id='$planning_id' and po_ref_type='planning'";

    $resChildNodes = $dbcon->query($getChildNodes);
    $count = brp_mysqli_num_rows($resChildNodes);
    //$childNode = brp_mysqli_fetch_assoc($resChildNodes);
    return $count;
}

function get_tree_bom_po($dbcon, $product_id, $parent_id, $level, $cnt, $bom, $number, $qty, $bom_trn_id, $planning_id)
{
    $current_stock = 0;
    $resev_stock = 0;
    $out_of_stock = 0;
    $check_r = get_check_request($dbcon, $bom_trn_id, $bom, $planning_id);
    if ($check_r > 0)
    {
        $dyn_text = 'Requested';
    }
    else
    {
        $dyn_text = '<input type="checkbox" name="bom_trn_id[]" class="chk_box" id="bom_trn_id' . $i . '" value="' . $bom_trn_id . '" style="width: 23px;height: 23px;margin-top: 0px;">';
    }
    if ($level == 0)
    {
        $pr_value = get_pro_field($dbcon, $product_id, 'product_name');

        $current_stock = get_product_stock($dbcon, $product_id);
        $resev_stock = get_bom_resrev_stock($dbcon, $product_id,"");
        $resev_stock = ($resev_stock) - floatval($qty);
        $out_of_stock = $current_stock - $resev_stock - $qty;
        if ($out_of_stock > 0)
        {
            $out_of_stock = 0;
        }
        else
        {
            $out_of_stock = abs($out_of_stock);
        }
        //$upd_out_of_stock = upd_out_of_stock_qty($out_of_stock, $bom_trn_id,$dbcon);
        

        echo '
        <td class="td1">' . $number . '</td>
        <td class="td2">' . $pr_value . '</td>
        <td class="td3">' . $current_stock . '</td>
        <td class="td3">' . $resev_stock . '</td>
        <td class="td3">' . $qty . '</td>
        <td class="td3">' . $out_of_stock . '</td>
        <td class="td4">' . $dyn_text . '</td>
        ';
    }

    $getChildNodes = "select * from tbl_bomtrn where parent_id = '" . $bom_trn_id . "' and bom_id='$bom'";
    $resChildNodes = $dbcon->query($getChildNodes);
    if (brp_mysqli_num_rows($resChildNodes) > 0)
    {

        echo '<tr>';

        $cntt = 1;
        $current_stock1 = 0;
        $resev_stock1 = 0;
        $out_of_stock1 = 0;
        while ($childNode = brp_mysqli_fetch_assoc($resChildNodes))
        {
            $pro_name = get_pro_field($dbcon, $childNode['product_id'], 'product_name');
            $current_stock1 = get_product_stock($dbcon, $childNode['product_id']);
            $resev_stock1 = get_bom_resrev_stock($dbcon, $childNode['product_id'],"");
            $resev_stock1 = ($resev_stock1) - floatval($childNode['product_qty']);
            $out_of_stock1 = $current_stock1 - $resev_stock1 - $childNode['product_qty'];
            if ($out_of_stock1 > 0)
            {
                $out_of_stock1 = 0;
            }
            else
            {
                $out_of_stock1 = abs($out_of_stock1);
            }

            $pro_name = get_pro_field($dbcon, $childNode['product_id'], 'product_name');

            $getChildNodes1 = "select * from tbl_bomtrn where parent_id = '" . $childNode['bom_trn_id'] . "' and bom_id='$bom'";
            $resChildNodes1 = $dbcon->query($getChildNodes1);
            if (brp_mysqli_num_rows($resChildNodes1) > 0)
            {
                $new_number = $number . '.' . $cntt;

                $check_r1 = get_check_request($dbcon, $childNode['bom_trn_id'], $bom, $planning_id);
                if ($check_r1 > 0)
                {
                    $dyn_text1 = 'Requested';
                }
                else
                {
                    $dyn_text1 = '<input type="checkbox" name="bom_trn_id[]" class="chk_box" id="bom_trn_id' . $i . '" value="' . $childNode['bom_trn_id'] . '" style="width: 23px;height: 23px;margin-top: 0px;">';
                }

                echo '<tr>
                <td  class="td1">' . $new_number . '</td>
                <td class="td2">' . $pro_name . '</td>
                <td class="td3">' . $current_stock1 . '</td>
                <td class="td3">' . $resev_stock1 . '</td>
                <td class="td3">' . $childNode['product_qty'] . '</td>
                <td class="td3">' . $out_of_stock1 . '</td>
                <td class="td4">' . $dyn_text1 . '</td>
                </tr>';

                $level++;
                $cnt++;
                $cntt++;

                get_tree_bom_po($dbcon, $childNode['product_id'], $parent_id, $level, $cnt, $bom, $new_number, $childNode['product_qty'], $childNode['bom_trn_id'], $planning_id);
            }
            else
            {
                $new_number = $number . '.' . $cntt;

                $check_r2 = get_check_request($dbcon, $childNode['bom_trn_id'], $bom, $planning_id);
                if ($check_r2 > 0)
                {
                    $dyn_text2 = 'Requested';
                }
                else
                {
                    $dyn_text2 = '<input type="checkbox" name="bom_trn_id[]" class="chk_box" id="bom_trn_id' . $i . '" value="' . $childNode['bom_trn_id'] . '" style="width: 23px;height: 23px;margin-top: 0px;">';
                }

                echo '<tr>
                <td  class="td1">' . $new_number . '</td>
                <td   class="td2">' . $pro_name . '</td>
                <td class="td3">' . $current_stock1 . '</td>
                <td class="td3">' . $resev_stock1 . '</td>
                <td class="td3">' . $childNode['product_qty'] . '</td>
                <td class="td3">' . $out_of_stock1 . '</td>
                <td class="td4">' . $dyn_text2 . '</td>
                </tr>';

                $level++;
                $cnt++;
                $cntt++;
                //get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);
                
            }

            //$cntt++;
            
        }
    }
}

function get_service_charge($dbcon, $id)
{
    $q = $dbcon->query("select sum(comp_amount) as amount from tbl_complaint_trn where complaint_id='$id'");
    $row = brp_mysqli_fetch_array($q);
    return $row['amount'];
}

function get_spare_part_rate($dbcon, $id)
{
    $q = $dbcon->query("select sum(s_amount) as amount from tbl_complain_spare_part where s_comp_id='$id' and s_paid_status='paid'");
    $row = brp_mysqli_fetch_array($q);
    return $row['amount'];
}


function get_tree_complain($dbcon, $product_id, $parent_id, $level, $cnt, $bom, $number, $eid, $bom_trn_id)
{

    // var_dump(123);
    //STATIC $counter_tree = 0;
    global $counter_tree;

    //$counter_tree++;
    if ($level == 0)
    {

        if ($eid != '')
        {
            $qe = $dbcon->query("select * from tbl_complain_spare_part where s_comp_id='$eid' and s_product='$product_id'");

            if (brp_mysqli_num_rows($qe) > 0)
            {
                $re = brp_mysqli_fetch_array($qe);

                $checked = " checked";
                $epstatus = $re['s_paid_status'];
                $eqty = $re['s_qty'];
                $erate = $re['s_rate'];
                $eamount = $re['s_amount'];
                $ecanme = $re['s_courier_name'];
                $ecno = $re['s_courier_no'];
                $ecdel = date("d/m/Y", strtotime($re['s_courier_del_date']));
                $esent = $re['sp_sent_status'];
                $readonly = "";
            }
            else
            {
                $checked = " ";
                $epstatus = "";
                $eqty = "";
                $erate = "";
                $eamount = "";
                $ecanme = "";
                $ecno = "";
                $ecdel = "";
                $esent = "";
                $readonly = "readonly";
            }
        }
        else
        {
            $checked = " ";
            $epstatus = "";
            $eqty = "";
            $erate = "";
            $eamount = "";
            $ecname = "";
            $ecno = "";
            $ecdel = "";
            $esent = "";
            $readonly = "readonly";
        }

        $pr_value = get_pro_field($dbcon, $product_id, 'product_name');
        echo '
        <td class="td1">' . $number . '<input type="hidden" name="sp_no[]" value="' . $number . '" /></td>
        <td class="td2">' . $pr_value . '<input type="hidden" name="sp_pid[]" value="' . $product_id . '" /></td>
        <td class="td3"><input type="checkbox" name="sp_part[]" id="chk' . $counter_tree . '" value="' . $counter_tree . '" onchange="enable_text(this.value)" ' . $checked . '  /></td>
        <td class="td3">
        <select class="form-control" name="sp_free[]" id="sp_free' . $counter_tree . '" >
        <option value="">--select value--</option>
        <option value="free" ' . ($epstatus == 'free' ? 'selected="selected"' : '') . '>Free</option>
        <option value="paid" ' . ($epstatus == 'paid' ? 'selected="selected"' : '') . '>Paid</option>
        </select>
        </td>
        <td class="td3"><input type="text" class="form-control" name="sp_qty[]" id="qty' . $counter_tree . '" placeholder="quantity" ' . $readonly . ' onkeyup="get_amount_spare(' . $counter_tree . ')" value="' . $eqty . '" onkeypress="return isNumberKey(event)" /></td>
        <td class="td3"><input type="text" class="form-control" name="sp_rate[]" id="rate' . $counter_tree . '" placeholder="rate" ' . $readonly . '  onkeyup="get_amount_spare(' . $counter_tree . ')" value="' . $erate . '" onkeypress="return isNumberKey(event)" /></td>
        <td class="td3"><input type="text" class="form-control" name="sp_amount[]"  id="amt' . $counter_tree . '" placeholder="amount" ' . $readonly . ' value="' . $eamount . '"  /></td>
        <td class="td3"><input type="text" class="form-control" name="sp_courier_name[]" id="cname' . $counter_tree . '" placeholder="courier Name" ' . $readonly . ' value="' . $ecname . '" /></td>
        <td class="td3"><input type="text" class="form-control" name="sp_courier_no[]" id="cno' . $counter_tree . '" placeholder="courier No" ' . $readonly . ' value="' . $ecno . '" /></td>
        <td class="td3"><input type="text" class="form-control default-date-picker" id="cdate' . $counter_tree . '" name="sp_courier_date[]" id="" placeholder="courier Date" ' . $readonly . ' value="' . $ecdel . '" onkeypress="return isNumberKey(event)" /></td>
        <td class="td3">
        <select class="form-control" name="sp_sent[]" id="sp_sent' . $counter_tree . '" >
        <option value="">--select Staus--</option>
        <option value="yes" ' . ($esent == 'yes' ? 'selected="selected"' : '') . '>YES</option>
        <option value="no" ' . ($esent == 'no' ? 'selected="selected"' : '') . '>NO</option>
        </select>
        </td>
        <td class="td3">
        <!-- // Amish Soni 22-09-2020 -->
        <select class="form-control" name="old_sp_sent[]" id="old_sp_sent' . $counter_tree . '" >
        <option value="">--select Staus--</option>
        <option value="yes" ' . ($esent == 'yes' || $esent == '' ? 'selected="selected"' : '') . '>YES</option>
        <option value="no" ' . ($esent == 'no' ? 'selected="selected"' : '') . '>NO</option>
        </select>
        </td>
        ';
    }

    $getChildNodes = "select * from tbl_bomtrn where bom_id = '" . $parent_id . "' and po_visible_status=0 and bom_trn_status='0'";
    $resChildNodes = $dbcon->query($getChildNodes);
    if (brp_mysqli_num_rows($resChildNodes) > 0)
    {
        //echo '<ul class="jtree_parent_node">';
        $cntt = 1;
        while ($childNode = brp_mysqli_fetch_assoc($resChildNodes))
        {
            if ($eid != '')
            {
                $qe = $dbcon->query("select * from tbl_complain_spare_part where s_comp_id='$eid' and s_product='$childNode[product_id]'");

                if (brp_mysqli_num_rows($qe) > 0)
                {
                    $re = brp_mysqli_fetch_array($qe);

                    $checked = " checked";
                    $epstatus = $re['s_paid_status'];
                    $eqty = $re['s_qty'];
                    $erate = $re['s_rate'];
                    $eamount = $re['s_amount'];
                    $ecanme = $re['s_courier_name'];
                    $ecno = $re['s_courier_no'];
                    $esent = $re['sp_sent_status'];
                    $ecdel = date("d/m/Y", strtotime($re['s_courier_del_date']));
                    $readonly = "";
                }
                else
                {
                    $checked = " ";
                    $epstatus = "";
                    $eqty = "";
                    $erate = "";
                    $eamount = "";
                    $ecanme = "";
                    $ecno = "";
                    $ecdel = "";
                    $esent = "";
                    $readonly = "readonly";
                }
            }
            else
            {
                $checked = " ";
                $epstatus = "";
                $eqty = "";
                $erate = "";
                $eamount = "";
                $ecanme = "";
                $ecno = "";
                $ecdel = "";
                $esent = "";
                $readonly = "readonly";
            }

            $pro_name = get_pro_field($dbcon, $childNode['product_id'], 'product_name');

            $getChildNodes1 = "select * from tbl_bomtrn where parent_id = '" . $childNode['bom_trn_id'] . "' and po_visible_status=0 and bom_id='$bom'";
            $resChildNodes1 = $dbcon->query($getChildNodes1);
            if (brp_mysqli_num_rows($resChildNodes1) > 0)
            {
                $new_number = $number . '.' . $cntt;
                $counter_tree++;
                echo '<tr>
                <td class="td1">' . $new_number . '<input type="hidden" name="sp_no[]" value="' . $new_number . '" /></td>
                <td class="td2">' . $pro_name . '<input type="hidden" name="sp_pid[]" value="' . $childNode['product_id'] . '" /></td>
                <td class="td3"><input type="checkbox" name="sp_part[]" id="chk' . $counter_tree . '" value="' . $counter_tree . '"  onchange="enable_text(this.value)" ' . $checked . '  /></td>
                <td class="td3">
                <select class="form-control" name="sp_free[]" id="sp_free' . $counter_tree . '">
                <option value="">--select value--</option>
                <option value="free" ' . ($epstatus == 'free' ? 'selected="selected"' : '') . '>Free</option>
                <option value="paid" ' . ($epstatus == 'paid' ? 'selected="selected"' : '') . '>Paid</option>
                </select>
                </td>
                <td class="td3"><input type="text" class="form-control" name="sp_qty[]" id="qty' . $counter_tree . '" placeholder="quantity" ' . $readonly . '  onkeyup="get_amount_spare(' . $counter_tree . ')" value="' . $eqty . '" onkeypress="return isNumberKey(event)" /></td>
                <td class="td3"><input type="text" class="form-control" name="sp_rate[]" id="rate' . $counter_tree . '" placeholder="rate" ' . $readonly . '  onkeyup="get_amount_spare(' . $counter_tree . ')" value="' . $erate . '" onkeypress="return isNumberKey(event)" /></td>
                <td class="td3"><input type="text" class="form-control" name="sp_amount[]" id="amt' . $counter_tree . '"  placeholder="amount" ' . $readonly . ' value="' . $eamount . '" onkeypress="return isNumberKey(event)" /></td>
                <td class="td3"><input type="text" class="form-control" name="sp_courier_name[]" id="cname' . $counter_tree . '" placeholder="courier Name" ' . $readonly . ' value="' . $ecanme . '" /></td>
                <td class="td3"><input type="text" class="form-control" name="sp_courier_no[]" id="cno' . $counter_tree . '" placeholder="courier No" ' . $readonly . ' value="' . $ecno . '" /></td>
                <td class="td3"><input type="text" class="form-control  default-date-picker valid" id="cdate' . $counter_tree . '" name="sp_courier_date[]" id="" placeholder="courier Date" ' . $readonly . ' value="' . $ecdel . '" onkeypress="return isNumberKey(event)" /></td>
                <td class="td3">
                <select class="form-control" name="sp_sent[]" id="sp_sent' . $counter_tree . '" >
                <option value="">--select Staus--</option>
                <option value="yes" ' . ($esent == 'yes' ? 'selected="selected"' : '') . '>YES</option>
                <option value="no" ' . ($esent == 'no' ? 'selected="selected"' : '') . '>NO</option>
                </select>
                </td>
                <td class="td3">
                <!-- // Amish Soni 22-09-2020 -->
                <select class="form-control" name="old_sp_sent[]" id="old_sp_sent' . $counter_tree . '" >
                <option value="">--select Staus--</option>
                <option value="yes" ' . ($esent == 'yes' || $esent == '' ? 'selected="selected"' : '') . '>YES</option>
                <option value="no" ' . ($esent == 'no' ? 'selected="selected"' : '') . '>NO</option>
                </select>
                </td>
                </tr>';

                $level++;
                $cnt++;
                $cntt++;

                get_tree_complain($dbcon, $childNode['product_id'], $parent_id, $level, $cnt, $bom, $new_number, $eid, $childNode['bom_trn_id']);
            }
            else
            {
                $new_number = $number . '.' . $cntt;
                $counter_tree++;
                echo '<tr data-node-id="' . $new_number . '" data-node-pid="' . $number . '">
                <td class="td1">' . $new_number . '<input type="hidden" name="sp_no[]" value="' . $new_number . '" /></td>
                <td class="td2">' . $pro_name . '<input type="hidden" name="sp_pid[]" value="' . $childNode['product_id'] . '" /></td>
                <td class="td3"><input type="checkbox" name="sp_part[]" id="chk' . $counter_tree . '" value="' . $counter_tree . '" onchange="enable_text(this.value)" ' . $checked . '  /></td>
                <td class="td3">
                <select class="form-control" name="sp_free[]" id="sp_free' . $counter_tree . '">
                <option value="">--select value--</option>
                <option value="free" ' . ($epstatus == 'free' ? 'selected="selected"' : '') . '>Free</option>
                <option value="paid" ' . ($epstatus == 'paid' ? 'selected="selected"' : '') . '>Paid</option>
                </select>
                </td>
                <td class="td3"><input type="text" class="form-control" name="sp_qty[]" id="qty' . $counter_tree . '" placeholder="quantity" ' . $readonly . '  onkeyup="get_amount_spare(' . $counter_tree . ')" value="' . $eqty . '" onkeypress="return isNumberKey(event)" /></td>
                <td class="td3"><input type="text" class="form-control" name="sp_rate[]" id="rate' . $counter_tree . '" placeholder="rate" ' . $readonly . '  onkeyup="get_amount_spare(' . $counter_tree . ')" value="' . $erate . '" onkeypress="return isNumberKey(event)" /></td>
                <td class="td3"><input type="text" class="form-control" name="sp_amount[]" id="amt' . $counter_tree . '"  placeholder="amount" ' . $readonly . ' value="' . $eamount . '" onkeypress="return isNumberKey(event)" /></td>
                <td class="td3"><input type="text" class="form-control" name="sp_courier_name[]" id="cname' . $counter_tree . '" placeholder="courier Name" ' . $readonly . ' value="' . $ecname . '" /></td>
                <td class="td3"><input type="text" class="form-control" name="sp_courier_no[]" id="cno' . $counter_tree . '" placeholder="courier No" ' . $readonly . ' value="' . $ecno . '" /></td>
                <td class="td3"><input type="text" class="form-control  default-date-picker valid" id="cdate' . $counter_tree . '" name="sp_courier_date[]" id="" placeholder="courier Date" ' . $readonly . ' value="' . $ecdel . '" onkeypress="return isNumberKey(event)" /></td>
                <td class="td3">
                <select class="form-control" name="sp_sent[]" id="sp_sent' . $counter_tree . '" >
                <option value="">--select Staus--</option>
                <option value="yes" ' . ($esent == 'yes' ? 'selected="selected"' : '') . '>YES</option>
                <option value="no" ' . ($esent == 'no' ? 'selected="selected"' : '') . '>NO</option>
                </select>
                </td>
                <td class="td3">
                <!-- // Amish Soni 22-09-2020 -->
                <select class="form-control" name="old_sp_sent[]" id="old_sp_sent' . $counter_tree . '" >
                <option value="">--select Staus--</option>
                <option value="yes" ' . ($esent == 'yes' || $esent == '' ? 'selected="selected"' : '') . '>YES</option>
                <option value="no" ' . ($esent == 'no' ? 'selected="selected"' : '') . '>NO</option>
                </select>
                </td>
                </tr>';

                $level++;
                $cnt++;
                $cntt++;
                // get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);
                
            }

            //$cntt++;
            
        }
    }

    return $counter_tree;
}

function get_pro_field($dbcon, $product_id, $field_name)
{
    $get_pro_qry = "select $field_name from product_mst where product_id=" . $product_id;
    $get_qry_rrl = brp_mysqli_fetch_assoc($dbcon->query($get_pro_qry));
    return $get_qry_rrl[$field_name];
    //return $get_pro_qry;
    
}

function get_tax_field_tax_id($dbcon, $tid, $field_name)
{
    $q = $dbcon->query("select $field_name from tbl_tax where tax_id='$tid'");
    $row = brp_mysqli_fetch_array($q);
    return $row[$field_name];
}

function get_total_tax($dbcon, $pamount, $formula)
{
    $tax_total = 0;
    foreach ($formula as $f)
    {
        $tax_value = get_tax_field_tax_id($dbcon, $f, 'tax_value');
        $tax = ($tax_value * $pamount) / 100;
        $tax_total += $tax;
    }

    return $tax_total;
}

function getbank($dbcon, $bankid, $con='')
{
    $bank = '';
    $qry = "select * from bank_mst where bank_status=0" . $con;
    $rs_type = $dbcon->query($qry);
    $bank .= '<option value="" selected="selected">Choose Bank</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type))
    {
        $sel = '';
        if ($row['bankid'] == $bankid)
        {
            $sel = 'selected="selected"';
        }
        $bank .= '<option ' . $sel . ' value="' . $row['bankid'] . '">' . $row['bank_name'] . '</option>';
    }
    return $bank;
}

function get_sitemap_pro($dbcon, $i, $p_name)
{
    $qry = "select * from tbl_bomtrn where bom_level='$i' and bom_trn_id='$p_name'";
    $rs_type = $dbcon->query($qry);
    $row = brp_mysqli_fetch_assoc($rs_type);
    return get_pro_field($dbcon, $row['product_id'], 'product_name');
}

function generate_sitemap($dbcon, $id)
{

    $query_bom = "SELECT * FROM tbl_bomtrn WHERE bom_trn_id = '" . $id . "'";
    $rsCategoryId = $dbcon->query($query_bom);
    $row_rsCategoryId = mysqli_fetch_assoc($rsCategoryId);
    $parent = $row_rsCategoryId['parent_id'];

    if ($parent != 0)
    {
        generate_sitemap($dbcon, $parent);
        echo '<li>';
    }
    else
    {
        echo '<li>';
    }

    echo '<a href="' . ROOT . "bom_allocate/" . $id . '">
    ' . get_pro_field($dbcon, $row_rsCategoryId['product_id'], 'product_name') . ' </a>
    </li>';
}

function get_product_by_planning($dbcon, $id)
{
    $str = '';
    $query = "Select pl.product_id,pl.pl_order_id,p.product_name from tbl_planning_ordertrn as pl  inner join product_mst as p on p.product_id=pl.product_id where pl.pl_ordertrn_status=0 and pl.pl_order_id='$id'";
    $rs_type = $dbcon->query($query);
    if ($id == '0')
    {
        $psel = 'selected="selected"';
    }
    $str = '<option value="" >--Choose Product--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type))
    {
        $sel = '';
        if ($row['product_id'] == $id)
        {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['product_id'] . '">' . $row['product_name'] . '</option>';
    }
    return $str;
}

function get_bom_id_by_product($dbcon, $id)
{
    $q = $dbcon->query("select bom_id from tbl_bom where bom_product='$id' and bom_status='0'");
    $row = brp_mysqli_fetch_array($q);
    return $row['bom_id'];
}

function get_po_for_grn($dbcon, $purchaseorder_id, $vender_id, $mode)
{
    $str = '';
    $query = "select * from tbl_purchaseorder as so where status=0 and (select IFNULL(sum(product_qty),0) as qty from tbl_purchaseordertrn as sosub  where purchaseordertrn_status=0 and sosub.purchaseorder_id=so.purchaseorder_id ) > (select IFNULL(sum(product_qty),0) as qty  from tbl_grn as chall left join tbl_grn_trn as chtrn on chtrn.grn_id=chall.grn_id where grn_status=0 and chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=so.purchaseorder_id) and so.po_approval_status=1 and so.vender_id=" . $vender_id . " and company_id=" . $_SESSION['company_id'];

    if ($mode == 'Edit')
    {
        $query_edit = "select purchaseorder_id,purchaseorder_no from tbl_purchaseorder where status=0 and purchaseorder_id=" . $purchaseorder_id . " and vender_id=" . $vender_id . " and company_id=" . $_SESSION['company_id'];
        $rs_dispatc = $dbcon->query($query_edit);
    }

    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Purchase Order</option>';
    while ($res = brp_mysqli_fetch_assoc($rs_dispatc))
    {
        $sel = '';
        if ($res['purchaseorder_id'] == $purchaseorder_id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $res['purchaseorder_id'] . '">' . $res['purchaseorder_no'] . '</option>';
    }
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        if ($rel['purchaseorder_id'] == $purchaseorder_id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['purchaseorder_id'] . '">' . $rel['purchaseorder_no'] . '</option>';
    }
    return $str;
}

function get_all_po_for_grn($dbcon, $purchaseorder_id, $vender_id, $mode, $potype)
{
    $ven = '';
    $ty = '';
    if (!empty($vender_id))
    {
        $ven = " and so.vender_id=" . $vender_id;
    }
    if ($potype == '3')
    {
        $ty = " and so.po_type=1";
    }
    $str = '';
    $query = "select * from tbl_purchaseorder as so 
    left join tbl_purchaseordertrn as trn on trn.purchaseorder_id = so.purchaseorder_id
    where so.status=0 and so.po_approval_status=1  and so.revise_status=0 and trn.used_status=0 and trn.purchaseordertrn_status=0 " . $ven . " " . $ty . " and so.company_id=" . $_SESSION['company_id'] . " group by so.purchaseorder_id";

    /* $query="select * from tbl_purchaseorder as so where status=0 and (select IFNULL(sum(product_qty),0) as qty from tbl_purchaseordertrn as sosub  where purchaseordertrn_status=0 and sosub.purchaseorder_id=so.purchaseorder_id ) > (select IFNULL(sum(product_qty),0) as qty  from tbl_grn as chall left join tbl_grn_trn as chtrn on chtrn.grn_id=chall.grn_id where grn_status=0 and chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=so.purchaseorder_id) and so.po_approval_status=1 and used_status=0 ".$ven." and company_id=".$_SESSION['company_id']; */

    if ($mode == 'Edit')
    {
        $query = "select purchaseorder_id,purchaseorder_no from tbl_purchaseorder where status=0 and purchaseorder_id=" . $purchaseorder_id . " and company_id=" . $_SESSION['company_id'];
        //$rs_dispatc=$dbcon->query($query_edit);
        
    }
    // echo $query;
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Order</option>';
    while ($res = brp_mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        if ($res['purchaseorder_id'] == $purchaseorder_id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $res['purchaseorder_id'] . '">' . $res['purchaseorder_no'] . '</option>';
    }

    return $str;
}



function get_all_jobwork_for_grn($dbcon, $id, $vender_id, $mode)
{
    $str = '';
    //$query="select * from tbl_jobwork as jo where status=0 and job_close_status=0 and company_id=".$_SESSION['company_id'];
    $query = 'select jo.*,pr.product_name,(select COALESCE(sum(p.product_qty),0) as tqty from tbl_grn as j left join tbl_grn_trn as p on p.grn_id=j.grn_id 
    where j.purchaseorder_id=jo.jobwork_id and grn_status=0 and ref_type=1 and grn_trn_status=0) as tqty from tbl_jobwork as jo 
    left join product_mst as pr on pr.product_id=jo.j_product_id 
    where jo.job_close_status="0" and jo.status="0" and  jo.company_id=' . $_SESSION['company_id'] . ' HAVING j_qty>tqty';

    if ($mode == 'Edit')
    {
        $query = "select * from tbl_jobwork where status=0 and jobwork_id=" . $id . " and company_id=" . $_SESSION['company_id'];
        // $rs_dispatc=$dbcon->query($query_edit);
        
    }

    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Order</option>';
    while ($res = brp_mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        if ($res['jobwork_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $res['jobwork_id'] . '">' . $res['jobwork_no'] . '</option>';
    }

    return $str;
}

function get_po_for_grn_trn($dbcon, $purchaseorder_id, $product_id, $mode)
{
    $str = '';
    if ($mode == 'Edit')
    {
        $query = "select trn.product_id,pro.product_name from tbl_purchaseordertrn as trn
        left join product_mst as pro on pro.product_id=trn.product_id
        where trn.purchaseordertrn_status=0 and trn.product_id=" . $product_id . " and trn.purchaseorder_id=" . $purchaseorder_id;
    }
    else
    {
        $query = "select trn.product_id,pro.product_name,trn.product_qty,main_grn_qty from tbl_purchaseordertrn as trn
        left join product_mst as pro on pro.product_id=trn.product_id
        left join (SELECT purchaseorder_id,product_id,sum(product_qty) as main_grn_qty FROM tbl_grn_trn as chtrn where chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=" . $purchaseorder_id . " group by chtrn.product_id,chtrn.purchaseorder_id) as chtrn on chtrn.product_id=trn.product_id
        where trn.purchaseordertrn_status=0 and trn.purchaseorder_id=" . $purchaseorder_id . " having trn.product_qty>main_grn_qty or main_grn_qty is NULL";
    }
    $rs_trn = $dbcon->query($query);
    $str = '<option value="">Choose Product</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_trn))
    {
        $sel = '';
        if ($rel['product_id'] == $product_id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '">' . $rel['product_name'] . '</option>';
    }
    return $str;
}

function get_all_parameter($dbcon, $id)
{
    $str = '';
    $query = "select * from tbl_qc_param where p_status='0' order by p_name";
    $rs_product = $dbcon->query($query);
    $str = '<option value="">Choose Parameter</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product))
    {
        $sel = '';
        if ($rel['p_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['p_id'] . '">' . $rel['p_name'] . '</option>';
    }
    return $str;
}

function get_all_grn($dbcon, $id)
{
    $str = '';
    $query = "select * from tbl_grn where grn_status='0' and qc_status='0' order by grn_no";
    $rs_product = $dbcon->query($query);
    $str = '<option value="">Choose GRN</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product))
    {
        $sel = '';
        if ($rel['grn_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['grn_id'] . '">' . $rel['grn_no'] . '</option>';
    }
    return $str;
}

function check_delete_trn($dbcon, $chk_arr)
{
    $chk_flag = false;
    foreach ($chk_arr as $fil_arr)
    {
        $col = $fil_arr[0];
        $tbl = $fil_arr[1];
        $whr = $fil_arr[2];
        $chk_qry = "select $col from $tbl where $whr";
        $chk_nums = brp_mysqli_num_rows($dbcon->query($chk_qry));
        if ($chk_nums)
        {
            return $chk_flag = true;
        }
    }
    return $chk_flag;
}

function get_po_details_for_grn_trn($dbcon, $id, $type, $mode, $eid, $vender_id, $branch_id)
{
    $str = '';
    if (!empty($eid))
    {
        $grn_ids = " and grn_id!=" . $eid;
    }
    if (!empty($vender_id))
    {
        $ven = " and op.vender_id=" . $vender_id;
    }
    if (!empty($id))
    {
        $po = " and po.purchaseorder_id=" . $id;
    }
    $branch_where = " and po.branch_id=" . $branch_id;
    //$branch_where=" and branch_id=".$branch_id;
    $query = "select po.*,sum(po.product_qty)as produ_qty,sum(po.product_conv_qty)as produ_con_qty,tc.cat_name,p.product_name,p.product_type,unit.unit_name,group_concat(po.purchaseordertrn_id ORDER BY po.purchaseordertrn_id ASC) as trn_id,group_concat(po.po_ref_id ORDER BY po.po_ref_id DESC) as ref_id,con_unit.unit_name as conv_unit_name from tbl_purchaseordertrn as po 
    left join product_mst as p on p.product_id=po.product_id
    left join tbl_category as tc on p.product_category=tc.cat_id 
    left join unit_mst as unit on unit.unitid=po.unit_id
    left join unit_mst as con_unit on con_unit.unitid=po.conv_unit_id
    left join tbl_purchaseorder as op on op.purchaseorder_id=po.purchaseorder_id
    where op.po_approval_status=1 and po.used_status=0 and purchaseordertrn_status=0 " . $branch_where . " " . $ven . " " . $po . " group by po.product_id,po.unit_id,po.conv_unit_id";
    $rs_product = $dbcon->query($query);
    $cnt = 1;
    while ($row = brp_mysqli_fetch_array($rs_product))
    {
        $cat_name = ($row['cat_name'] != null) ? $row['cat_name'] : 'PRIMARY';
        $query1 = "select sum(product_qty) as done_qty,sum(product_conv_qty) as conv_done_qty from tbl_grn_sub_trn as po where status=0 and purchaseordertrn_id in (" . $row['trn_id'] . ")";
        $rs_product1 = $dbcon->query($query1);
        $row1 = brp_mysqli_fetch_array($rs_product1);

        $pending_qty = $row['produ_qty'] - $row1['done_qty'];
        $pending_conv_qty = $row['produ_con_qty'] - $row1['conv_done_qty'];

        /*
        Code By Umair
        Comment: Below code is commented and updating new code to check qc parameter added or not according to pathik
        Date: 27/03/2021
        */

        /*$pr_setting=get_pro_field($dbcon,$row['product_id'],'product_setting_check');
        $pr_setting_arr=explode(",",$pr_setting);
        if(in_array("product_qc",$pr_setting_arr))
        {
        $qc_st="yes";
        $sty="display:none;";
        }else{
        $qc_st="no";
        $sty="";
    }*/
    $qc_paramter_info = check_product_qc_paramter($dbcon, $row['product_id'], "-1");
    if ($qc_paramter_info == '1')
    {
        $qc_st = "yes";
        $sty = "display:none;";
    }
    else
    {
        $qc_st = "no";
        $sty = "";
    }

    if (!empty($eid))
    {
        $query11 = "select * from tbl_grn_trn as mst
        where mst.grn_id=" . $eid . " and product_id=" . $row['product_id'] . " and purchaseorder_id=" . $row['purchaseorder_id'];
        $rol = brp_mysqli_fetch_assoc($dbcon->query($query11));

        if ($rol['product_qc'] == 1)
        {
            $ronly = "readonly";
        }
        else
        {
            $ronly = "";
        }
    }
    $tolerance = get_pro_field($dbcon, $row['product_id'], 'tolerance');
    $maximum_tolerance = get_pro_field($dbcon, $row['product_id'], 'maximum_tolerance');
    $minimum_tolerance = get_pro_field($dbcon, $row['product_id'], 'minimum_tolerance');
    if ($tolerance == "1")
    {
            // $maximum_tolerance=get_pro_field($dbcon,$row['product_id'],'maximum_tolerance');
        $pending_qty1 = $pending_qty;
    }
    else
    {
        $pending_qty1 = $pending_qty;
    }
        /* Code By Umair: 29/10/2020
        Comment: I have removed the max value from the input tag for tolerance functionality for grn module.
        ".$pending_qty1."
        */

        $str .= "<tr id='trid" . $cnt . "'>
        <!--<td>" . $cnt . "</td>-->
        <td>" . get_product_type_name($dbcon, $row['product_type']) . " " . $row['abc'] . "</td>
        <td>" . $row['product_name'] . "<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$cnt' value='" . $row['product_id'] . "' /></td>
        <td>" . $cat_name . "</td>
        <td>
        <div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
        </br> " . number_format($row['produ_con_qty'], 4, ".", "") . " </br> " . $row['conv_unit_name'] . " 
        </div>
        </br>
        <div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'>
        " . number_format($row['produ_qty'], 4, ".", "") . " </br> <span>" . $row['unit_name'] . "</span> 
        </div>
        </td>
        <td>

        <div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
        </br> " . number_format($pending_conv_qty, 4, ".", "") . " </br> " . $row['conv_unit_name'] . " 
        </div>
        </br>
        <div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'>
        " . number_format($pending_qty, 4, ".", "") . " </br> " . $row['unit_name'] . " 
        </div>
        <td>

        <div style='background-color: #c3c1c1;margin: -5px;padding: 10px;'>
        <input type='number' min='0' max='' data-pendingqty='" . $pending_qty1 . "' data-pid='" . $row['product_id'] . "' data-qty='" . $row['produ_qty'] . "' data-mini-tol='" . $minimum_tolerance . "' data-max-tol='" . $maximum_tolerance . "' data-tol='" . $tolerance . "' class='form-control qty_mangement' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='" . $rol['product_qty'] . "' " . $ronly . " onkeyup='product_convert_qty(1," . $cnt . ");' />
        " . $row['conv_unit_name'] . "

        <input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
        <input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='" . $row["conv_unit_id"] . "' />
        </div>
        ";

        if ($row["unit_id"] != $row["conv_unit_id"])
        {
            $str .= "<br/>
            <div style='background-color: #eae7e7;margin: -5px;padding: 10px;'>
            <input type='number' class='form-control'  name='grn_qty[]' id='grn_qty$cnt' value='" . $rol['product_qty'] . "' " . $ronly . " onkeyup='product_convert_qty(2," . $cnt . ");' />
            " . $row['unit_name'] . "
            <input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
            <input type='hidden' name='unit_id[]' id='unit_id$cnt' value='" . $row["unit_id"] . "' />
            </div>
            ";
        }
        else
        {
            /*$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
            <input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
            <input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />";*/

            $str .= "<input type='hidden' min='0' max='' class='form-control ' name='grn_qty[]' id='grn_qty$cnt' value='" . $rol['product_qty'] . "' " . $ronly . " />
            <input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
            <input type='hidden' name='unit_id[]' id='unit_id$cnt' value='" . $row["unit_id"] . "' />";
        }

        $str .= "</td>
        <!--<td>
        <input type='number' min='0' max='' data-pendingqty='" . $pending_qty1 . "' data-pid='" . $row['product_id'] . "' data-qty='" . $row['produ_qty'] . "' data-mini-tol='" . $minimum_tolerance . "' data-max-tol='" . $maximum_tolerance . "' data-tol='" . $tolerance . "' class='form-control qty_mangement' name='grn_qty[]' id='grn_qty$cnt' value='" . $rol['product_qty'] . "' " . $ronly . " />
        <input type='hidden' name='unit_id[]' id='unit_id$cnt' value='" . $row["unit_id"] . "' />
        </td>-->
        <td>
        <select class='form-control' name='grn_godown[]' style='" . $sty . "' id='grn_godown$cnt' required >";
        $str .= get_all_godown($dbcon, $rol['grn_godown'], 1);
        $str .= "</select>
        <input type='hidden' name='qc_type[]' id='qc_type$cnt' value='" . $qc_st . "' />
        <input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='" . $rol['grn_trn_id'] . "' />
        <input type='hidden' name='qc_status[]' id='qc_status$cnt' value='" . $rol['product_qc'] . "' />
        <input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='" . $row['ref_id'] . "' />
        <input type='hidden' name='purchaseordertrn_id[]' id='purchaseordertrn_id$cnt' value='" . $row['trn_id'] . "' />

        </td>
        <td>
        <button type='button' class='btn btn-round btn-danger btn-xs' onclick='remove_data(" . $cnt . ");' id='fieldremove" . $cnt . "'><i class='fa fa-times'></i></button>
        </td>
        </tr>";

        $cnt++;
    }

    return $str;
}
function grn_po_sub_trn($dbcon, $grn_trn_id, $purchaseordertrn_id, $job_work_po_trn_id = 0)
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
    
    $min_toll = intval($row1['minimum_tolerance']);
    $max_toll = intval($row1['maximum_tolerance']);
    
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
        $min_toll_qty = (intval($row['product_qty']) * $min_toll) / 100;
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
                                    $mm = purchaseorder_delivery_datewise_used_qty_update($dbcon, $row['purchaseordertrn_id'], $used_base_qty, $re['product_base_unit'],$used_conv_qty, $re['product_conv_unit']);
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
                        $mm = purchaseorder_delivery_datewise_used_qty_update($dbcon, $row['purchaseordertrn_id'], $base_stock, $re['product_base_unit'],$con_stock,$re['product_conv_unit']);
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
                            $qty = $qty - $used_base_qty;
                            
                            $mm = purchaseorder_delivery_datewise_used_qty_update($dbcon, $row['purchaseordertrn_id'], $base_stock, $re['product_base_unit'],$con_stock, $re['product_conv_unit']);
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

                    $mm = purchaseorder_delivery_datewise_used_qty_update($dbcon, $row['purchaseordertrn_id'], $base_stock, $re['product_base_unit'], $con_stock, $re['product_conv_unit']);
                }

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

    $mm = purchaseorder_delivery_datewise_used_qty_update($dbcon, $purchaseordertrn_id, $used_base_qty, $row1['unit_id'],$used_conv_qty, $row1['product_conv_unit']);
    purchase_order_grn_used_status_update($dbcon, $row1['purchaseordertrn_id']);
}

    //return $mm;
    //return $query1;

}


function purchase_order_grn_used_status_update($dbcon, $purchse_order_trn_id)
{
    $query1 = "select purchaseorder_id, product_qty, product_conv_qty, rate_unit, product_id from tbl_purchaseordertrn as po where purchaseordertrn_id =" . $purchse_order_trn_id;
    $rs_product1 = $dbcon->query($query1);
    $row1 = brp_mysqli_fetch_array($rs_product1);
    
    $query2 = "select sum(product_qty) as used_qty,sum(product_conv_qty) as used_conv_qty from tbl_grn_sub_trn where status=0 and purchaseordertrn_id =" . $purchse_order_trn_id;
    $rs_product2 = $dbcon->query($query2);
    $row2 = brp_mysqli_fetch_array($rs_product2);

    $que = "select * from product_mst as ta where product_id=" . $row1['product_id'];
    $rs_di = $dbcon->query($que);
    $re = brp_mysqli_fetch_assoc($rs_di);

    // print_r($re);
    if ($re['product_conv_unit'] == $row1['rate_unit'])
    {
        $total_qty = number_format($row1['product_conv_qty'], 4, ".", "");
        $used_qty = number_format($row2['used_conv_qty'], 4, ".", "");
        // echo "conv Total qty : " . $total_qty . "</br>";
        // echo "conv used_qty qty : " . $used_qty . "</br>";
        
    // }
    // else
    // {
        $total_qty = number_format($row1['product_qty'], 4, ".", "");
        $used_qty = number_format($row2['used_qty'], 4, ".", "");
        /*echo "base Total qty : " . $total_qty . "</br>";
        echo "base used_qty qty : " . $used_qty . "</br>";*/
    // }

    /* echo "rate unit:" . $row1['rate_unit']. "</br>";
    echo "base uni : " . $re['product_base_unit'] . "</br>";
    echo "conv unit : " .$re['product_conv_unit'] . "</br>";
    echo "Total qty : " . $total_qty . "</br>";
    echo "used_qty qty : " . $used_qty . "</br>";*/
    if ($total_qty <= $used_qty)
    {
        $info['used_status'] = 1;
    }
    else
    {
        $info['used_status'] = 0;
    }
    
    $updateid = update_record('tbl_purchaseordertrn', $info, "purchaseordertrn_id=" . $purchse_order_trn_id, $dbcon);

    $query3 = "select count(purchaseordertrn_id) as cou from tbl_purchaseordertrn as po where purchaseordertrn_status=0 and used_status=0 and purchaseorder_id =" . $row1['purchaseorder_id'];
    $rs_product3 = $dbcon->query($query3);
    $row3 = brp_mysqli_fetch_array($rs_product3);
    if ($row3['cou'] <= 0)
    {
        $info4['used_status'] = 1;
    }
    else
    {
        $info4['used_status'] = 0;
    }
    $updateid = update_record('tbl_purchaseorder', $info4, "purchaseorder_id=" . $row1['purchaseorder_id'], $dbcon);
}
}

function get_vender_id($dbcon, $id, $grn_type)
{
    if ($grn_type == 2)
    {
        $q = $dbcon->query("select vender_id from tbl_purchaseorder where purchaseorder_id='$id'");
        $row = mysqli_fetch_array($q);
        $return = $row['vender_id'];
    }
    else
    {
        $q = $dbcon->query("select j_vendor from tbl_jobwork where jobwork_id='$id'");
        $row = mysqli_fetch_array($q);
        $return = $row['j_vendor'];
    }

    return $return;
}

function get_vender_name($dbcon, $id)
{

    $q = $dbcon->query("select l_name from tbl_ledger where l_id='$id'");
    $row = mysqli_fetch_array($q);
    $return = $row['l_name'];

    return $return;
}

function get_request_id_jobwork($dbcon, $id)
{
    $q = $dbcon->query("select j_ref_id from tbl_jobwork where jobwork_id='$id'");
    $row = mysqli_fetch_array($q);
    $return = $row['j_ref_id'];

    return $return;
}

function get_jobwork_details_for_grn_trn($dbcon, $id, $type, $mode, $eid, $vender_id, $order_id = null)
{

    $str = '';
    $po = '';
    if (!empty($vender_id))
    {

        $ven = "  j.j_vendor=" . $vender_id . " and";
    }
    if (!empty($id))
    {
        $po = "  j.jobwork_id=" . $id;
    }

    if (empty($po))
    {
        $ven = trim($ven, ' and');
    }

    /*$query="select j.*,p.product_name,p.product_type,sum(jt.product_qty) as tqty,unit.unit_name,unit.unitid from tbl_jobwork as j
    left join product_mst as p on p.product_id=j.j_product_id
    left join tbl_grn_trn as jt on jt.purchaseorder_id=j.jobwork_id
    LEFT join unit_mst as unit on unit.unitid=j.process_unit
    where ".$ven." ".$po." order by j.jobwork_id ";*/

    /*
    Code By Umair: 21/01/2021
    
    */
    $query = "select j.*,prom.process_name,tc.cat_name,p.product_name,p.product_type,unit.unit_name,unit.unitid, sum(j.j_qty) as j_t_qty,group_concat(DISTINCT j.jobwork_id order by j.jobwork_id) as jobworkid from tbl_jobwork as j 
    left join product_mst as p on p.product_id=j.j_product_id 
    left join process_mst as prom on prom.process_id=j.j_pr_process_id
    left join tbl_category as tc on p.product_category=tc.cat_id 
    LEFT join unit_mst as unit on unit.unitid=j.process_unit where " . $ven . " " . $po . " 
    group by j_product_id,j_pr_process_id 
    order by j.jobwork_id ";
    $rs_product = $dbcon->query($query);
    $cnt = 1;
    while ($row = brp_mysqli_fetch_array($rs_product))
    {
        $cat_name = ($row['cat_name'] != null) ? $row['cat_name'] : 'PRIMARY';
        if (!empty($eid))
        {
            $grn_ids = " and grn_id!=" . $eid;
        }
        /* $query_u="select sum(p.product_qty) as tqty from tbl_grn as j
        left join tbl_grn_trn as p on p.grn_id=j.grn_id
        where j.purchaseorder_id=".$row['jobwork_id']." ".$grn_ids." and grn_status=0 and ref_type=1 and grn_trn_status=0 ";
        $rs_product_u=$dbcon->query($query_u);
        $row_u=brp_mysqli_fetch_array($rs_product_u); */

        $query_u = "select sum(strn.product_qty) as tqty from tbl_grn as j 
        left join tbl_grn_trn as p on p.grn_id=j.grn_id 
        left join tbl_grn_sub_trn as strn on strn.grn_trn_id=p.grn_trn_id
        where strn.jobwork_id=" . $row['jobwork_id'] . " " . $grn_ids . " and j.grn_status=0 and strn.status=0 and j.ref_type=1 and p.grn_trn_status=0 ";
        $rs_product_u = $dbcon->query($query_u);
        $row_u = brp_mysqli_fetch_array($rs_product_u);

        $query_ww = "select group_concat(DISTINCT p.p_ref_id ORDER BY p.p_ref_id ASC) as prf_id from tbl_jobwork_process as j 
        left join tbl_allocate_process as p on p.p_id=j.p_id 
        where j.jobwork_id in (" . $row['jobworkid'] . ") and status=0 ";
        $rs_product_u1 = $dbcon->query($query_ww);
        $row_u1 = mysqli_fetch_array($rs_product_u1);

        if (!empty($eid))
        {

            $query11 = "select * from tbl_grn_trn as mst
            where mst.grn_id=" . $eid . " and product_id=" . $row['j_product_id'] . " and purchaseorder_id in (" . $row['jobworkid'] . ")";
            $rol = brp_mysqli_fetch_assoc($dbcon->query($query11));

            if ($rol['product_qc'] == 1)
            {
                $ronly = "readonly";
            }
            else
            {
                $ronly = "";
            }
        }

        $pending_qty = $row['j_t_qty'] - $row_u['tqty'];

        /*
        Code By Umair
        Comment: Below code is commented and updating new code to check qc parameter added or not according to pathik
        Date: 27/03/2021
        */

        /*$pr_setting=get_pro_field($dbcon,$row['j_product_id'],'product_setting_check');
        $pr_setting_arr=explode(",",$pr_setting);
        if(in_array("product_qc",$pr_setting_arr))
        {
        $qc_st="yes";
        $sty="display:none;";
        }else{
        $qc_st="no";
        $sty="";
    }*/
    $qc_paramter_info = check_product_qc_paramter($dbcon, $row['j_product_id'], $row['j_pr_process_id']);
    if ($qc_paramter_info == '1')
    {
        $qc_st = "yes";
        $sty = "display:none;";
    }
    else
    {
        $qc_st = "no";
        $sty = "";
    }

    $str .= "<tr>
    <td>" . $cnt . "</td>
    <td>" . get_product_type_name($dbcon, $row['product_type']) . "</td>
    <td>" . $row['product_name'] . " (" . $row['process_name'] . ")
    <input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$id' value='" . $row['j_product_id'] . "' /></td>
    <td>" . $cat_name . "</td>
    <td>" . $row['j_t_qty'] . "</td>
    <td>" . $pending_qty . "</td>
    <td><input type='text' class='form-control' max='" . $pending_qty . "' name='grn_qty[]' id='grn_qty$id' value='" . $rol['product_qty'] . "' " . $ronly . " /></td>
    <td>" . $row['unit_name'] . "
    <input type='hidden' name='unit_id[]' id='unit_id$cnt' value='" . $row["process_unit"] . "' />
    </td>
    <td>
    <select class='form-control' name='grn_godown[]' style='" . $sty . "' id='grn_godown$cnt' required >";
    $str .= get_all_godown($dbcon, $rol['grn_godown'], 1);
    $str .= "</select>
    <input type='hidden' name='qc_type[]' id='qc_type$cnt' value='" . $qc_st . "' />
    <input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='" . $rol['grn_trn_id'] . "' />
    <input type='hidden' name='qc_status[]' id='qc_status$cnt' value='" . $rol['product_qc'] . "' />
    <input type='hidden' name='j_alloc_process_id[]' id='j_alloc_process_id$cnt' value='" . $row['j_alloc_process_id'] . "' />
    <!--<input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='" . $row['j_ref_id'] . "' />-->

    <input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='" . $row_u1['prf_id'] . "' />
    <!--<input type='text' name='po_ref_id[]' id='po_ref_id$cnt' value='" . $query_ww . "' />-->
    <input type='hidden' name='j_job_work_id[]' id='j_job_work_id$cnt' value='" . $row['jobworkid'] . "' />
    <input type='hidden' name='j_pr_process_id[]' id='j_pr_process_id$cnt' value='" . $row['j_pr_process_id'] . "' />
    </td>

    </tr>";

    $cnt++;
}

return $str;
    //return $query_u;

}
function get_jobwork_details_for_grn_trn_20_08_2020($dbcon, $id, $type, $mode, $eid)
{
    $str = '';
    $query = "select j.*,p.product_name,p.product_type,sum(jt.product_qty) as tqty,unit.unit_name,unit.unitid from tbl_jobwork as j 
    left join product_mst as p on p.product_id=j.j_product_id 
    left join tbl_grn_trn as jt on jt.purchaseorder_id=j.jobwork_id 
    LEFT join unit_mst as unit on unit.unitid=j.process_unit
    where j.jobwork_id='$id' order by j.jobwork_id ";
    $rs_product = $dbcon->query($query);
    $cnt = 1;
    while ($row = brp_mysqli_fetch_array($rs_product))
    {
        if (!empty($eid))
        {
            $grn_ids = " and grn_id!=" . $eid;
        }
        $query_u = "select sum(p.product_qty) as tqty from tbl_grn as j 
        left join tbl_grn_trn as p on p.grn_id=j.grn_id 
        where j.purchaseorder_id=" . $row['jobwork_id'] . " " . $grn_ids . " and grn_status=0 and ref_type=1 and grn_trn_status=0 ";
        $rs_product_u = $dbcon->query($query_u);
        $row_u = mysqli_fetch_array($rs_product_u);

        if (!empty($eid))
        {
            $query11 = "select * from tbl_grn_trn as mst
            where mst.grn_id=" . $eid . " and product_id=" . $row['j_product_id'] . " and purchaseorder_id=" . $row['jobwork_id'];
            $rol = brp_mysqli_fetch_assoc($dbcon->query($query11));

            if ($rol['product_qc'] == 1)
            {
                $ronly = "readonly";
            }
            else
            {
                $ronly = "";
            }
        }

        $pending_qty = $row['j_qty'] - $row_u['tqty'];

        $pr_setting = get_pro_field($dbcon, $row['j_product_id'], 'product_setting_check');

        $pr_setting_arr = explode(",", $pr_setting);

        if (in_array("product_qc", $pr_setting_arr))
        {
            $qc_st = "yes";
            $sty = "display:none;";
        }
        else
        {
            $qc_st = "no";
            $sty = "";
        }

        $str = "<tr>
        <td>" . $cnt . "</td>
        <td>" . get_product_type_name($dbcon, $row['product_type']) . "</td>
        <td>" . $row['product_name'] . "<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$id' value='" . $row['j_product_id'] . "' /></td>
        <td>" . $row['j_qty'] . "</td>
        <td>" . $pending_qty . "</td>
        <td><input type='text' class='form-control' max='" . $pending_qty . "' name='grn_qty[]' id='grn_qty$id' value='" . $rol['product_qty'] . "' " . $ronly . " /></td>
        <td>" . $row['unit_name'] . "
        <input type='hidden' name='unit_id[]' id='unit_id$cnt' value='" . $row["process_unit"] . "' />
        </td>
        <td>
        <select class='form-control' name='grn_godown[]' style='" . $sty . "' id='grn_godown$cnt' required >";
        $str .= get_all_godown($dbcon, $rol['grn_godown'], 1);
        $str .= "</select>
        <input type='hidden' name='qc_type[]' id='qc_type$cnt' value='" . $qc_st . "' />
        <input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='" . $rol['grn_trn_id'] . "' />
        <input type='hidden' name='qc_status[]' id='qc_status$cnt' value='" . $rol['product_qc'] . "' />
        <input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='" . $row['j_ref_id'] . "' />
        </td>

        </tr>";

        $cnt++;
    }

    return $str;
    //return $query;
    
}

function get_branch_from_zone($dbcon, $zone, $id, $sindex)
{
    $str = '';
    $query = "select * from branch_mst where branch_status='0' and zoneid='$zone' order by branch_name ";
    $rs_product = $dbcon->query($query);

    $str .= '<option value="">Choose Branch</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product))
    {
        $sel = '';
        if ($rel['branch_id_customer'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['branch_id'] . '">' . $rel['branch_name'] . '</option>';
    }
    return $str;
}

function build_category_tree($dbcon, $product, $parent, $indent = "")
{
    $r = $dbcon->query("SELECT * FROM tbl_bomtrn WHERE parent_id = " . $parent . " and sale_product_id='$product'");

    while ($c = mysqli_fetch_array($r))
    {

        $output .= "<option value=\"" . $c["product_id"] . "\" " . $selected . ">" . get_pro_field($dbcon, $c["product_id"], "product_name") . "</option>";
        build_category_tree($dbcon, $product, $c["bom_trn_id"], $indent . "&nbsp;&nbsp;");
    }
    echo $output;
}

function getEmployeeIdComplain($dbcon, $id)
{
    $query = "select emp_id from tbl_complaint where complaint_id=$id";
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
    return $rel['emp_id'];
}

function get_last_remark($dbcon, $ex_id)
{
    $query = "select eh_remark from tbl_expense_status_history where eh_ex_id='$ex_id' order by eh_id desc";
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
    return $rel['eh_remark'];
}

function get_picode($dbcon)
{
}

function get_product_process($dbcon, $id, $product_id)
{
    $query = "select * from tbl_wororder_product_process where rp_id='$id' and process_priority='1' and product_id=" . $product_id;
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
    //$row=array();
    $row['process_id'] = $rel['process_id'];
    $row['process_type'] = $rel['process_type'];
    $row['process_priority'] = $rel['process_priority'];
    $row['description'] = $rel['description'];

    return json_encode($row);
}

function get_current_process($dbcon, $job_id, $product_id)
{
    $query = "select j_pr_process_id from tbl_jobwork where j_product_id='$product_id' and jobwork_id='$job_id'";
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
    //$row=array();
    return $rel['j_pr_process_id'];
}
function get_current_process_allocate($dbcon, $job_id, $product_id)
{
    $query = "select j_alloc_process_id from tbl_jobwork where j_product_id='$product_id' and jobwork_id='$job_id'";
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
    //$row=array();
    return $rel['j_alloc_process_id'];
}

function get_current_process_type($dbcon, $job_id, $product_id)
{
    $query = "select j_process_type from tbl_jobwork where j_product_id='$product_id' and jobwork_id='$job_id'";
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
    //$row=array();
    return $rel['j_process_type'];
}

function count_process_qty($dbcon, $id, $type)
{
    /*$user_type = $_SESSION['user_type'];
    $where_user_wise = '';
    if($user_type!='2'){
    $where_user_wise = 'and resource_id="'.$_SESSION['resource_id'].'"';
}*/
$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
$whre = "";
if (!empty($_SESSION['branch_id']))
{
    $whre = " and branch_id=" . $branch_id;
}
/* $query="select (select COALESCE(sum(pen_qty),0) as sqty1 from tbl_allocate_process where process_id='$id' ".$whre." and pr_process_type='$type')  as sqty,( select COALESCE(sum(pt_qty),0) as stqty1 from tbl_allocate_process_trn apt left join tbl_allocate_process as ap on ap.p_id=apt.pt_alloc_id where pt_process_id='$id' and ap.pr_process_type='$type' ) as stqty"; */


$query = "select IFNULL(sum(pen_qty),0) as sqty,IFNULL(sum(variation_qty_plus),0) as variation_qty_plus,IFNULL(sum(variation_qty_minus),0) as variation_qty_minus from tbl_allocate_process where process_id='$id' " . $whre . " and company_id=" . $_SESSION['company_id'] . " and p_status !=2 and pr_process_type='$type'";

$rs_cust = $dbcon->query($query);
$rel = brp_mysqli_fetch_array($rs_cust);

    //$total=$rel['sqty']-$rel['stqty'];
$total = $rel['sqty'] + $rel['variation_qty_minus'];

if ($total == 0)
{
    return 0;
}
else
{
    return $total;
}
    //return $id;

}

function count_working_process_qty_24_12_2020($dbcon, $id, $type)
{
    $is_available = count_process_qty($dbcon, $id, $type);
    $p_qty = 0;
    if ($is_available > 0)
    {
        $user_type = $_SESSION['user_type'];
        $where_user_wise = '';
        if ($user_type != '2')
        {
            $where_user_wise = 'and resource_id="' . $_SESSION['resource_id'] . '"';
        }

        $q = $dbcon->query("select ap.*,p.product_type,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 
           left join product_mst as p on p.product_id=ap.p_product_id 
           left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
           left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id
           where ap.p_status IN (0,1) and process_id=" . $id . " and pr_process_type='$type' $where_user_wise");

        //$dddd="select ap.*,p.product_type from tbl_allocate_process as ap left join product_mst as p on p.product_id=ap.p_product_id where ap.p_status IN (0,1) and pr_process_type='$type'";
        while ($rel = brp_mysqli_fetch_array($q))
        {
            $pid = $rel['p_product_id'];

            $where = '';
            //$pp=$rel['product_type'];
            if ($rel['product_type'] == 0)
            {
                $where .= " and parent_id = '0' and sale_product_id='$pid'";
            }
            else
            {
                $where .= " and parent_id = (select bom_trn_id from tbl_bomtrn where product_id='$pid' order by bom_trn_id desc limit 0,1)";
            }
            if ($rel['p_status'] == 1)
            {
                //$unused=$rel['p_qty']-$rel['start_qty'];
                //$min_machine=$rel['pen_qty']-$unused;
                //$pending_qty=$rel['pen_qty']-$unused;
                //$min_machine=$rel['strtt_qty']-$rel['end_qty'];
                //$pending_qty=$rel['strtt_qty']-$rel['end_qty'];
                $min_machine = $rel['start_qty'];
                $min_machine111 = $rel['strtt_qty'] - $rel['end_qty'];
                $pending_qty = $rel['pen_qty'];
                if ($min_machine111 > $pending_qty)
                {
                    $min_machine111 = $pending_qty;
                }
            }
            else if ($rel['previous_process_id'] == 0)
            {
                $cur_stock = 0;
                $machine_make = array();
                $q12 = $dbcon->query("select * from tbl_request_product as ap 
                 where status=0 and perent_id=" . $rel['p_ref_id']);
                while ($rel_n1 = mysqli_fetch_array($q12))
                {
                    $o_qty = convert_stock($dbcon, $rel_n1['req_qty_one'], $rel_n1['rp_id'], "base_unit");
                    $required_qty = $rel['p_qty'] * $o_qty;
                    //var_dump($required_qty);
                    //$cur_stock=reserve_stock($dbcon,$row['product_id'],$row['product_base_unit']);
                    $cur_stock = reserve_stock($dbcon, $rel_n1['rp_pid'], $rel_n1['purchase_unit'], "", $rel_n1['rp_id']);
                    //var_dump($cur_stock);
                    $total = $cur_stock;
                    if ($total < 0)
                    {
                        $total = 0;
                    }
                    if ($total > $required_qty)
                    {
                        $usable = $required_qty;
                    }
                    else
                    {

                        //var_dump($total."===".$o_qty);    //$usable=round(($total/$rel_n1['req_qty_one']),0,PHP_ROUND_HALF_DOWN);
                        //$usable=round(($total/$o_qty),0,PHP_ROUND_HALF_DOWN);
                        $usable = $total / $o_qty;
                        //var_dump($total/$rel_n1['req_qty_one']);
                        //$usable=$usable*$rel_n1['req_qty_one'];
                        $usable = $usable * $o_qty;
                    }
                    $chkp = $usable / $o_qty;

                    /*
                    Code By Umair: 09/12/2020
                    Commnet: number_format function is commneted to solve the real value
                    */
                    //$machine_make[]=number_format($chkp,4,".","");
                    $machine_make[] = $chkp;

                    $min_machine = min($machine_make);
                    //var_dump($min_machine);
                    $min_machine111 = $min_machine;
                    //var_dump($min_machine111);
                    $pending_qty = $rel['pen_qty'];

                    if ($min_machine111 > $pending_qty)
                    {
                        $min_machine111 = $pending_qty;
                    }
                    //var_dump($min_machine111);
                    
                }

                /* $q1="select itm.product_id,itm.product_type,itm.product_purchase_rate,itm.product_name,itm.product_min_stock,itm.product_opening,bt.product_act_qty,u.unit_name,IFNULL(qcqty,0),bt.product_base_unit,(bt.product_act_qty/bt.product_base_qty) as bom_qty from tbl_bomtrn as bt
                left join product_mst as itm on itm.product_id=bt.product_id
                left join unit_mst as u on u.unitid=bt.product_base_unit
                left join (select sum(qc.qc_accepted) as qcqty,qc.qc_product
                from tbl_qc_trn as qc where qc_status=0 group by qc.qc_product) as qcd on qcd.qc_product=bt.product_id
                where bt.bom_trn_status=0 ".$where."";
                $q2=$dbcon->query($q1);
                $machine_make=array();
                $aao="";
                while($row=brp_mysqli_fetch_array($q2))
                {
                //$required_qty=$rel['p_qty']*$row['product_act_qty'];
                $required_qty=$rel['p_qty']*$row['bom_qty'];
                
                $ri1="select rp_id from tbl_request_product as ap
                where ap.perent_id=".$rel['p_ref_id']." and rp_pid=".$row['product_id'];
                $ri11=$dbcon->query($ri1);
                $r221=brp_mysqli_fetch_array($ri11);
                
                $cur_stock=reserve_stock($dbcon,$row['product_id'],$row['product_base_unit'],"",$r221['rp_id']);
                //echo $cur_stock;
                $pp=$row['product_id'];
                
                if($cur_stock<0){
                //$aao=$aao."+".$cur_stock."(".$row['product_id'].")";
                $cur_stock=0;
                }
                
                $total=$cur_stock;
                if($total>$required_qty)
                {
                $usable=$required_qty;
                
                }
                else
                {
                //$usable=round(($total/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
                $usable=round(($total/$row['bom_qty']),0,PHP_ROUND_HALF_DOWN);
                //$usable=$usable*$row['product_act_qty'];
                $usable=$usable*$row['bom_qty'];
                //$usable22=$usable22+$usable;
                }
                
                //$machine_make[]=round(($usable/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
                $machine_make[]=round(($usable/$row['bom_qty']),0,PHP_ROUND_HALF_DOWN);
                }
                
                $min_machine=min($machine_make);
                
                $min_machine111=$min_machine;
                $pending_qty=$rel['pen_qty'];
                if($min_machine111>$pending_qty){
                $min_machine111=$pending_qty;
                }
                */
            }
            else
            {
                /* $q22="select * from tbl_allocate_process as bt
                where bt.p_id=".$rel['previous_process_id'];
                $q23=$dbcon->query($q22);
                $row12=brp_mysqli_fetch_array($q23);
                
                $min_machine=$row12['process_stock']-$row12['process_used_stock'];
                $pending_qty=$min_machine; */

                $q22 = "select * from tbl_allocate_process as bt 
                where bt.p_id=" . $rel['previous_process_id'];
                $q23 = $dbcon->query($q22);
                $row12 = brp_mysqli_fetch_array($q23);

                $min_machine = $row12['process_stock'] - $row12['process_used_stock'];
                $min_machine111 = $min_machine;
                //$pending_qty11=$min_machine;
                $pending_qty = $rel['pen_qty'];
                if ($min_machine111 > $pending_qty)
                {
                    $min_machine111 = $pending_qty;
                }
            }
            //$sho=$sho."n".$min_machine."-".$pp;
            //$sho=$sho."nnnnn".$q1;
            $p_qty += $min_machine111;
        }
        return round($p_qty, 2);
        //return $dddd;
        //return $sho;
        
    }
    else
    {
        return round($p_qty, 2);
    }

    //$total=$rel['sqty']-$rel['stqty'];
    //return $total;
    
}
function count_working_process_qty_24_08_20($dbcon, $id, $type)
{
    $is_available = count_process_qty($dbcon, $id, $type);
    $p_qty = 0;
    if ($is_available > 0)
    {

        $q = $dbcon->query("select ap.*,p.product_type,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 
           left join product_mst as p on p.product_id=ap.p_product_id 
           left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
           left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id
           where ap.p_status IN (0,1) and process_id=" . $id . " and pr_process_type='$type'");

        //$dddd="select ap.*,p.product_type from tbl_allocate_process as ap left join product_mst as p on p.product_id=ap.p_product_id where ap.p_status IN (0,1) and pr_process_type='$type'";
        while ($rel = brp_mysqli_fetch_array($q))
        {
            $pid = $rel['p_product_id'];

            $where = '';
            //$pp=$rel['product_type'];
            if ($rel['product_type'] == 0)
            {
                $where .= " and parent_id = '0' and sale_product_id='$pid'";
            }
            else
            {
                $where .= " and parent_id = (select bom_trn_id from tbl_bomtrn where product_id='$pid' order by bom_trn_id desc limit 0,1)";
            }
            if ($rel['p_status'] == 1)
            {
                //$unused=$rel['p_qty']-$rel['start_qty'];
                //$min_machine=$rel['pen_qty']-$unused;
                //$pending_qty=$rel['pen_qty']-$unused;
                //$min_machine=$rel['strtt_qty']-$rel['end_qty'];
                //$pending_qty=$rel['strtt_qty']-$rel['end_qty'];
                $min_machine = $rel['start_qty'];
                $min_machine111 = $rel['strtt_qty'] - $rel['end_qty'];
                $pending_qty = $rel['pen_qty'];
                if ($min_machine111 > $pending_qty)
                {
                    $min_machine111 = $pending_qty;
                }
            }
            else if ($rel['previous_process_id'] == 0)
            {
                $q1 = "select itm.product_id,itm.product_type,itm.product_purchase_rate,itm.product_name,itm.product_min_stock,itm.product_opening,bt.product_act_qty,u.unit_name,IFNULL(qcqty,0),bt.product_base_unit,(bt.product_act_qty/bt.product_base_qty) as bom_qty from tbl_bomtrn as bt 
                left join product_mst as itm on itm.product_id=bt.product_id 
                left join unit_mst as u on u.unitid=bt.product_base_unit 
                left join (select sum(qc.qc_accepted) as qcqty,qc.qc_product 
                from tbl_qc_trn as qc where qc_status=0 group by qc.qc_product) as qcd on qcd.qc_product=bt.product_id 
                where bt.bom_trn_status=0 " . $where . "";
                $q2 = $dbcon->query($q1);
                $machine_make = array();
                $aao = "";
                while ($row = mysqli_fetch_array($q2))
                {

                    //$required_qty=$rel['p_qty']*$row['product_act_qty'];
                    $required_qty = $rel['p_qty'] * $row['bom_qty'];

                    //$op_stock=$row['product_opening'];
                    //$total=$op_stock+$row['qcqty'];
                    $cur_stock = get_current_stock_new($dbcon, $row['product_id'], $row['product_base_unit']);

                    /* $ri="select * from tbl_allocate_process as ap
                    where ap.p_status IN (0,1) and pr_process_type='$type'";
                    $ri1=$dbcon->query($ri);
                    $r22=brp_mysqli_fetch_array($ri1);
                    */

                    //$cur_stock=reserve_stock($dbcon,$row['product_id'],$row['product_base_unit']);
                    $pp = $row['product_id'];

                    if ($cur_stock < 0)
                    {
                        //$aao=$aao."+".$cur_stock."(".$row['product_id'].")";
                        $cur_stock = 0;
                    }

                    $total = $cur_stock;
                    if ($total > $required_qty)
                    {
                        $usable = $required_qty;
                    }
                    else
                    {
                        //$usable=round(($total/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
                        $usable = round(($total / $row['bom_qty']) , 0, PHP_ROUND_HALF_DOWN);
                        //$usable=$usable*$row['product_act_qty'];
                        $usable = $usable * $row['bom_qty'];
                        //$usable22=$usable22+$usable;
                        
                    }

                    //$machine_make[]=round(($usable/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
                    $machine_make[] = round(($usable / $row['bom_qty']) , 0, PHP_ROUND_HALF_DOWN);
                }

                $min_machine = min($machine_make);

                $min_machine111 = $min_machine;
                $pending_qty = $rel['pen_qty'];
                if ($min_machine111 > $pending_qty)
                {
                    $min_machine111 = $pending_qty;
                }
            }
            else
            {
                /* $q22="select * from tbl_allocate_process as bt
                where bt.p_id=".$rel['previous_process_id'];
                $q23=$dbcon->query($q22);
                $row12=brp_mysqli_fetch_array($q23);
                
                $min_machine=$row12['process_stock']-$row12['process_used_stock'];
                $pending_qty=$min_machine; */

                $q22 = "select * from tbl_allocate_process as bt 
                where bt.p_id=" . $rel['previous_process_id'];
                $q23 = $dbcon->query($q22);
                $row12 = brp_mysqli_fetch_array($q23);

                $min_machine = $row12['process_stock'] - $row12['process_used_stock'];
                $min_machine111 = $min_machine;
                //$pending_qty11=$min_machine;
                $pending_qty = $rel['pen_qty'];
                if ($min_machine111 > $pending_qty)
                {
                    $min_machine111 = $pending_qty;
                }
            }
            //$sho=$sho."n".$min_machine."-".$pp;
            //$sho=$sho."nnnnn".$q1;
            $p_qty += $min_machine111;
        }

        return $p_qty;
        //return $dddd;
        //return $sho;
        
    }
    else
    {
        return $p_qty;
    }

    //$total=$rel['sqty']-$rel['stqty'];
    //return $total;
    
}
function count_working_process_qty_old_13052020($dbcon, $id, $type)
{
    $is_available = count_process_qty($dbcon, $id, $type);
    $p_qty = 0;
    if ($is_available > 0)
    {

        $q = $dbcon->query("select ap.*,p.product_type,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 
           left join product_mst as p on p.product_id=ap.p_product_id 
           left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
           left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id
           where ap.p_status IN (0,1) and process_id=" . $id . " and pr_process_type='$type'");

        //$dddd="select ap.*,p.product_type from tbl_allocate_process as ap left join product_mst as p on p.product_id=ap.p_product_id where ap.p_status IN (0,1) and pr_process_type='$type'";
        while ($rel = brp_mysqli_fetch_array($q))
        {
            $pid = $rel['p_product_id'];

            $where = '';
            //$pp=$rel['product_type'];
            if ($rel['product_type'] == 0)
            {
                $where .= " and parent_id = '0' and sale_product_id='$pid'";
            }
            else
            {
                $where .= " and parent_id = (select bom_trn_id from tbl_bomtrn where product_id='$pid' order by bom_trn_id desc limit 0,1)";
            }
            if ($rel['p_status'] == 1)
            {
                //$unused=$rel['p_qty']-$rel['start_qty'];
                //$min_machine=$rel['pen_qty']-$unused;
                //$pending_qty=$rel['pen_qty']-$unused;
                $min_machine = $rel['strtt_qty'] - $rel['end_qty'];
                $pending_qty = $rel['strtt_qty'] - $rel['end_qty'];
            }
            else if ($rel['previous_process_id'] == 0)
            {
                $q1 = "select itm.product_id,itm.product_type,itm.product_purchase_rate,itm.product_name,itm.product_min_stock,itm.product_opening,bt.product_act_qty,u.unit_name,IFNULL(qcqty,0),bt.product_base_unit from tbl_bomtrn as bt 
                left join product_mst as itm on itm.product_id=bt.product_id 
                left join unit_mst as u on u.unitid=bt.product_base_unit 
                left join (select sum(qc.qc_accepted) as qcqty,qc.qc_product 
                from tbl_qc_trn as qc where qc_status=0 group by qc.qc_product) as qcd on qcd.qc_product=bt.product_id 
                where bt.bom_trn_status=0 " . $where . "";
                $q2 = $dbcon->query($q1);
                $machine_make = array();
                $aao = "";
                while ($row = mysqli_fetch_array($q2))
                {
                    $required_qty = $rel['p_qty'] * $row['product_act_qty'];

                    //$op_stock=$row['product_opening'];
                    //$total=$op_stock+$row['qcqty'];
                    $cur_stock = get_current_stock_new($dbcon, $row['product_id'], $row['product_base_unit']);
                    $pp = $row['product_id'];

                    if ($cur_stock < 0)
                    {
                        //$aao=$aao."+".$cur_stock."(".$row['product_id'].")";
                        $cur_stock = 0;
                    }

                    $total = $cur_stock;
                    if ($total > $required_qty)
                    {
                        $usable = $required_qty;
                    }
                    else
                    {
                        $usable = round(($total / $row['product_act_qty']) , 0, PHP_ROUND_HALF_DOWN);
                        $usable = $usable * $row['product_act_qty'];
                        //$usable22=$usable22+$usable;
                        
                    }

                    $machine_make[] = round(($usable / $row['product_act_qty']) , 0, PHP_ROUND_HALF_DOWN);
                }

                $min_machine = min($machine_make);
            }
            else
            {
                $q22 = "select * from tbl_allocate_process as bt 
                where bt.p_id=" . $rel['previous_process_id'];
                $q23 = $dbcon->query($q22);
                $row12 = brp_mysqli_fetch_array($q23);

                $min_machine = $row12['process_stock'] - $row12['process_used_stock'];
                $pending_qty = $min_machine;
            }
            //$sho=$sho."n".$min_machine."-".$pp;
            //$sho=$sho."nnnnn".$q1;
            $p_qty += $min_machine;
        }

        return $p_qty;
        //return $dddd;
        //return $sho;
        
    }
    else
    {
        return $p_qty;
    }

    //$total=$rel['sqty']-$rel['stqty'];
    //return $total;
    
}

function count_re_process_qty($dbcon, $id, $type)
{
    /* $query="select (select COALESCE(sum(pen_qty),0) as sqty1 from tbl_allocate_re_process where  process_id='$id' and pr_process_type='$type')  as sqty,( select COALESCE(sum(pt_qty),0) as stqty1 from tbl_allocate_re_process_trn apt left join tbl_allocate_re_process as ap on ap.p_id=apt.pt_alloc_id where pt_process_id='$id' and ap.pr_process_type='$type') as stqty"; */

    $branch_whre = "";
    if (!empty($_SESSION['branch_id']))
    {
        $branch_whre = " and branch_id=" . $_SESSION['branch_id'];
    }

    $query = "select COALESCE(sum(pen_qty),0) as sqty1 from tbl_allocate_re_process where process_id='$id' " . $branch_whre . " and company_id=" . $_SESSION['company_id'] . " and pr_process_type='$type'";

    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);

    //$total=$rel['sqty']-$rel['stqty'];
    $total = $rel['sqty'];

    if ($total == 0)
    {
        return 0;
    }
    else
    {
        return $total;
    }
    //return $id;
    
}

function count_opening_process_qty($dbcon, $id, $type)
{
    //select sum(process_opening) as opening from tbl_product_process where process_id='$id' and process_type='$type'
    $query = "select (select COALESCE(sum(process_opening),0) as sqty1 from tbl_product_process where status = 0 and process_id='$id' and process_type='$type')  as sqty,(select COALESCE(sum(pt_qty),0) as stqty1 from tbl_allocate_process_trn apt left join tbl_allocate_process as ap on ap.p_id=apt.pt_alloc_id where pt_process_id='$id' and ap.pr_process_type='$type') as stqty";
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);

    $total = $rel['sqty'] - $rel['stqty'];

    if ($total == 0)
    {
        return 0;
    }
    else if ($total < 0)
    {
        return 0;
    }
    else
    {
        return $total;
    }
    //return $query;
    
}

function get_process_name($dbcon, $id)
{
    $query = "select process_name from process_mst where  process_id='$id'";
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
    return $rel['process_name'];
}

function get_next_process($dbcon, $process_id, $product_id, $rp_id, $current_process_priority)
{
    /* $query="select * from tbl_product_process where status = 0 and product_id='$product_id' and process_id='$process_id'";
    $rs_cust=$dbcon->query($query);
    $rel=brp_mysqli_fetch_array($rs_cust);
    $cur_priority=$rel['process_priority'];
    $next_priority=$cur_priority+1;
    
    $query1="select * from tbl_product_process where status = 0 and product_id='$product_id' and process_priority='$next_priority'";
    $rs_cust1=$dbcon->query($query1);
    $rel1=brp_mysqli_fetch_array($rs_cust1);
    $count=brp_mysqli_num_rows($rs_cust1);
    */

    $query = "select * from tbl_wororder_product_process where product_id='$product_id' and process_id='$process_id' and rp_id=" . $rp_id . " and process_priority=" . $current_process_priority;

    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
    $cur_priority = $rel['process_priority'];
    $next_priority = $cur_priority + 1;

    $query1 = "select * from tbl_wororder_product_process where  product_id='$product_id' and process_priority='$next_priority' and rp_id=" . $rp_id;
    $rs_cust1 = $dbcon->query($query1);
    $rel1 = brp_mysqli_fetch_array($rs_cust1);
    $count = brp_mysqli_num_rows($rs_cust1);

    //  echo $rel1['process_id'];
    if ($count > 0)
    {
        $row['process_id'] = $rel1['process_id'];
        $row['process_type'] = $rel1['process_type'];
        $row['process_priority'] = $next_priority;

        return json_encode($row);
    }
    else
    {
        $row['process_id'] = 0;
        $row['process_type'] = 0;
        $row['process_priority'] = 0;
        return json_encode($row);
    }
}

function get_next_reprocess($dbcon, $process_id, $product_id, $qc_id, $current_process_priority)
{

    $query = "select * from tbl_wororder_product_reprocess where product_id='$product_id' and process_id='$process_id' and qc_id=" . $qc_id . " and process_priority=" . $current_process_priority;

    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
    $cur_priority = $rel['process_priority'];
    $next_priority = $cur_priority + 1;

    $query1 = "select * from tbl_wororder_product_reprocess where  product_id='$product_id' and process_priority='$next_priority' and qc_id=" . $qc_id;
    $rs_cust1 = $dbcon->query($query1);
    $rel1 = brp_mysqli_fetch_array($rs_cust1);
    $count = brp_mysqli_num_rows($rs_cust1);

    //  echo $rel1['process_id'];
    if ($count > 0)
    {
        $row['process_id'] = $rel1['process_id'];
        $row['process_type'] = $rel1['process_type'];
        $row['process_priority'] = $next_priority;

        return json_encode($row);
    }
    else
    {
        $row['process_id'] = 0;
        $row['process_type'] = 0;
        $row['process_priority'] = 0;
        return json_encode($row);
    }
}

function get_product_specification($dbcon, $id)
{
    $str = '';
    $query = "select * from mst_material_spec where ms_status='0' order by ms_name";
    $rs_product = $dbcon->query($query);
    $str = '<option value="">Choose Material Specification</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product))
    {
        $sel = '';
        if ($rel['ms_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['ms_id'] . '">' . $rel['ms_name'] . '</option>';
    }
    return $str;
}

function get_alloc_id($dbcon, $ref_id, $process_id)
{
    $query = "select p_id from tbl_allocate_process where p_ref_id='$ref_id' and process_id='$process_id'";
    $rel = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($rel);

    return $row['p_id'];
}

function reprocess_get_alloc_id($dbcon, $ref_id, $process_id)
{
    $query = "select p_id from tbl_allocate_re_process where p_ref_id='$ref_id' and process_id='$process_id'";
    $rel = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($rel);

    return $row['p_id'];
}

function get_jobwork_qc_qty($dbcon, $id)
{
    $query = "select j.*,dqty from tbl_jobwork as j left join (select sum(qc_product_qty) as dqty,po_id from tbl_qc_trn group by po_id) as apta on apta.po_id=j.jobwork_id where j.jobwork_id='$id'";
    $rel = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($rel);

    $qty = $row['j_qty'] - $row['dqty'];

    return $qty;
}

function count_min_max($dbcon, $type)
{
    //+IFNULL(qc_total_rejected,0)
    /* $query="SELECT pro.product_id, pro.product_name,pro.product_status,pro.product_min_stock, pro.product_opening, reqqty,pro.product_setting_check,grn_total,qc_total,inv_qty,jobout_qty,process_de,qc_total_rejected,((IFNULL(grn_total,0)+IFNULL(qc_total,0)+IFNULL(add_adjustment_qty,0)+pro.product_opening)-(IFNULL(inv_qty,0)+IFNULL(jobout_qty,0)+IFNULL(remove_adjustment_qty,0)))+IFNULL(reqqty,0) as stock_in,(IFNULL(((IFNULL((select IFNULL(sum(qc.base_stock),0) as base_stock_add from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=".$_SESSION['company_id']."
    group by qc.product_id),0)+IFNULL((select IFNULL(sum(qc.convert_stock),0) as con_stock_add from tbl_stock_trn as qc 
    where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=".$_SESSION['company_id']." 
    group by qc.product_id),0))-(IFNULL((select IFNULL(sum(qc.base_stock),0) as base_stock_minus from tbl_stock_trn as qc 
    where qc.stock_status != 2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=".$_SESSION['company_id']." 
    group by qc.product_id),0)+IFNULL((select IFNULL(sum(qc.convert_stock),0) as con_stock_minus from tbl_stock_trn as qc 
    where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=".$_SESSION['company_id']." 
    group by qc.product_id),0))),0)+IFNULL(reqqty,0)) as stock_in_new from  product_mst as pro  
    
    left join (select sum(gt.product_qty) as grn_total,gt.product_id,g.grn_status,g.product_qc,p.product_setting_check from tbl_grn_trn as gt left join tbl_grn as g on g.grn_id=gt.grn_id left join product_mst as p on p.product_id=gt.product_id where g.grn_status=0 and !FIND_IN_SET('product_qc',p.product_setting_check) and gt.company_id=".$_SESSION['company_id']." group by gt.product_id) as grn on grn.product_id=pro.product_id
    
    left join (select sum(qc.qc_accepted) as qc_total,qc.qc_product,q.qc_status from tbl_qc_trn as qc left join tbl_qc as q on q.qc_id=qc.qc_id where q.qc_status=0 and q.company_id=".$_SESSION['company_id']." group by qc.qc_product) as qc on qc.qc_product=pro.product_id
    
    left join (select sum(intrn.product_qty) as inv_qty,intrn.product_id from tbl_invoicetrn as intrn where intrn.trancation_status=0 and intrn.company_id=".$_SESSION['company_id']." group by intrn.product_id) as invt on invt.product_id=pro.product_id
    
    left join (select sum(jobout.outward_product_qty) as jobout_qty,jobout.raw_product_id from tbl_jobworktrn as jobout where jobout.jobworktrn_status=0 and jobwork_id!=0 and jobout.company_id=".$_SESSION['company_id']."  group by jobout.raw_product_id) as jout on jout.raw_product_id=pro.product_id
    
    left join (select GROUP_CONCAT(prm.process_name) as process_de,pp.product_id from tbl_product_process as pp left join process_mst as prm on prm.process_id=pp.process_id group by pp.product_id) as pr on pr.product_id=pro.product_id
    
    left join (select sum(qc.qc_rejected) as qc_total_rejected,qc.qc_product,q.qc_status from tbl_qc_trn as qc left join tbl_qc as q on q.qc_id=qc.qc_id where q.qc_status!=2 and q.company_id=".$_SESSION['company_id']." group by qc.qc_product) as qc1 on qc1.qc_product=pro.product_id
    
    left join (select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0 group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id
    
    left join (select sum(jobout.add_adjustment_qty) as add_adjustment_qty,jobout.product_id from tbl_stcok_adjustment_trn as jobout
    left join tbl_stcok_adjustment as saj on saj.stcok_adjustment_id=jobout.stcok_adjustment_id
    where jobout.stcok_adjustment_trn_status=0 and saj.company_id=".$_SESSION['company_id']."  group by jobout.product_id) as aaje on aaje.product_id=pro.product_id
    
    left join (select sum(jobout.remove_adjustment_qty) as remove_adjustment_qty,jobout.product_id from tbl_stcok_adjustment_trn as jobout
    left join tbl_stcok_adjustment as saj on saj.stcok_adjustment_id=jobout.stcok_adjustment_id
    where jobout.stcok_adjustment_trn_status=0 and saj.company_id=".$_SESSION['company_id']."  group by jobout.product_id) as aaje1 on aaje1.product_id=pro.product_id
    
    where pro.product_status=0 and pro.product_min_stock!=0 and pro.company_id=".$_SESSION['company_id']." group by pro.product_id HAVING stock_in_new < pro.product_min_stock order by product_name"; */

    /*
    $query="SELECT pro.product_id,pro.product_name,pro.product_min_stock,pro.product_opening,reqqty from product_mst as pro
    
    left join (select sum(req.rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0  group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id
    
    where pro.product_status=0 and  pro.product_min_stock >= IFNULL(reqqty,0)  and pro.product_min_stock!=0  group by pro.product_id order by product_name"; */

    $query = "SELECT pro.product_id,pro.product_base_unit,pro.product_name,tc.cat_name,pro.product_status,pro.product_min_stock,reqqty, base_stock_add,base_stock_minus,con_stock_add,con_stock_minus,(((IFNULL(base_stock_add,0)+IFNULL(con_stock_add,0))-(IFNULL(base_stock_minus,0)+IFNULL(con_stock_minus,0)))+IFNULL(reqqty,0)) as stock
    from product_mst as pro 
    left join tbl_category as tc on pro.product_category=tc.cat_id

    left join (select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0  group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id

    left join (select sum(qc.base_stock) as base_stock_add,qc.product_id,qc.base_unit from tbl_stock_trn as qc 
      where qc.stock_status != 2 and stock_flage=1 and qc.company_id=" . $_SESSION['company_id'] . " 
      group by qc.product_id) as qc4 on qc4.product_id=pro.product_id and qc4.base_unit=pro.product_base_unit

      left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id,qc.base_unit from tbl_stock_trn as qc 
      where qc.stock_status != 2 and stock_flage=2 and qc.company_id=" . $_SESSION['company_id'] . " 
      group by qc.product_id) as qc1 on qc1.product_id=pro.product_id and qc1.base_unit=pro.product_base_unit

      left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id,qc.convert_unit from tbl_stock_trn as qc 
      where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.company_id=" . $_SESSION['company_id'] . " 
      group by qc.product_id) as qc2 on qc2.product_id=pro.product_id and qc2.convert_unit=pro.product_base_unit

      left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id,qc.convert_unit from tbl_stock_trn as qc 
      where qc.stock_status != 2 and stock_flage=2 and qc.base_unit!=qc.convert_unit and qc.company_id=" . $_SESSION['company_id'] . " 
      group by qc.product_id) as qc3 on qc3.product_id=pro.product_id and qc3.convert_unit=pro.product_base_unit

      where pro.product_status=0 and pro.product_min_stock!=0 and pro.company_id=" . $_SESSION['company_id'] . " HAVING stock < pro.product_min_stock";

      $rs = $dbcon->query($query);

      $count = brp_mysqli_num_rows($rs);

      return $count;
    //return $query;

  }
  function count_reject_procuct_req($dbcon)
  {
    //$query="select count(qctrn_id) as qty from tbl_qc_trn where qc_rejected!=0 and qc_rejected_used<qc_rejected and qc_status=0";
    /*
    Code By Umair: Below code is written by umair
    */
    $query = "select count(qc_process_trn_id) as qty from tbl_qc_process_trn as rp
    where rp.qc_process_status=0 and rp.reject_qty>0 and CAST(reject_qty as DECIMAL(50,2)) > CAST(reject_request_qty as DECIMAL(50,2))";
    $rs = $dbcon->query($query);

    $row = brp_mysqli_num_rows($rs);
    if ($row > 0)
    {
        $data = brp_mysqli_fetch_array($rs);
        $count = $data['qty'];
    }
    else
    {
        $count = 0;
    }

    return $count;
}

function get_other_po_qty($dbcon, $product_id, $po_id)
{
    $query = "select COALESCE(sum(product_qty),0) as qty from  tbl_purchasetrntemp where product_id='$product_id' and purchaseorder_id!='$po_id' and po_trn_req_status='0'";

    $rs = $dbcon->query($query);

    $row = brp_mysqli_fetch_array($rs);

    return $row['qty'];
}

function members_Tree($dbcon, $parentKey)
{

    $sql = 'SELECT g_id, g_name from tbl_group WHERE g_pid="' . $parentKey . '" order by g_name';

    $result = $dbcon->query($sql);

    while ($value = mysqli_fetch_assoc($result))
    {
        $id = $value['g_id'];
        $row1[$id]['id'] = $value['g_id'];
        $row1[$id]['name'] = $value['g_name'];
        $row1[$id]['text'] = $value['g_name'];
        $row1[$id]['nodes'] = array_values(members_Tree($dbcon, $value['g_id']));
    }

    return $row1;
}

function getenvelope($dbcon, $id)
{
    $query = "select * from evelope_design where env_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_cust = $dbcon->query($query);
    echo '<option value="">Select Envelope</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {
        $sel = '';
        if ($rel['envelope_design_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['envelope_design_id'] . '">' . $rel['env_name'] . '</option>';
    }
}

function get_warehouse_qty($dbcon, $pro_id, $req_qty, $eid)
{

    $cnt = 1;
    $str = '';
    $selb = $dbcon->query("select gd.*,gps.product_stock,gps.priority from mst_godown as gd left join tbl_branch_product_stock as gps on gd.gd_id=gps.branch_id where gd.g_status=0 and gps.product_id='$pro_id'");

    while ($rb = brp_mysqli_fetch_array($selb))
    {
        if ($req_qty >= $rb['product_stock'])
        {
            $deducted = $rb['product_stock'];
            $req_qty = $req_qty - $rb['product_stock'];
        }
        else
        {
            $deducted = $req_qty;
            $req_qty = 0;
        }

        $str .= '
        ' . $rb['gd_name'] . ':
        ' . $deducted . '<input type="hidden" name="deducted_stock[]" id="" value="' . $deducted . '"  />
        <input type="hidden" name="deducted_gd_id[]" id="" value="' . $rb['gd_id'] . '"  />
        <input type="hidden" name="product_id[]" id="" value="' . $pro_id . '"  />
        <input type="hidden" name="gst_eid[]" id="" value="' . $eid . '"  />
        <br>';

        $cnt++;
    }

    return $str;
}

function get_all_godown($dbcon, $eid, $blnk)
{
    $query = "select gd_id,gd_name from mst_godown where g_status=0 order by gd_id desc";
    $rs_dispatch = $dbcon->query($query);
    if ($blnk != "1")
    {
        $str = '<option value="">Choose Godown</option>';
    }
    while ($rel = mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        if ($rel['gd_id'] == $eid)
        {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $rel['gd_id'] . '">' . $rel['gd_name'] . '</option>';
    }
    return $str;
}

function get_all_parent_godown($dbcon, $eid, $blnk)
{
    $query = "select gd_id,gd_name from mst_godown where g_status=0 and p_gd_id=0 order by gd_id desc";
    $rs_dispatch = $dbcon->query($query);
    if ($blnk != "1") {
        $str = '<option value="">Choose Godown</option>';
    }
    while ($rel = mysqli_fetch_assoc($rs_dispatch)) {
        // $sel = '';
        if ($rel['gd_id'] == $eid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $rel['gd_id'] . '">' . $rel['gd_name'] . '</option>';
    }
    return $str;
}



function get_current_stock($dbcon, $pro_id)
{

    $query = 'SELECT pro.product_id,pro.product_opening,pro.product_setting_check,grn_total,qc_total,inv_qty,sup_qty,jobout_qty,qc_total_rejected,remove_adjustment_qty,add_adjustment_qty  FROM `product_mst` as pro 

    left join (select sum(gt.product_qty) as grn_total,gt.product_id,g.grn_status,g.product_qc,p.product_setting_check from tbl_grn_trn as gt left join tbl_grn as g on g.grn_id=gt.grn_id left join product_mst as p on p.product_id=gt.product_id where g.grn_status=0 and !FIND_IN_SET("product_qc",p.product_setting_check) and gt.company_id=' . $_SESSION['company_id'] . ' group by gt.product_id) as grn on grn.product_id=pro.product_id

      left join (select sum(qc.qc_accepted) as qc_total,qc.qc_product,q.qc_status from tbl_qc_trn as qc left join tbl_qc as q on q.qc_id=qc.qc_id where q.qc_status!=2 and q.company_id=' . $_SESSION['company_id'] . ' group by qc.qc_product) as qc on qc.qc_product=pro.product_id

      left join (select sum(qc.qc_rejected) as qc_total_rejected,qc.qc_product,q.qc_status from tbl_qc_trn as qc left join tbl_qc as q on q.qc_id=qc.qc_id where q.qc_status!=2 and q.company_id=' . $_SESSION['company_id'] . ' group by qc.qc_product) as qc1 on qc1.qc_product=pro.product_id

      left join (select sum(intrn.product_qty) as inv_qty,intrn.product_id from tbl_invoicetrn as intrn where intrn.trancation_status=0 and intrn.company_id=' . $_SESSION['company_id'] . ' group by intrn.product_id) as invt on invt.product_id=pro.product_id

      left join (select sum(suptrn.product_qty) as sup_qty,suptrn.product_id from tbl_bill_of_supplytrn as suptrn where suptrn.bill_of_supply_trn_status=0 and suptrn.company_id=' . $_SESSION['company_id'] . ' group by suptrn.product_id) as supt on supt.product_id=pro.product_id

      left join (select sum(jobout.outward_product_qty) as jobout_qty,jobout.raw_product_id from tbl_jobworktrn as jobout where jobout.jobworktrn_status=0 and jobwork_id!=0 and jobout.company_id=' . $_SESSION['company_id'] . '  group by jobout.raw_product_id) as jout on jout.raw_product_id=pro.product_id

      left join (select sum(jobout.add_adjustment_qty) as add_adjustment_qty,jobout.product_id from tbl_stcok_adjustment_trn as jobout 
      left join tbl_stcok_adjustment as saj on saj.stcok_adjustment_id=jobout.stcok_adjustment_id
      where jobout.stcok_adjustment_trn_status=0 and saj.company_id=' . $_SESSION['company_id'] . '  group by jobout.product_id) as aaje on aaje.product_id=pro.product_id

      left join (select sum(jobout.remove_adjustment_qty) as remove_adjustment_qty,jobout.product_id from tbl_stcok_adjustment_trn as jobout 
      left join tbl_stcok_adjustment as saj on saj.stcok_adjustment_id=jobout.stcok_adjustment_id
      where jobout.stcok_adjustment_trn_status=0 and saj.company_id=' . $_SESSION['company_id'] . '  group by jobout.product_id) as aaje1 on aaje1.product_id=pro.product_id

      where pro.product_type in(0,1,2,3,4,5) and  pro.product_id=' . $pro_id;
      $rows = brp_mysqli_fetch_assoc($dbcon->query($query));
    //+$rows['qc_total_rejected']
      $stock = ($rows['product_opening'] + $rows['grn_total'] + $rows['qc_total'] + $rows['add_adjustment_qty']) - ($rows['inv_qty'] + $rows['sup_qty'] + $rows['jobout_qty'] + $rows['remove_adjustment_qty']);
    //$stock=($rows['product_stock']+$rows['pur_qty']+$rows['jobin_qty']+$rel['strnt_qty'])-($rows['jobout_qty']+$rows['inv_qty']+$rel['strn_qty']+$rel['mwaste_qty']);
      return floatval($stock);
  }

  function get_process_stock_detail($dbcon, $pr_id, $product_base_unit)
  {
    $q = "select pp.*,pro.product_name,pr.process_name,qc_total,qc_total_rejected from tbl_product_process as pp 

    left join product_mst as pro on pro.product_id=pp.product_id left join process_mst as pr on pr.process_id=pp.process_id 

    left join (select sum(qc.process_stock) as qc_total,process_id from tbl_allocate_process as qc 
    where p_product_id=" . $pr_id . " and company_id=" . $_SESSION['company_id'] . " group by process_id) as qc on qc.process_id=pp.process_id

    left join (select sum(qc.process_used_stock) as qc_total_rejected,process_id from tbl_allocate_process as qc 
    where p_product_id=" . $pr_id . " and company_id=" . $_SESSION['company_id'] . " group by process_id) as qc1 on qc1.process_id=pp.process_id


    where pp.status = 0 and pp.product_id='$pr_id'
    ";

    $rel = $dbcon->query($q);
    //$str=array();
    $str = '';
    $str = '<table class="table ">';
    while ($row = brp_mysqli_fetch_array($rel))
    {
        $process_id = $row['process_id'];
        //$stock = ($row['process_opening']+$row['qc_total'])-$row['qc_total_rejected'];
        $stock = production_process_reseve_stock($dbcon, $product_base_unit, $branch_id, $p_id, $pr_id, $process_id, $process_reserve_id, $process_stock_id);

        $pstock = get_current_process_stock_new($dbcon, $pr_id, $process_id, $product_base_unit, $branch_id);

        $str .= '<tr>
        <td>' . $row['process_name'] . '</td>
        <td>' . ($pstock - $stock) . '</td>
        </tr>';
        //$str[]=$row['process_name'].' - '.$row['process_opening'].'<br/>';
        
    }

    return $str;
    //return $q;
    
}
function get_godown_stock($dbcon, $product_id, $unit_id)
{
    $q = "select gd_name,gd_id from mst_godown as gd 
    where g_status=0 order by gd_id";

    $rel = $dbcon->query($q);
    //$str=array();
    $str1 = '';
    $str1 .= '<table class="table ">';
    while ($row = brp_mysqli_fetch_array($rel))
    {
        $stock = get_current_godown_stock_new($dbcon, $product_id, $unit_id, $row['gd_id']);
        if ($stock > 0)
        {
            $str1 .= '<tr>
            <td>' . $row['gd_name'] . '</td>
            <td>' . $stock . '</td>
            </tr>';
        }
    }
    $str1 .= '</table>';

    return $str1;
}

//Amish Soni 04-09-2020
/*
 * Custom function to compress image size and
 * upload to the server using PHP
*/
function compressImage($source, $destination, $quality)
{
    // Get image info
    $imgInfo = getimagesize($source);
    $mime = $imgInfo['mime'];

    // Create a new image from file
    switch ($mime)
    {
        case 'image/jpeg':
        $image = imagecreatefromjpeg($source);
        break;
        case 'image/png':
        $image = imagecreatefrompng($source);
        break;
        case 'image/gif':
        $image = imagecreatefromgif($source);
        break;
        default:
        $image = imagecreatefromjpeg($source);
    }

    // Save image
    imagejpeg($image, $destination, $quality);

    // // Return compressed image
    // return $destination;
    
}

//Amish Soni 11-09-2020
function getWeekendDates($date, $date2)
{

    $curdate = date('d-M-Y');
    if (!$date)
    {
        $date = date('01-M-yy');
    }

    if (!$date2)
    {
        $date2 = date("t-M-Y", strtotime($curdate));
    }

    $period = new DatePeriod(new DateTime($date) , new DateInterval('P1D') , new DateTime($date2));

    $weekends = [];
    foreach ($period as $key => $value)
    {
        if ($value->format('N') >= 7)
        {
            $weekends[] = $value->format('d-m-Y');
        }
    }

    return $weekends;
}

/*Code By Umair: Get Item Price From Purchase Crad Transaction Table based on the Vendor Selection*/
function getItemPriceByVendorId($dbcon, $vender_id, $product_id)
{
    //$query="select * from tbl_purchasecardtrn where purchasecardtrn_status=0 AND vendor_id='".$vender_id."' AND product_id='".$product_id."' AND affected_date <= '".date('Y-m-d')."' order by purchasecardtrn_id desc limit 1" ;
    // AND `tpt`.`purchase_type`='0'
    $query = "select tpt.*, `u`.`user_name` from tbl_purchasecardtrn as tpt left join users as u ON `tpt`.`user_id`=`u`.`user_id` where `tpt`.`purchasecardtrn_status`=0 AND `tpt`.`vendor_id`='" . $vender_id . "' AND `tpt`.`product_id`='" . $product_id . "'  AND `tpt`.`company_id`='" . $_SESSION['company_id'] . "' AND `tpt`.`affected_date` <= '" . date('Y-m-d') . "' order by `tpt`.`purchasecardtrn_id` desc limit 1";

    $result = $dbcon->query($query);
    $row = mysqli_fetch_assoc($result);
    return $row;
}

/*Code By Umair: Get Item Price From Purchase Crad Transaction Table based on the Item Selection*/
function getItemPriceByProductId($dbcon, $product_id, $vender_id)
{
    //$query="select * from tbl_purchasecardtrn where purchasecardtrn_status=0 AND vendor_id='".$vender_id."' AND product_id='".$product_id."' AND affected_date <= '".date('Y-m-d')."' order by purchasecardtrn_id desc limit 1" ;
    // AND `tpt`.`purchase_type`='1'
    $query = "select tpt.*, `u`.`user_name` from tbl_purchasecardtrn as tpt left join users as u ON `tpt`.`user_id`=`u`.`user_id` where `tpt`.`purchasecardtrn_status`=0 AND `tpt`.`vendor_id`='" . $vender_id . "' AND `tpt`.`product_id`='" . $product_id . "'  AND `tpt`.`company_id`='" . $_SESSION['company_id'] . "' AND `tpt`.`affected_date` <= '" . date('Y-m-d') . "' order by `tpt`.`purchasecardtrn_id` desc limit 1";
    $result = $dbcon->query($query);
    $row = mysqli_fetch_assoc($result);
    return $row;
}

/*Code By Umair: Get Purchase Type For Purchase Bill*/
function purchase_type_main_bill($dbcon, $id)
{
    $array = array(
        '',
        'General',
        'Job Works',
        'Service Order'
    );
    //echo '<option value="">Choose Company</option>';
    foreach ($array as $key => $val)
    {
        if ($key > 0)
        {
            $sel = '';
            if ($key == $id)
            {
                $sel = "selected='selected'";
            }
            echo '<option ' . $sel . ' value="' . $key . '">' . $val . '</option>';
        }
    }
}

/*Code By Umair: Get Purchase Type For Purchase Bill*/
function purchase_type_second_bill($dbcon, $id)
{
    $array = array(
        '',
        'Local Purchase From Manufacturer',
        'Local Purchase From Dealer',
        'Import',
        'Capital Goods Bill',
        'Capital Goods Bill (Import)',
        'Job Work Bill',
        'Service Tax Bill',
        'Other Bill'
    );
    //echo '<option value="">Choose Company</option>';
    foreach ($array as $key => $val)
    {
        if ($key > 0)
        {
            $sel = '';
            if ($key == $id)
            {
                $sel = "selected='selected'";
            }
            echo '<option ' . $sel . ' value="' . $key . '">' . $val . '</option>';
        }
    }
}

/*Code By Umair: Get Purchase Type For Tax Type*/
function tax_type_bill($dbcon, $id)
{
    $array = array(
        '',
        'GST'
    );
    //echo '<option value="">Choose Company</option>';
    foreach ($array as $key => $val)
    {
        if ($key > 0)
        {
            $sel = '';
            if ($key == $id)
            {
                $sel = "selected='selected'";
            }
            echo '<option ' . $sel . ' value="' . $key . '">' . $val . '</option>';
        }
    }
}

/*Code By Umair: Get Purchase Type For ITC*/
function itc_bill($dbcon, $id)
{
    $array = array(
        '',
        'Yes',
        'No'
    );
    //echo '<option value="">Choose Company</option>';
    foreach ($array as $key => $val)
    {
        if ($key > 0)
        {
            $sel = '';
            if ($key == $id)
            {
                $sel = "selected='selected'";
            }
            echo '<option ' . $sel . ' value="' . $key . '">' . $val . '</option>';
        }
    }
}
/*Code By Umair: Get Supply Type For Purchase Bill*/
function supply_type_main_bill($dbcon, $id)
{
    $array = array(
        '',
        'Goods',
        'Services'
    );
    //echo '<option value="">Choose Company</option>';
    foreach ($array as $key => $val)
    {
        if ($key > 0)
        {
            $sel = '';
            if ($key == $id)
            {
                $sel = "selected='selected'";
            }
            echo '<option ' . $sel . ' value="' . $key . '">' . $val . '</option>';
        }
    }
}
/*Code By Umair: Get Supply Second Type For Purchase Bill*/
function supply_type_second_bill($dbcon, $id)
{
    $array = array(
        '',
        'Intrastate Purchase Taxable'
    );
    //echo '<option value="">Choose Company</option>';
    foreach ($array as $key => $val)
    {
        if ($key > 0)
        {
            $sel = '';
            if ($key == $id)
            {
                $sel = "selected='selected'";
            }
            echo '<option ' . $sel . ' value="' . $key . '">' . $val . '</option>';
        }
    }
}
/*Code By Umair: Get Supply Second Type For Purchase Bill*/
function gst_type_bill($dbcon, $id)
{
    $array = array(
        '',
        'Bill Wise',
        'Item Wise'
    );
    //echo '<option value="">Choose Company</option>';
    foreach ($array as $key => $val)
    {
        if ($key > 0)
        {
            $sel = '';
            if ($key == $id)
            {
                $sel = "selected='selected'";
            }
            echo '<option ' . $sel . ' value="' . $key . '">' . $val . '</option>';
        }
    }
}
/*Code By Umair: Get Supply Second Type For Purchase Bill*/
function reverse_type_bill($dbcon, $id)
{
    $array = array(
        '',
        'Yes',
        'No'
    );
    //echo '<option value="">Choose Company</option>';
    foreach ($array as $key => $val)
    {
        if ($key > 0)
        {
            $sel = '';
            if ($key == $id)
            {
                $sel = "selected='selected'";
            }
            echo '<option ' . $sel . ' value="' . $key . '">' . $val . '</option>';
        }
    }
}

/*Code By Umair: Get Expense Name By ID*/
function get_expense_name_by_id($dbcon, $id)
{
    $query = "select l_id,l_name from tbl_ledger where l_id='$id'";

    $row = $dbcon->query($query);

    $rel = brp_mysqli_fetch_assoc($row);

    return $rel['l_name'];
}

/*Code By Umair: 04/11/2020
Comment: Get the Salary account user list
*/
function getsalaryemployee($dbcon, $id, $branch_id = '')
{

    $where = '';
    if ($id)
    {
        $where .= 'and ledger_id!="' . $id . '"';
    }
    $sql = "select ledger_id from tbl_resource where resource_status=0 and branch_id='" . $branch_id . "' $where";
    $resu = $dbcon->query($sql);

    $l_where = '';
    if (brp_mysqli_num_rows($resu) > 0)
    {
        $id_array = [];
        while ($result_data = brp_mysqli_fetch_assoc($resu))
        {
            $id_array[] = $result_data['ledger_id'];
        }

        $id_array = implode(',', $id_array);

        $l_where = 'and l_id not in (' . $id_array . ')';
    }

    $query = "select l_id,l_name from tbl_ledger where l_status=0 and l_form='emp_form' and branch_id='" . $branch_id . "' $l_where";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Employee</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {
        $sel = '';
        if ($rel['l_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['l_id'] . '">' . $rel['l_name'] . '</option>';
    }
    return $str;
}

function reciptdata($dbcon, $poid)
{
    $response = [];
    $query = "SELECT  trn.total_amount as clearpayment,tr.cheque_dtl as chqnumber,tr.ref_date as chqdate,tr.payment_date,tr.payment_remark
    FROM tbl_receipt_trn as trn
    left JOIN tbl_receipt as tr ON trn.receipt_id=tr.receipt_id
    where trn.status!='2' and trn.purchase_id=" . $poid;
    $result1 = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($result1))
    {
        array_push($response, $rel);
    }
    return $response;
}

/*Code By Umair: 04/11/2020
Comment: Get All Resource name
*/
function get_all_resource($dbcon, $id, $where = null, $branch_id = 0)
{
    if ($where)
    {
        $where = 'AND ' . $where;
    }
    $where_db = check_branch('res', $branch_id);
    $where .= " $where_db and res.company_id=" . $_SESSION['company_id'];

    $str = '';
    $query = "select res.* from tbl_resource as res where res.resource_status=0 $where";
    $rs_product = $dbcon->query($query);
    $str .= '<option value="">--Select Resource--</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product))
    {
        $sel = '';
        if ($rel['resource_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['resource_id'] . '">' . $rel['resource_name'] . '</option>';
    }
    return $str;
}

/*Code By Umair: 09/11/2020
Comment: Get Resource Name By ID
*/
function get_resource_info_by_id($dbcon, $where = null)
{
    if ($where)
    {
        $where = 'AND ' . $where;
    }
    $query = "select * from tbl_resource where resource_status=0 AND company_id='" . $_SESSION['company_id'] . "'  $where";
    $rs_product = $dbcon->query($query);
    $rel = brp_mysqli_fetch_assoc($rs_product);
    return $rel;
}

/*Code By Umair: 09/11/2020
Comment: Get Completed Date Of Resource Based On The Working Hours
*/
function get_completed_date_of_resource_based_on_working_hours($startdate, $numberofdays)
{
    //echo date('Y-m-d',strtotime('+1 day'));    //+1 day from today
    $d = new DateTime($startdate);
    $t = $d->getTimestamp();
    // loop for X days
    for ($i = 1;$i < $numberofdays;$i++)
    {
        // add 1 day to timestamp
        $addDay = 86400;
        // get what day it is next day
        $nextDay = date('w', ($t + $addDay));
        // if it's Saturday or Sunday get $i-1
        /*if($nextDay == 0 || $nextDay == 6) {
            $i--;
        }*/
        if ($nextDay == 0)
        {
            $i--;
        }
        // modify timestamp, add 1 day
        $t = $t + $addDay;
    }
    $d->setTimestamp($t);
    return $d->format('Y-m-d');
}

/*Code By Umair: 04/11/2020
Comment: Insert Work Order Resource Allocation In tbl_work_order_resource_allocate table
*/
function work_order_resource_allocate($dbcon, $resource_id = null, $request_id = null, $process_id = null, $product_id = null, $qty = null, $time_per_qty = null, $edit_id = null, $action_type = null, $completed_time = null, $branch_id = 0)
{

    $process_allocate['resource_id'] = $resource_id;
    $process_allocate['request_id'] = $request_id;
    $process_allocate['process_id'] = $process_id;
    $process_allocate['product_id'] = $product_id;
    $process_allocate['qty'] = $qty;
    $process_allocate['time_per_qty'] = $time_per_qty;
    $process_allocate['total_time'] = ($time_per_qty * $qty);
    $process_allocate['completed_time'] = $completed_time;
    $process_allocate['user_id'] = $_SESSION['user_id'];
    $process_allocate['cdate'] = date('Y-m-d H:i:s');
    $process_allocate['company_id'] = $_SESSION['company_id'];
    $process_allocate['resourse_allocation_status'] = 0;

    $return = '';
    if ($action_type == 'add')
    {
        $return = add_record('tbl_work_order_resource_allocate', $process_allocate, $dbcon, $branch_id);
        resource_schedule_assign_at_process_allocate($dbcon, $request_id, $qty, $return);
    }

    return $return;
}

/*Code By Umair: 06/11/2020
Comment: Insert Work Order Resource Transfer. Insert Log In tbl_resource_allocation_transfer table
*/

function work_order_resource_transfer($dbcon, $resource_id_by = null, $resource_id_to = null, $process_id = null, $product_id = null, $qty = null, $resource_transfer_allocate_id = null, $work_order_id = null, $branch_id = 0)
{

    $resource_transfer['resource_id_by'] = $resource_id_by;
    $resource_transfer['resource_id_to'] = $resource_id_to;
    $resource_transfer['resource_transfer_number'] = rand(111111, 999999);
    $resource_transfer['resource_transfer_date'] = date('Y-m-d H:i:s');
    $resource_transfer['resource_transfer_allocate_id'] = $resource_transfer_allocate_id;
    $resource_transfer['product_id'] = $product_id;
    $resource_transfer['process_id'] = $process_id;
    $resource_transfer['work_order_id'] = $work_order_id;
    $resource_transfer['qty'] = $qty;
    $resource_transfer['user_id'] = $_SESSION['user_id'];
    $resource_transfer['cdate'] = date('Y-m-d H:i:s');
    $resource_transfer['company_id'] = $_SESSION['company_id'];
    $resource_transfer['resourse_allocation_transfer_status'] = 0;

    $return = add_record('tbl_resource_allocation_transfer', $resource_transfer, $dbcon, $branch_id);
    return $return;
}

/*Code By Umair: 10/11/2020
Comment: Get Resource Name By Product Name And Process ID From tbl_product_process
*/
function get_resource_from_product_process($dbcon, $product_id, $process_id, $where = null)
{
    if ($where)
    {
        $where = 'AND ' . $where;
    }
    //process_type='1' AND
    $query = "select * from tbl_product_process where status = 0 and product_id='" . $product_id . "' AND process_id='" . $process_id . "' AND company_id='" . $_SESSION['company_id'] . "'  $where";
    $rs_product = $dbcon->query($query);
    $rel = brp_mysqli_fetch_assoc($rs_product);
    return $rel;
}

/*Code By Umair: 10/11/2020
Comment: Transfer the qty to another resource to display the dashboard based on the login user
*/
function allocate_process_transfer($dbcon, $existing_resource_id = null, $new_resource_id = null, $request_id = null, $process_id = null, $transfer_qty = null, $edit_id = null, $action_type = null, $branch_id = 0)
{

    $sql = "select * from tbl_allocate_process where process_id='" . $process_id . "' AND  resource_id='" . $existing_resource_id . "' AND p_ref_id='" . $request_id . "'  AND company_id='" . $_SESSION['company_id'] . "' and branch_id = '" . $branch_id . "' ";

    $rs_product = $dbcon->query($sql);
    $rel = brp_mysqli_fetch_assoc($rs_product);

    $process_allocate['process_id'] = $rel['process_id'];
    $process_allocate['resource_id'] = $new_resource_id;
    $process_allocate['p_start_time'] = $rel['p_start_time'];
    $process_allocate['p_start_time'] = $rel['p_start_time'];
    $process_allocate['p_qty'] = $transfer_qty;
    $process_allocate['pen_qty'] = $transfer_qty;
    $process_allocate['start_qty'] = $rel['start_qty'];
    $process_allocate['p_status'] = $rel['p_status'];
    $process_allocate['task_status'] = $rel['task_status'];
    $process_allocate['p_ref_id'] = $rel['p_ref_id'];
    $process_allocate['p_ref_type'] = $rel['p_ref_type'];
    $process_allocate['p_product_id'] = $rel['p_product_id'];
    $process_allocate['pr_process_type'] = $rel['pr_process_type'];
    $process_allocate['previous_process_id'] = $rel['previous_process_id'];
    $process_allocate['process_priority'] = $rel['process_priority'];
    $process_allocate['process_stock'] = $rel['process_stock'];
    $process_allocate['process_used_stock'] = $rel['process_used_stock'];
    $process_allocate['user_id'] = $_SESSION['user_id'];
    $process_allocate['cdate'] = date('Y-m-d H:i:s');
    $process_allocate['company_id'] = $_SESSION['company_id'];
    $process_allocate['process_type_data'] = $rel['process_type_data'];
    $process_allocate['process_unit'] = $rel['process_unit'];

    $return = '';
    if ($action_type == 'add')
    {

        $return = add_record('tbl_allocate_process', $process_allocate, $dbcon, $branch_id);

        $update_process['p_qty'] = number_format($rel['p_qty'] - $transfer_qty, 2, '.', '');
        $update_process['pen_qty'] = number_format($rel['pen_qty'] - $transfer_qty, 2, '.', '');

        update_record('tbl_allocate_process', $update_process, "p_id='" . $rel['p_id'] . "' ", $dbcon, $branch_id);
    }
    return $return;
}

/*
Code By Umair: 11/11/2020
Comment: Update the completed quantity and time entry in tbl_work_order_resource_allocate database 
*/

function update_completed_process_time_and_qty($dbcon, $process_id = null, $resource_id = null, $request_no = null, $com_qty = null)
{

    $query1 = "select * from tbl_allocate_process_trn where resource_id=" . $resource_id . " AND pt_ref_id=" . $request_no . " AND pt_process_id=" . $process_id . " AND pt_process_id=" . $process_id . " AND parent_pt_id='0' ORDER BY pt_id DESC Limit 1";
    $rows1 = brp_mysqli_fetch_assoc($dbcon->query($query1));

    $start_time = $rows1['process_time'];
    $end_time = date("Y-m-d H:i:s");

    $start_time = strtotime($start_time);
    $end_time = strtotime($end_time);

    $completed_time = round(abs($end_time - $start_time) / 60, 2);
    $completed_time = number_format($completed_time, 2);

    /*$updatedata['completed_time'] = $completed_time;
    $updatedata['completed_qty'] = $com_qty;
    $updatedata['muser_id'] = $_SESSION['user_id'];
    $updatedata['mdate'] = date("Y-m-d H:i:s");
    
    $where = 'request_id="'.$request_no.'" AND resource_id="'.$resource_id.'" AND process_id="'.$process_id.'" AND product_id="'.$rows1['pt_product_id'].'"';
    $res = update_record('tbl_work_order_resource_allocate', $updatedata, $where , $dbcon);*/

    $sql = "UPDATE tbl_work_order_resource_allocate SET completed_time = completed_time + '$completed_time', completed_qty = completed_qty + '$com_qty' , muser_id = " . $_SESSION['user_id'] . " , mdate='" . date("Y-m-d H:i:s") . "'  WHERE request_id='" . $request_no . "' AND resource_id='" . $resource_id . "' AND process_id='" . $process_id . "' AND product_id='" . $rows1['pt_product_id'] . "'";

    $res = $dbcon->query($sql);
    return $res;
}

/*
Code By Umair: 27/11/2020
Comment: Get Revision Number By ID
*/
function getrevision($dbcon, $cid)
{
    $query = "select * from tbl_revision where revision_status=0  AND approve_status = 3  AND user_id='" . $_SESSION['user_id'] . "' AND company_id='" . $_SESSION['company_id'] . "' ";
    $rs_cust = $dbcon->query($query);
    echo '<option value="">Choose Revision</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {
        $sel = '';
        if ($rel['revision_id'] == $cid)
        {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['revision_id'] . '">' . $rel['revision_number'] . '</option>';
    }
}

/*
Code By Umair: 27/11/2020
Comment: Get Revision Number By ID
*/
function getrevision_validate($dbcon, $cid, $did = null)
{
    if ($cid != '')
    {
        $where = '';
        if ($did != '')
        {
            $where = ' and drawing_id="' . $did . '" ';
        }
        $query = "select * from tbl_revision where revision_status=0 AND approve_status = 3 AND user_id='" . $_SESSION['user_id'] . "' AND company_id='" . $_SESSION['company_id'] . "' $where ";
        $rs_cust = $dbcon->query($query);
        echo '<option value="">Choose Revision</option>';
        while ($rel = brp_mysqli_fetch_assoc($rs_cust))
        {
            $sel = '';
            if ($rel['revision_id'] == $cid)
            {
                $sel = "selected='selected'";
            }
            echo '<option ' . $sel . ' value="' . $rel['revision_id'] . '">' . $rel['revision_number'] . '</option>';
        }
    }
}

/*Code By Umair: 27/11/2020
Comment: Get the Sales Order Resturn
*/
function getrevision_return($dbcon, $cid = null, $sid = null)
{

    //$query="SELECT `r`.`revision_id`, `r`.`revision_number` FROM `tbl_drawing` as dr left join `tbl_revision` as r on `dr`.`revision_id` = `r`.`revision_id` WHERE `dr`.`drawing_number`='".$cid."' and `r`.`revision_status`=0  AND `r`.`user_id`='".$_SESSION['user_id']."' AND `r`.`company_id`='".$_SESSION['company_id']."' ";
    $query = "SELECT * FROM `tbl_revision` WHERE `drawing_id`='" . $cid . "' and `revision_status`=0  AND approve_status = 3  AND `user_id`='" . $_SESSION['user_id'] . "' AND `company_id`='" . $_SESSION['company_id'] . "' ";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Revision.</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {
        $sel = '';
        if ($rel['revision_id'] == $sid)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['revision_id'] . '">' . $rel['revision_number'] . '</option>';
    }
    return $str;
}

/*
Code By Umair: 27/11/2020
Comment: Get Revision Number By ID
*/
function getsalesorder($dbcon, $cid = null, $sid = null)
{
    if ($cid != '')
    {
        $query = "select * from tbl_sales_order where sales_order_status=0 AND cust_id='" . $cid . "' AND user_id='" . $_SESSION['user_id'] . "' AND company_id='" . $_SESSION['company_id'] . "' ";
        $rs_cust = $dbcon->query($query);
        echo '<option value="">Choose SO NO.</option>';
        while ($rel = brp_mysqli_fetch_assoc($rs_cust))
        {
            $sel = '';
            if ($rel['sales_order_id'] == $sid)
            {
                $sel = "selected='selected'";
            }
            echo '<option ' . $sel . ' value="' . $rel['sales_order_id'] . '">' . $rel['sales_order_no'] . '</option>';
        }
    }
}
/*Code By Umair: 27/11/2020
Comment: Get the Sales Order Resturn
*/
function getsalesorder_return($dbcon, $cid = null, $sid = null)
{
    $query = "select * from tbl_sales_order where sales_order_status=0 AND cust_id='" . $cid . "' AND user_id='" . $_SESSION['user_id'] . "' AND company_id='" . $_SESSION['company_id'] . "' ";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose SO NO.</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {
        $sel = '';
        if ($rel['sales_order_id'] == $sid)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['sales_order_id'] . '">' . $rel['sales_order_no'] . '</option>';
    }
    return $str;
}

/*
Code By Umair: 27/11/2020
Comment: Get Drawing Number By ID
*/
function getdrawingnumber($dbcon, $cid = null)
{
    $query = "select * from tbl_drawing where drawing_status=0 AND approve_status = 3 AND company_id='" . $_SESSION['company_id'] . "' group by drawing_number ";
    $rs_cust = $dbcon->query($query);
    echo '<option value="">Choose Drawing No.</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {
        $sel = '';
        if ($rel['drawing_id'] == $cid)
        {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['drawing_id'] . '">' . $rel['drawing_number'] . '</option>';
    }
}

/*
Code By Umair: 28/11/2020
Comment: Get Make Name By ID
*/
function getmake($dbcon, $id)
{
    $query = "select * from tbl_make where make_status=0 and company_id in (0,$_SESSION[company_id])";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Make</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {
        $sel = '';
        if ($rel['make_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['make_id'] . '">' . $rel['make_name'] . '</option>';
    }
    return $str;
}

/*
Code By Umair: 03/02/2021
Comment: Get Make Number By ID
*/
function getmakenumber($dbcon, $id)
{
    $query = "select * from tbl_make_number where make_number_status=0 and company_id in (0,$_SESSION[company_id])";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Make Number</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {
        $sel = '';
        if ($rel['make_number_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['make_number_id'] . '">' . $rel['make_number'] . '</option>';
    }
    return $str;
}

function getpaymentbilldatawithledger($dbcon, $vender_id, $postdata)
{
    $response = [];
    $query = "SELECT tpo.po_no as billno,tpo.po_date as billdate,sum(tpot.total) as billtotal,tpo.g_total as grossamt,(select IFNULL(SUM(tbl_receipt_trn.total_amount), 0) from tbl_receipt_trn where tbl_receipt_trn.purchase_id=tpo.po_id ) as clearedpayment,tr.cheque_dtl as chqnumber,tr.ref_date as chqdate,tr.payment_date,tr.payment_remark,tpo.po_id
    FROM tbl_pono as tpo
    left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
    left JOIN tbl_receipt_trn as trt ON trt.purchase_id=tpo.po_id
    left JOIN tbl_receipt as tr ON trt.receipt_id=tr.receipt_id
    where tpo.status!='2' and tpo.vender_id=" . $vender_id;

    if ($postdata['po_date_type'])
    {
        if ($postdata['po_date_type'] == 'po')
        {
            $s_date = explode(' - ', $postdata['rep_po_date']);
            $startdate = $s_date[0];
            $enddate = $s_date[1];
            $query .= " and tpo.po_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tpo.po_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
        }
        else
        {
            $s_date = explode(' - ', $postdata['rep_del_date']);
            $startdate = $s_date[0];
            $enddate = $s_date[1];
            $query .= " and tpo.po_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tpo.po_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
        }
    }

    if (isset($postdata['specific_vendor']))
    {
        if ($postdata['vendor_id'])
        {
            $query .= ' and tpo.vender_id=' . $postdata['vendor_id'];
        }
    }
    if (isset($postdata['specific_item']))
    {
        if ($postdata['item_id'])
        {
            $query .= ' and tpot.product_id=' . $postdata['item_id'];
        }
    }
    if (isset($postdata['purchase_type_status']))
    {
        if ($postdata['purchase_type_id'])
        {
            $query .= ' and tpo.purchase_bill_type=' . $postdata['purchase_type_id'];
        }
    }
    //  echo $query;
    $query .= ' group by tpo.po_id';
    $result1 = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($result1))
    {
        array_push($response, $rel);
    }
    return $response;
}

function getpaymentbilldata($dbcon, $vender_id, $postdata)
{
    $response = [];
    $query = "SELECT tpo.po_no as billno,tpo.po_date as billdate,sum(tpot.total) as billtotal,tpo.g_total as grossamt,(select IFNULL(SUM(tbl_receipt_trn.total_amount), 0) from tbl_receipt_trn where tbl_receipt_trn.purchase_id=tpo.po_id ) as clearedpayment
    FROM tbl_pono as tpo
    left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
    inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
    where tpo.status!='2' and tpo.vender_id=" . $vender_id;

    if ($postdata['po_date_type'])
    {
        if ($postdata['po_date_type'] == 'po')
        {
            $s_date = explode(' - ', $postdata['rep_po_date']);
            $startdate = $s_date[0];
            $enddate = $s_date[1];
            $query .= " and tpo.po_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tpo.po_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
        }
        else
        {
            $s_date = explode(' - ', $postdata['rep_del_date']);
            $startdate = $s_date[0];
            $enddate = $s_date[1];
            $query .= " and tpo.po_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tpo.po_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
        }
    }

    if (isset($postdata['specific_vendor']))
    {
        if ($postdata['vendor_id'])
        {
            $query .= ' and tpo.vender_id=' . $postdata['vendor_id'];
        }
    }
    if (isset($postdata['specific_item']))
    {
        if ($postdata['item_id'])
        {
            $query .= ' and tpot.product_id=' . $postdata['item_id'];
        }
    }
    if (isset($postdata['purchase_type_status']))
    {
        if ($postdata['purchase_type_id'])
        {
            $query .= ' and tpo.purchase_bill_type=' . $postdata['purchase_type_id'];
        }
    }
    //  echo $query;
    $query .= ' group by tpo.po_id';
    $result1 = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($result1))
    {
        array_push($response, $rel);
    }
    return $response;
}

function getpaymentbilldatabillnowise($dbcon, $postdata)
{
    $response = [];
    $query = "SELECT tpo.po_no as billno,tl.l_name,tpo.po_date as billdate,sum(tpot.total) as billtotal,tpo.g_total as grossamt,(select IFNULL(SUM(tbl_receipt_trn.total_amount), 0) from tbl_receipt_trn where tbl_receipt_trn.purchase_id=tpo.po_id ) as clearedpayment
    FROM tbl_pono as tpo
    left JOIN tbl_ledger as tl ON tl.l_id=tpo.vender_id 
    left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
    inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
    where tpo.status!='2'";

    $startdate = $postdata['from_po_date'];
    $enddate = $postdata['to_po_date'];
    $query .= " and tpo.po_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tpo.po_date<='" . date('Y-m-d', strtotime($enddate)) . "'";

    // if($postdata['po_date_type']){
    //  if($postdata['po_date_type']=='po'){
    //      $startdate=$postdata['from_po_date'];
    //      $enddate=$postdata['to_po_date'];
    //      $query.=" and tpo.po_date>='".date('Y-m-d',strtotime($startdate))."' and tpo.po_date<='".date('Y-m-d',strtotime($enddate))."'";
    //  }else{
    //      $startdate=$postdata['from_po_date'];
    //      $enddate=$postdata['to_po_date'];
    //      $query.=" and tpo.po_date>='".date('Y-m-d',strtotime($startdate))."' and tpo.po_date<='".date('Y-m-d',strtotime($enddate))."'";
    //  }
    // }
    if ($postdata['vendor_id'])
    {
        $query .= ' and tpo.vender_id=' . $postdata['vendor_id'];
    }
    if ($postdata['item_id'])
    {
        $query .= ' and tpot.product_id=' . $postdata['item_id'];
    }
    if ($postdata['purchase_type_id'])
    {
        $query .= ' and tpo.purchase_bill_type=' . $postdata['purchase_type_id'];
    }
    // if(isset($postdata['specific_vendor'])){
    //  if($postdata['vendor_id']){
    //      $query.=' and tpo.vender_id='.$postdata['vendor_id'];
    //  }
    // }
    // if(isset($postdata['specific_item'])){
    //  if($postdata['item_id']){
    //              $query.=' and tpot.product_id='.$postdata['item_id'];
    //  }
    // }
    // if(isset($postdata['purchase_type_status'])){
    //  if($postdata['purchase_type_id']){
    //              $query.=' and tpo.purchase_bill_type='.$postdata['purchase_type_id'];
    //  }
    // }
    //  echo $query;
    $query .= ' group by tpo.po_id';
    $result1 = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($result1))
    {
        array_push($response, $rel);
    }
    return $response;
}

function getproductbysalesorder($dbcon, $prids)
{

    $str = '';

    $query = "select * from product_mst as pro where product_status=0 and  product_id in ($prids) and company_id in (0,$_SESSION[company_id]) order by product_name";
    $rs_dispatch = $dbcon->query($query);
    $str .= '<option value="">Choose Product</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        if ($rel['product_id'] == $prids)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '">' . $rel['product_name'] . ' - ' . $rel['product_code'] . '</option>';
    }
    return $str;
}

function getstages($dbcon)
{
    $str = '';
    $query = "select * from stage_mst as l where l.stage_status=0";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Stage</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {
        $sel = '';
        // if($rel['l_id']==$id) { $sel="selected='selected'"; }
        $str .= '<option ' . $sel . ' value="' . $rel['stage_id'] . '">' . $rel['stage_name'] . '.</option>';
    }
    return $str;
    //return $query;
    
}

function getsalesorderprdctqty($dbcon, $prid, $sales_order_id)
{
    $query = "select product_qty from tbl_sales_ordertrn where product_id='$prid' and sales_order_id='$sales_order_id'";

    $row = $dbcon->query($query);

    $rel = brp_mysqli_fetch_assoc($row);

    return $rel['product_qty'];
}
function getstagedata($dbcon, $field, $prid, $sales_order_id, $stageid)
{
    $query = "SELECT " . $field . " from tbl_sales_order_stage where product_id='$prid' and sales_order_id='$sales_order_id' and stage_id=$stageid";

    $row = $dbcon->query($query);

    $rel = brp_mysqli_fetch_assoc($row);

    return $rel[$field];
}
//(select sum(tbl_grn_trn.product_qty)  from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as pending
function sostagereportdata($dbcon, $postdata)
{
    $response = [];
    $query = "SELECT DISTINCT tsos.product_id,tsos.sales_order_id,tso.sales_order_no,tsos.sales_order_id,pm.product_name,pm.product_hsn,tl.l_name,bunit.unit_name,tsos.product_id
    FROM tbl_sales_order_stage as tsos left JOIN tbl_sales_order as tso ON tso.sales_order_id=tsos.sales_order_id inner JOIN product_mst as pm ON pm.product_id=tsos.product_id 
    left JOIN tbl_ledger as tl ON tl.l_id=tso.cust_id 
    left join unit_mst as bunit on bunit.unitid=tsos.unitid
    where tso.sales_order_status!='2'";

    $result1 = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($result1))
    {
        array_push($response, $rel);
    }
    return $response;
}
// left join tbl_product_stage as tps on tps.pro
function get_stage_completed_per($dbcon, $sales_order_id, $prid, $stage_id)
{
    $response = [];
    $query = "SELECT sum(tsos.accept_qty) as accepttotqty,tsos.product_qty,tsos.stage_id,tps.stage_per from tbl_sales_order_stage  as tsos
    left join tbl_product_stage as tps on tps.party_product=tsos.product_id and tps.stage_id=tsos.stage_id
    where tsos.product_id='$prid' and tsos.sales_order_id='$sales_order_id' group by tsos.stage_id";
    $result1 = $dbcon->query($query);
    $tot_pecenatge = 0;
    while ($rel = brp_mysqli_fetch_assoc($result1))
    {
        $rel['totacptqty_ratio'] = $rel['accepttotqty'] * 100 / $rel['product_qty'];
        $rel['com_percentage'] = $rel['totacptqty_ratio'] * $rel['stage_per'] / 100;
        $tot_pecenatge = $tot_pecenatge + $rel['com_percentage'];
        array_push($response, $rel);
    }
    return number_format($tot_pecenatge, 2);
}

function getcurrentstage($dbcon, $sales_order_id, $prid)
{
    $query = "SELECT tsos.stage_id,sm.stage_name from tbl_sales_order_stage  as tsos
    left join tbl_product_stage as tps on tps.party_product=tsos.product_id and tps.stage_id=tsos.stage_id
    left join  stage_mst as sm on tsos.stage_id=sm.stage_id
    where tsos.product_id='$prid' and tsos.sales_order_id='$sales_order_id'  order by tsos.id desc limit 1";

    $row = $dbcon->query($query);

    $rel = brp_mysqli_fetch_assoc($row);

    return $rel['stage_name'];
}
function getmaxtqtystagewise($dbcon, $stageid, $prdctid, $sales_order_id)
{
    $query = "SELECT sum(tsos.accept_qty) as acceptqty,(tso.product_qty - (IFNULL(sum(tsos.accept_qty),0))) as remaining
    FROM tbl_sales_ordertrn as tso
    left JOIN tbl_sales_order_stage as tsos ON tsos.product_id=tso.product_id and tsos.sales_order_id=tsos.sales_order_id
    where tsos.stage_id=" . $stageid . " and tsos.product_id=" . $prdctid . " and tsos.sales_order_id=" . $sales_order_id;
    $row = $dbcon->query($query);
    $rel = brp_mysqli_fetch_assoc($row);
    return $rel['remaining'];
}

function getallcategoriesdata($dbcon)
{
    $allcat = [];
    $query = "SELECT * FROM tbl_category where cat_status=0 and company_id = '" . $_SESSION['company_id'] . "'";
    $rs_type = $dbcon->query($query);
    while ($row = brp_mysqli_fetch_assoc($rs_type))
    {
        array_push($allcat, $row);
    }
    return $allcat;
}

function getcategoriesbyid($dbcon, $id)
{
    $query = "SELECT * from tbl_category where cat_id=" . $id;
    $row = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($row);
    return $rel;
}

function getavgprorate($dbcon, $prid, $fromdate, $todate)
{
    $query = "SELECT avg(product_rate) as product_rate from tbl_potrancation as ptrn
    left join tbl_pono as pno on pno.po_id = ptrn.po_id 
    where  product_id=" . $prid;
    $query .= " and pno.po_date>='" . $fromdate . "' and pno.po_date<='" . $todate . "'";

    $rsCategoryId = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($rsCategoryId);
    //var_dump($query);
    return $row['product_rate'];
}

function getstockusingprid($dbcon, $id, $frmdate, $todate)
{
    $query = "SELECT  pro.product_id, pro.product_base_unit, un.unit_name, pro.product_name, pro.product_status,pro.product_icode, 
    (SELECT sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1  and qc.stock_date>='" . $frmdate . "' and qc.stock_date<='" . $todate . "' group by qc.product_id) as base_stock_add, 
      (SELECT sum(qc.base_stock) as base_stock_minus from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1   and qc.stock_date>='" . $frmdate . "' and qc.stock_date<='" . $todate . "' group by qc.product_id) as base_stock_minus, 
      (SELECT sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='" . $frmdate . "' and qc.stock_date<='" . $todate . "' group by qc.product_id) as con_stock_add, 
      (SELECT sum(qc.convert_stock) as con_stock_minus from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='" . $frmdate . "' and qc.stock_date<='" . $todate . "' group by qc.product_id) as con_stock_minus 
      FROM product_mst as pro left join unit_mst as un on un.unitid=pro.product_base_unit where ( 1 AND pro.product_status !=2 ) and pro.product_id=" . $id;
      $rsCategoryId = $dbcon->query($query);
      $row = brp_mysqli_fetch_assoc($rsCategoryId);
      return $stock = ($row['base_stock_add'] + $row['con_stock_add']) - ($row['base_stock_minus'] + $row['con_stock_minus']);

  }

  function getstockusingprid1($dbcon, $id, $frmdate, $todate)
  {
    $query = "SELECT  pro.product_id, pro.product_base_unit, un.unit_name, pro.product_name, pro.product_status,pro.product_icode, 
    (SELECT sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 group by qc.product_id) as base_stock_add, 
    (SELECT sum(qc.base_stock) as base_stock_minus from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 group by qc.product_id) as base_stock_minus, 
    (SELECT sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1   group by qc.product_id) as con_stock_add, 
    (SELECT sum(qc.convert_stock) as con_stock_minus from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1   group by qc.product_id) as con_stock_minus 
    FROM product_mst as pro left join unit_mst as un on un.unitid=pro.product_base_unit where ( 1 AND pro.product_status !=2 ) and pro.product_id=" . $id;

    $rsCategoryId = $dbcon->query($query);
    $row = mysqli_fetch_assoc($rsCategoryId);
    return $stock = ($row['base_stock_add'] + $row['con_stock_add']) - ($row['base_stock_minus'] + $row['con_stock_minus']);
}

function startingstock($dbcon, $prid, $frmdate = '')
{
    $query = "SELECT  pro.product_id, pro.product_base_unit, un.unit_name, pro.product_name, pro.product_status,pro.product_icode, 
    (select sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date<='" . $frmdate . "' group by qc.product_id) as base_stock_add, 
       (select sum(qc.base_stock) as base_stock_minus from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date<='" . $frmdate . "' group by qc.product_id) as base_stock_minus, 
       (select sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date<='" . $frmdate . "' group by qc.product_id) as con_stock_add, 
       (select sum(qc.convert_stock) as con_stock_minus from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date<='" . $frmdate . "' group by qc.product_id) as con_stock_minus 
       FROM product_mst as pro left join unit_mst as un on un.unitid=pro.product_base_unit where ( 1 AND pro.product_status !=2 ) and pro.product_id=" . $prid;

       $rsCategoryId = $dbcon->query($query);
       $row = mysqli_fetch_assoc($rsCategoryId);

       return $stock = ($row['base_stock_add'] + $row['con_stock_add']) - ($row['base_stock_minus'] + $row['con_stock_minus']);
   }

   function getprorate($dbcon, $prid, $type, $fromdate, $todate)
   {
    $query = "Select * from tbl_potrancation where  product_id=" . $prid;
    if ($type == 0)
    {
        $query = "Select ptrn.* from tbl_potrancation as ptrn
        left join tbl_pono as pno on pno.po_id = ptrn.po_id 
        where  ptrn.product_id=" . $prid;
        $query .= " and pno.po_date>='" . $fromdate . "' and pno.po_date<='" . $todate . "'  order by ptrn.potrancation_id asc limit 1";
    }

    if ($type == 1)
    {
        $query = "Select * from tbl_potrancation as ptrn
        left join tbl_pono as pno on pno.po_id = ptrn.po_id 
        where  product_id=" . $prid;
        $query .= " and pno.po_date>='" . $fromdate . "' and pno.po_date<='" . $todate . "'  order by ptrn.potrancation_id desc limit 1";
    }

    $rsCategoryId = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($rsCategoryId);
    //var_dump($query);
    return $row['product_rate'];
}

/*Nikunj START*/
function getallchallanno($dbcon, $product_id, $purchase_order_trn_id, $field)
{
    $response = [];
    $query = "SELECT tg." . $field . ",tg.grn_date
    FROM tbl_grn_trn as tgt
    left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id and tgt.product_id=tpt.product_id
    left JOIN tbl_grn as tg ON tgt.grn_id=tg.grn_id 
    where tpt.purchaseordertrn_status!=2  and tgt.product_id=" . $product_id . " and tgt.purchaseordertrn_id=" . $purchase_order_trn_id;
    $result1 = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($result1))
    {
        array_push($response, $rel);
    }
    //print_r($response);
    //exit;
    return $response;
}
/*Nikunj End*/
function get_inquiry_probability($dbcon, $eid)
{
    $qry = "select opp_id,opp_probability from tbl_opportunity_mst where opp_status=0";
    $rs_state = $dbcon->query($qry);
    $str = "<option value=''>Choose Stage</option>";
    while ($row = brp_mysqli_fetch_assoc($rs_state))
    {
        $sel = '';
        if ($row['opp_id'] == $eid)
        {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['opp_id'] . '">' . $row['opp_probability'] . '</option>';
    }
    return $str;
}
function month_name()
{
    $str='';
    $str .= '<option value="">--Select Month--</option>';
    for ($y = 1970;$y <= 2036;$y++)
    {
        for ($x = 1;$x <= 12;$x++)
        {
            $sel = "";
            $v = date("1-" . $x . "-" . $y);
            $d = date("Y-m");
            $d1 = date("Y");
            $d2 = date("m");
            if ($y == $d1)
            {
                if ($x == $d2)
                {
                    $sel = 'selected="selected"';
                }
            }
            $month_name = date("F", mktime(0, 0, 0, $x, 10));
            $str .= '<option ' . $sel . ' value="' . $v . '">' . $y . '-' . $month_name . '</option>';
        }
    }
    return $str;
}
//pathik start date 10-12-2020
function work_order_bom_show_print($dbcon, $bom_id, $qty, $num, $call, $space)
{
    $query_m = "select * from tbl_bom as bom where bom_status=0 and bom_id=" . $bom_id;
    $result_m = $dbcon->query($query_m);
    $rel_m = brp_mysqli_fetch_assoc($result_m);

    $query1 = "select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
    left join product_mst as pro on pro.product_id=bom_trn.product_id
    left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
    left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
    where bom_trn_status=0 and bom_id=" . $bom_id;
    $result1 = $dbcon->query($query1);
    $k = 1;
    $new_call = $call + 1;
    for ($x = 1;$x <= $call;$x++)
    {
        $space = $space . "&nbsp;&nbsp;";
    }
    while ($rel1 = mysqli_fetch_assoc($result1))
    {
        $html = '';
        $new_num = $num . "." . $k;

        $base_one_qty = $rel1['product_base_qty'] / $rel_m['product_base_qty'];
        $base_qty = $base_one_qty * $qty;
        $conv_stock = convert_stock($dbcon, $base_qty, $rel1['product_id'], "conv_unit");

        $html .= '<tr>
        <!-- <td style="border:0.5px #444 solid;">' . $space . $new_num . '</td> -->
        <td style="border:0.5px #444 solid;">' . $new_num . '</td>
        <td style="border:0.5px #444 solid;">' . $rel1['product_name'] . '</td>
        <td style="border:1px #444 solid;" >' . get_product_type_by_id($dbcon, $rel1['product_type']) . '</td>
        <td style="border:1px #444 solid;" >';
        $_SESSION['bom_tot'] = $_SESSION['bom_tot'] + $base_qty;
        if ($rel1['product_base_unit'] != $rel1['product_conv_unit'])
        {
            $html .= $base_qty . $rel1['base_unit_name'] . '<br/>';
            $html .= $conv_stock . $rel1['conv_unit_name'];
        }
        else
        {
            $html .= $base_qty . $rel1['base_unit_name'];
        }
        $html .= '</td>
        <td style="border:1px #444 solid;">' . $rel1['base_unit_name'] . '</td>
        <td style="border:1px #444 solid;">' . get_last_purchase($dbcon, $rel1['product_id']) . '</td>
        <td style="border:1px #444 solid;" >';
        $query = "select mst.*,p.process_name from tbl_product_process as mst 
        left join process_mst as p on p.process_id=mst.process_id where mst.status = 0 and mst.product_id=" . $rel1['product_id'] . " order by process_priority";
        $result = $dbcon->query($query);
        $cnt = mysqli_num_rows($result);
        if ($cnt > 0)
        {
            $html .= '<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
            <tr>
            <th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
            <th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
            <th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
            </tr>';
            while ($rel = mysqli_fetch_assoc($result))
            {
                if ($rel['process_type'] == 1)
                {
                    $process_type = "Inhouse";
                }
                else
                {
                    $process_type = "Outside";
                }

                $html .= '<tr>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $rel['process_priority'] . '</td>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $process_type . '</td>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $rel['process_name'] . '</td>
                </tr>';
            }
            $html .= '</table>';
        }
        $html .= '</td>
        </tr>';
        $html .= work_order_bom_show_print($dbcon, $rel1['p_bom_id'], $base_qty, $new_num, $new_call, $space);
        $k++;
    }
    return $html;
}
//pathik end
//pathik start date 11-12-2020
function work_order_po_track($dbcon, $rp_id)
{
    $query1 = "select bom_trn.rp_po_qty,rp_po_base_qty,rp_req_date,process_unit,purchase_unit from tbl_request_product as bom_trn 
    where status=0 and rp_id=" . $rp_id;
    $result1 = $dbcon->query($query1);
    $rel1 = brp_mysqli_fetch_assoc($result1);

    $base_unit_name = getunitname($dbcon,$rel1['process_unit']);
    $conv_unit_name = getunitname($dbcon,$rel1['purchase_unit']);

    $workorder = $rel1['rp_po_qty'];
    $workorder_base = $rel1['rp_po_base_qty'];
    
    $query2 = "select IFNULL(sum(approve_qty), 0) as used_qty,IFNULL(sum(approve_base_qty), 0) as used_base_qty,approve_date from approve_indent as bom_trn 
    where approve_indent_status=0 and rp_id=" . $rp_id;
    $result2 = $dbcon->query($query2);
    $rel2 = brp_mysqli_fetch_assoc($result2);
    $indent_qty = $rel2['used_qty'];
    $indent_base_qty = $rel2['used_base_qty'];

    $workorder = $workorder - $indent_qty;
    $workorder_base = $workorder_base - $indent_base_qty;

    $query3 = "select IFNULL(sum(product_qty), 0) as total_qty,IFNULL(sum(product_base_qty), 0) as total_base_qty,purchaseordertrn_id,cdate,unit_id from tbl_purchasetrntemp as bom_trn 
    where purchaseordertrn_status=0 and po_ref_id=" . $rp_id;

    $result3 = $dbcon->query($query3);
    $rel3 = brp_mysqli_fetch_assoc($result3);

    $query4 = "select IFNULL(sum(used_qty), 0) as use_qty,IFNULL(sum(used_base_qty), 0) as use_base_qty,cdate from tbl_purchaseorder_req_trn as bom_trn 
    where purchaseordertrn_req_status=0 and req_id=" . $rel3['purchaseordertrn_id'];
    $result4 = $dbcon->query($query4);
    $rel4 = brp_mysqli_fetch_assoc($result4);
    $po_pending_qty = $rel3['total_qty'];
    $po_pending_base_qty = $rel3['total_base_qty'];
    $purchase_order_qty = $rel4['use_qty'];
    $purchase_order_base_qty = $rel4['use_base_qty'];

    $po_pending_qty = $po_pending_qty - $purchase_order_qty;
    $po_pending_base_qty = $po_pending_base_qty - $purchase_order_base_qty;

    $query5 = "select IFNULL(sum(bom_trn.used_qty), 0) as total_qty,IFNULL(sum(bom_trn.used_base_qty), 0) as total_base_qty,GROUP_CONCAT(bom_trn.purchaseordertrn_id) as purchaseordertrn_id,trn.product_id,bom_trn.cdate from 
    tbl_purchaseorder_req_trn  as bom_trn 
    left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id = bom_trn.purchaseordertrn_id
    where trn.purchaseordertrn_status=0 and bom_trn.purchaseordertrn_req_status = 0 and bom_trn.rp_id=" . $rp_id;

    $result5 = $dbcon->query($query5);
    $rel5 = brp_mysqli_fetch_assoc($result5);

    /* $query5="select IFNULL(sum(product_qty), 0) as total_qty,purchaseordertrn_id,product_id from tbl_purchaseordertrn as bom_trn
    where purchaseordertrn_status=0 and po_ref_id=".$rp_id;
    $result5=$dbcon->query($query5);
    $rel5=brp_mysqli_fetch_assoc($result5); */

    $query6 = "select IFNULL(sum(product_qty), 0) as used_qty,IFNULL(sum(product_conv_qty), 0) as used_conv_qty,bom_trn.cdate,product_conv_unit from tbl_grn_sub_trn as bom_trn 
    where status=0 and purchaseordertrn_id in(" . $rel5['purchaseordertrn_id'] . ") and rp_id = " . $rp_id . " and product_id=" . $rel5['product_id'];
    $result6 = $dbcon->query($query6);
    $rel6 = brp_mysqli_fetch_assoc($result6);
    // if($rel6['product_conv_unit'] == $rel3['unit_id']){
        $inward_qty = $rel6['used_conv_qty'];    
    // }else{
        $inward_base_qty = $rel6['used_qty'];    
    // }
    
    // $grn_qty=$rel5['total_qty'];
    $grn_qty = $rel5['total_qty'] - $inward_qty;
    $grn_base_qty = $rel5['total_base_qty'] - $inward_base_qty;

    $product_id = $rel5['product_id'];

    $qc_paramter_info = check_product_qc_paramter($dbcon, $product_id, "");

    if ($qc_paramter_info == '1')
    {
        $query7 = "select IFNULL(sum(product_qty), 0) as total_qty,bom_trn.cdate from tbl_grn_trn as bom_trn 
        left join tbl_grn as gnr on gnr.grn_id=bom_trn.grn_id
        where bom_trn.grn_trn_status=0 and bom_trn.product_qc=0 and gnr.ref_type=2 and po_ref_id=" . $rp_id;
        $result7 = $dbcon->query($query7);
        $rel7 = brp_mysqli_fetch_assoc($result7);

        // $qc_qty=$rel7['total_qty'];
        // $qc_qty = $rel6['used_qty'];
        // if($rel6['product_conv_unit'] == $rel3['unit_id']){
            $qc_qty = $rel6['used_conv_qty'];    
        // }else{
            $qc_base_qty = $rel6['used_qty'];    
        // }

        $query8 = "select IFNULL(sum(accept_qty), 0) as accept_qty,IFNULL(sum(reject_qty-reject_used_qty), 0) as reject_qty,IFNULL(sum(reprocess_qty), 0) as reprocess_qty,bom_trn.cdate,bom_trn.qc_unit from tbl_qc_process_trn as bom_trn 
        where bom_trn.qc_process_status=0 and p_ref_id=" . $rp_id;
        $result8 = $dbcon->query($query8);
        $rel8 = brp_mysqli_fetch_assoc($result8);

        $qc_accept_qty = $rel8['accept_qty'];
        $qc_reject_qty = $rel8['reject_qty'];
        $qc_reprocess_qty = $rel8['reprocess_qty'];

        $qc_qty = $qc_qty - ($qc_accept_qty + $qc_reject_qty + $qc_reprocess_qty);

        if($conv_unit_name == $rel8['qc_unit']){
            $qc_unit_name = $base_unit_name;
        }else{
            $qc_unit_name = $conv_unit_name; 
        }
        
    }

    $str = "";
    $str .= '<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
    <tr>
    <th width="60%">stages</th>
    <th width="20%"> Qty</th>
     <th width="20%"> Date</th>  
    </tr>';
    if ($workorder > 0)
    {
        $str .= '<tr style="color:red">
        <td>Indent Approved Pending</td>
        <td>' . $workorder_base .  ' ' . $base_unit_name . '</br>' . $workorder . ' '. $conv_unit_name . '</td>
        <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_po_tracking_date('.$rp_id.',\'indent_approve_pending\')"><i class="fa fa-eye"></i></button> </td>  
        </tr>';
    }
    if ($indent_qty > 0)
    {
        $str .= '<tr style="color:green">
        <td>Indent Approved</td> 
        <td>' . $indent_base_qty .  ' ' . $base_unit_name . '</br>' . $indent_qty . ' '. $conv_unit_name . '</td>
        <td><button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_po_tracking_date('.$rp_id.',\'indent_approved\')"><i class="fa fa-eye"></i></button> </td> 
        </tr>';
    }
    // if ($query72 > 0) {
    //  $str .= '<tr>
    //  <td>Purchase Quotation</td>
    //  <td>' . $query72 . '</td>
    //  </tr>';
    // }
    if ($po_pending_qty > 0)
    {
        $str .= '<tr style="color:red">
        <td>Purchase order Pending</td>
        <td>' . $po_pending_base_qty .  ' ' . $base_unit_name . '</br>' . $po_pending_qty . ' '. $conv_unit_name . '</td>
        <td><button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_po_tracking_date('.$rp_id.',\'po_pending\','.$rel3['purchaseordertrn_id'].')"><i class="fa fa-eye"></i></button> </td> 
        </tr>';
    }
    if ($purchase_order_qty > 0) 
    {
        $str .= '<tr style="color:green">
        <td>Purchase order</td>
        <td>' . $purchase_order_base_qty .  ' ' . $base_unit_name . '</br>' . $purchase_order_qty . ' '. $conv_unit_name . '</td>
         <td><button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_po_tracking_date('.$rp_id.',\'purchase_order\','.$rel3['purchaseordertrn_id'].')"><i class="fa fa-eye"></i></button> </td> 
        </tr> 
        </tr>';
    }
    if ($grn_qty > 0)
    {
        $str .= '<tr style="color:red">
        <td>Inward Pending</td>
        <td>' . $grn_base_qty .  ' ' . $base_unit_name . '</br>' . $grn_qty . ' '. $conv_unit_name . '</td>
        <td><button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_po_tracking_date('.$rp_id.',\'inward_pending\')"><i class="fa fa-eye"></i></button> </td>  
        </tr>';
    }
    if ($inward_qty > 0)
    {
        $str .= '<tr style="color:green">
        <td>Grn Inwarded</td>
        <td>' . $inward_base_qty .  ' ' . $base_unit_name . '</br>' . $inward_qty . ' '. $conv_unit_name . '</td>
        <td><button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_po_tracking_date('.$rp_id.',\'grn_inward\',\''.$rel5['purchaseordertrn_id'].'\','.$rel5['product_id'].')"><i class="fa fa-eye"></i></button> </td>  
        </tr>';
    }
    if ($qc_qty > 0)
    {
        $str .= '<tr style="color:red">
        <td>Qc Pending</td>
        <td>' . $qc_base_qty .  ' ' . $base_unit_name . '</br>' . $qc_qty . ' '. $conv_unit_name . '</td>
        <td><button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_po_tracking_date('.$rp_id.',\'qc_pending\')"><i class="fa fa-eye"></i></button> </td>   
        </tr>';
    }
    if ($qc_accept_qty > 0)   
    {
        $str .= '<tr style="color:green">
        <td>Qc Accept</td>
        <td>' . $qc_accept_qty . ' ' . $qc_unit_name . '</td>
        <td><button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_po_tracking_date('.$rp_id.',\'qc_accepted\')"><i class="fa fa-eye"></i></button> </td>   
        </tr>';
    }
    if ($qc_reject_qty > 0)
    {
        $str .= '<tr style="color:green">
        <td>Qc Reject</td>
        <td>' . $qc_reject_qty . ' ' . $qc_unit_name . '</td>
        <td><button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_po_tracking_date('.$rp_id.',\'qc_rejected\')"><i class="fa fa-eye"></i></button> </td>   
        </tr>';
    }
    if ($qc_reprocess_qty > 0)
    {
        $str .= '<tr style="color:green">
        <td>Qc Reprocess</td>
        <td>' . $qc_reprocess_qty .' ' . $qc_unit_name .  '</td>
        <td><button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_po_tracking_date('.$rp_id.',\'qc_reprocess\')"><i class="fa fa-eye"></i></button> </td>   
        </tr>';
    }

    $query3 = "select IFNULL(sum(accept_qty),0) as store_pen_qty,IFNULL(sum(strn.product_conv_qty),0) as grn_qty,batch.cdate,batch.batch_unit from tbl_batch_data as batch 
    left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id and trn.grn_trn_status = 0 
    left join tbl_grn_sub_trn as strn on trn.grn_trn_id = strn.grn_trn_id and strn.status = 0 
    where  batch.qc_status = 1 and stock_approval_status = 0 and strn.rp_id=" . $rp_id;

    $result3 = $dbcon->query($query3);
    $rel3 = brp_mysqli_fetch_assoc($result3);

    if ($qc_paramter_info == '1')
    {
        $store_pending = $rel3['store_pen_qty'];
    }
    else
    {
        $store_pending = $rel3['grn_qty'];
    }

  if($conv_unit_name == $rel3['batch_unit']){
        $bt_unit_name = $base_unit_name;
    }else{
        $bt_unit_name = $conv_unit_name; 
    }

    if ($store_pending > 0)
    {
        $str .= '<tr style="color:red">
        <td> Store approval Pending </td>
        <td>' . $store_pending . '  '. $bt_unit_name  . '</td> 
        <td><button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_po_tracking_date('.$rp_id.',\'store_pending\',\'\',\'\','.$qc_paramter_info.')"><i class="fa fa-eye"></i></button> </td>
        </tr>';
    }

    $query3 = "select IFNULL(sum(accept_qty),0) as store_acp_qty,IFNULL(sum(strn.product_conv_qty),0) as grn_qty,batch.cdate  from tbl_batch_data as batch 
    left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id and trn.grn_trn_status = 0 
    left join tbl_grn_sub_trn as strn on trn.grn_trn_id = strn.grn_trn_id and strn.status = 0 
    where  batch.qc_status = 1 and stock_approval_status = 1 and strn.rp_id=" . $rp_id;

    $result3 = $dbcon->query($query3);
    $rel3 = brp_mysqli_fetch_assoc($result3);

    if ($qc_paramter_info == '1')
    {
        $store_accept = $rel3['store_acp_qty'];
    }
    else
    {
        $store_accept = $rel3['grn_qty'];
    }

    if ($store_accept > 0)
    {
        $str .= '<tr style="color:green">
        <td> Store Accepted </td>
        <td>' . $store_accept .  '  '. $bt_unit_name  .'</td>
        <td><button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_po_tracking_date('.$rp_id.',\'store_accept\',\'\',\'\','.$qc_paramter_info.')"><i class="fa fa-eye"></i></button> </td>
        </tr>';
    }
    $str .= '</table>';

    return $str;
}



/*Code By Umair: 11/12/2020
Comment: Get the Resource Allocate List
*/
function get_resource_work_list($dbcon, $id, $branch_id = 0)
{
    $where ='';
    //$branch_id = ($_SESSION['user_type'] == '2' && isset($branch_id) && $branch_id) ? $branch_id : '';
    $where_db = check_branch('res', $branch_id);
    $where .= " $where_db and res.company_id=" . $_SESSION['company_id'];

    $query = "SELECT resource_id,resource_name,ledger_id,l_name FROM `tbl_resource` as res left join `tbl_ledger` as l on `res`.`ledger_id`=`l`.`l_id` where `res`.`resource_status`=0 $where ";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Employee</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {
        $sel = '';
        if ($rel['resource_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['resource_id'] . '">' . $rel['resource_name'] . ' - (' . $rel['l_name'] . ')' . '</option>';
    }
    return $str;
}

/*
Code By Umair:  25/12/2020
Comment: Get Email Module List From email_module_list table
*/
function get_email_module_list($dbcon, $module_id = null, $showCaption = true)
{
    $str = '';
    $query = "select * from email_module_list where status=0 ";
    $rs_product = $dbcon->query($query);

    if ($showCaption)
    {
        $str .= '<option value="">--Select Module--</option>';
    }
    while ($rel = brp_mysqli_fetch_assoc($rs_product))
    {
        $sel = '';
        if ($rel['email_module_id'] == $module_id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['email_module_id'] . '">' . $rel['name'] . '</option>';
    }
    return $str;
}

/*
Code By Umair:  25/12/2020
Comment: Get Email Module List From email_module_list table
*/
function get_email_type_based_on_module($dbcon, $module_id, $email_type_id = null)
{
    $str = '';
    $query = "select * from email_module_type_list where status=0 and module_id = '" . $module_id . "' ";
    $rs_product = $dbcon->query($query);
    $str .= '<option value="">--Select Email Type--</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product))
    {
        $sel = '';
        if ($rel['email_module_type_id'] == $email_type_id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['email_module_type_id'] . '">' . $rel['email_template_name'] . '</option>';
    }
    return $str;
}

/*
Code By Umair:  28/12/2020
Comment: Get All Vendor List Based On The Process ID and Product ID From tbl_product_job_party_purchase table
*/
function get_vendor_based_on_process_product_id($dbcon, $process_id, $product_id = null)
{
    $query = "SELECT `pjpp`.`job_party_rate`, `l`.`l_name` FROM `tbl_product_job_party_purchase` as pjpp left join `tbl_ledger` as l on `l`.`l_id` = `pjpp`.`job_party_id` where `pjpp`.`job_party_process_id` = '" . $process_id . "' and `pjpp`.`job_party_product` = '" . $product_id . "' and  `pjpp`.`company_id` = '" . $_SESSION['company_id'] . "' ";

    $result = $dbcon->query($query);
    $rs_vendor_count = brp_mysqli_num_rows($result);

    $vendor_array = [];
    if ($rs_vendor_count > 0)
    {
        while ($rel = brp_mysqli_fetch_assoc($result))
        {
            $vendor_array[] = $rel['l_name'];
        }
    }

    return $vendor_array;
}

/*
Code By Umair:  30/12/2020
Comment: Get Working Qty 
*/
function working_qty_avalable($dbcon, $process_id, $process_type, $p_product_id, $p_status, $previous_process_id, $branch_id)
{

    /*$user_type = $_SESSION['user_type'];
    $where_user_wise = '';
    if($user_type!='2'){
    $where_user_wise = 'and ap.resource_id="'.$_SESSION['resource_id'].'"';
}*/
if (!empty($branch_id))
{
    $branch_whre = " and ap.branch_id=" . $branch_id;
}
$sq_l11 = "select ap.*,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 

left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 

left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id 

where ap.p_status in (0,1) and ap.process_id=" . $process_id . " and ap.p_product_id=" . $p_product_id . " " . $branch_whre . " and ap.company_id=" . $_SESSION['company_id'] . " and pr_process_type='$process_type' $where_user_wise";

$q11 = $dbcon->query($sq_l11);
$total_start_qty = 0;
    //echo $sq_l11;
while ($rel_n11 = brp_mysqli_fetch_array($q11))
{

        //$min_machine=$rel_n11['start_qty'];
    $start_qty = $rel_n11['strtt_qty'] - $rel_n11['end_qty'];
    $spending_qty = $rel_n11['pen_qty'];
    if ($start_qty > $spending_qty)
    {
        $start_qty = $spending_qty;
    }
        //$min_machine111=$min_machine111+$min_machine1111;
        //var_dump($start_qty);
    $total_start_qty = $total_start_qty + $start_qty;
}

    /* if($p_status==1){
    $min_machine111=0;$pending_qty=0;
    $sq_l1 = "select ap.*,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap
    
    left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id
    
    left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id
    
    where ap.p_status in (0,1) and ap.process_id=".$process_id." and ap.p_product_id=".$p_product_id." and pr_process_type='$process_type' $where_user_wise";
    
    $q1=$dbcon->query($sq_l1);
    while($rel_n=brp_mysqli_fetch_array($q1)){
    
    $min_machine=$rel_n['start_qty'];
    $min_machine1111=$rel_n['strtt_qty']-$rel_n['end_qty'];
    $pending_qty1=$rel_n['pen_qty'];
    if($min_machine1111>$pending_qty1){
    $min_machine1111=$pending_qty1;
    }
    $pending_qty=$pending_qty+$pending_qty1;
    $min_machine111=$min_machine111+$min_machine1111;
    }
    }
    else  */
        if ($previous_process_id == 0)
        {
            $pending_qty = 0;
            $min_machine111 = 0;
            $min_machine1112 = 0;

            $q1 = $dbcon->query("select * from tbl_allocate_process as ap 
                where ap.process_id=" . $process_id . " and ap.p_product_id=" . $p_product_id . " " . $branch_whre . " and ap.company_id=" . $_SESSION['company_id'] . " and ap.p_status in (0,1) and pr_process_type='$process_type'");

            while ($rel_n = brp_mysqli_fetch_array($q1))
            {

                $machine_make = array();
                $min_machine1112 = 0;
                $q12 = $dbcon->query("select * from tbl_request_product as ap 
                 where status=0 and perent_id=" . $rel_n['p_ref_id']);
                while ($rel_n1 = brp_mysqli_fetch_array($q12))
                {

                    $o_qty = $rel_n1['req_qty_one'];
                    $o_qty = $o_qty;
                    $required_qty = $rel_n['p_qty'] * $o_qty;

                    $required_qty = $required_qty;

                    $cur_stock = reserve_stock($dbcon, $rel_n1['rp_pid'], $rel_n1['purchase_unit'], "", $rel_n1['rp_id'], "", "", $branch_id);

                    $total = $cur_stock;

                    if ($total < 0)
                    {
                        $total = 0;
                    }
                    if ($total > $required_qty)
                    {
                        $usable = $required_qty;
                    }
                    else
                    {
                        $usable = $total / $o_qty;
                        $usable = $usable * $o_qty;
                    }
                    $chkp = $usable / $o_qty;
                $machine_make[] = $chkp; //number_format($chkp,4,".",""); code by umair
                $min_machine = min($machine_make);
                $min_machine1111 = $min_machine;

                $pending_qty1 = $rel_n['pen_qty'];
                if ($min_machine1111 > $pending_qty1)
                {
                    $min_machine1111 = $pending_qty1;
                }
                if ($min_machine1111 != $rel_n['pen_qty'])
                {

                    $min_machine1111 = $min_machine1111; //floor($min_machine1111); // $pending_qty1; code change by umair : 09/12/
                    
                }
            }
            $pending_qty = $pending_qty + $rel_n['pen_qty'];
            $min_machine1112 = $min_machine1112 + $min_machine1111;
            if ($min_machine1112 > $pending_qty)
            {
                $min_machine1112 = $pending_qty;
            }
            $min_machine111 = $min_machine111 + $min_machine1112;
        }
    }
    else
    {

        $min_machine111 = 0;
        $pending_qty = 0;
        $q1 = $dbcon->query("select * from tbl_allocate_process as ap 
           where ap.process_id=" . $process_id . " and ap.p_product_id=" . $p_product_id . " and ap.p_status in (0,1) " . $branch_whre . " and ap.company_id=" . $_SESSION['company_id'] . "  and pr_process_type='$process_type' ");
        while ($rel_n = brp_mysqli_fetch_array($q1))
        {

            $q22 = "select * from tbl_allocate_process as bt 
            where bt.p_id=" . $rel_n['previous_process_id'];
            $q23 = $dbcon->query($q22);
            $row12 = brp_mysqli_fetch_array($q23);

            $min_machine = $row12['process_stock'] - $row12['process_used_stock'];
            $min_machine1111 = $min_machine;
            //$pending_qty11=$min_machine;
            $pending_qty1 = $rel_n['pen_qty'];
            if ($min_machine1111 > $pending_qty1)
            {
                $min_machine1111 = $pending_qty1;
            }
            $pending_qty = $pending_qty + $pending_qty1;
            $min_machine111 = $min_machine111 + $min_machine1111;
        }
    }
    $min_machine111 = $min_machine111 - $total_start_qty;
    return $min_machine111;
    //return $total_start_qty;
    
}

/*
Code By Umair:  2/01/2021
Comment: Get Item Rate At Purchase Time. First we are getting the rate from the quotation table, if not exist then we are checking the tbl_product_party_purchase table later we are getting the that particular party rate.
*/
function get_product_rate_at_purchase_time($dbcon, $vendor_id, $product_id)
{
    $que_po = "select min(party_rate) as mrate from tbl_product_party_purchase where party_product=" . $product_id . " and company_id = '" . $_SESSION['company_id'] . "'";
    $resi = $dbcon->query($que_po);
    $re_po = brp_mysqli_fetch_assoc($resi);

    $que_po1 = "select party_rate from tbl_product_party_purchase where party_id=" . $vendor_id . " and party_product=" . $product_id . " and company_id = '" . $_SESSION['company_id'] . "' order by party_purchase_id desc limit 1 ";
    $resi1 = $dbcon->query($que_po1);
    $re_po1 = brp_mysqli_fetch_assoc($resi1);

    $query_used = "select quo.product_rate from tbl_purchasetrntemp as rpro 
    left join po_quotation as quo on quo.po_quotation_id=rpro.po_quotation_id
    where purchaseordertrn_status=0 and po_trn_req_status=0 and rpro.po_quotation_id!=0 and rpro.product_id=" . $product_id;
    $rel_used = mysqli_fetch_assoc($dbcon->query($query_used));

    $pr_rate = 0;
    if (!empty($rel_used['product_rate']))
    {
        $pr_rate = $rel_used['product_rate'];
    }
    else
    {
        if (!empty($re_po1['party_rate']))
        {
            $pr_rate = $re_po1['party_rate'];
        }
        else
        {
            $pr_rate = $re_po['mrate'];
        }
    }
    return $pr_rate;
}

/*
Code By Umair:  2/01/2021
Comment: Get Item Rate At Bill Time. First we are getting the rate from the tbl_purchaseordertrn, if not exist then we are checking the tbl_product_party_purchase table later we are getting the that particular party rate.
*/
function get_product_rate_at_purchase_billing_time($dbcon, $vendor_id, $product_id)
{
    $que_po = "select min(party_rate) as mrate from tbl_product_party_purchase where party_product=" . $product_id . " and company_id = '" . $_SESSION['company_id'] . "'";
    $resi = $dbcon->query($que_po);
    $re_po = brp_mysqli_fetch_assoc($resi);

    $que_po1 = "select party_rate from tbl_product_party_purchase where party_id = '" . $vendor_id . "' and party_product = '" . $product_id . "' and company_id = '" . $_SESSION['company_id'] . "' order by party_purchase_id desc limit 1 ";
    $resi1 = $dbcon->query($que_po1);
    $re_po1 = brp_mysqli_fetch_assoc($resi1);

    $pr_rate = 0;

    $query = "select product_rate from tbl_purchaseordertrn where product_id = '" . $product_id . "' and company_id = '" . $_SESSION['company_id'] . "' ";
    $result = $dbcon->query($query);
    $count = brp_mysqli_num_rows($result);

    if ($count > 0)
    {
        $row = brp_mysqli_fetch_assoc($result);
        $pr_rate = $row['product_rate'];
    }
    else
    {
        if (!empty($re_po1['party_rate']))
        {
            $pr_rate = $re_po1['party_rate'];
        }
        else
        {
            $pr_rate = $re_po['mrate'];
        }
    }

    return $pr_rate;
}

//pathik end
// pathik date : 16-12-2020
function job_card_entry_show($dbcon, $rp_id)
{
    $bom1 = "SELECT rpro.*,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM `tbl_request_product` as rpro
    left join product_mst as pro on pro.product_id=rpro.rp_pid
    left join unit_mst as bunit on bunit.unitid=rpro.process_unit
    left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
    WHERE rpro.status in (0,3) AND rpro.perent_id=" . $rp_id;
    $result = $dbcon->query($bom1);
    while ($rel = mysqli_fetch_assoc($result))
    {
        if ($rel['status'] == 3)
        {
            $request_button = '<a class="btn btn-primary dispbtn" data-original-title="" id="reqest_btn' . $rel["rp_id"] . '" data-toggle="tooltip" data-placement="top" onclick="add_product_request(' . $rel["rp_id"] . ');" ><i class="fa fa-paper-plane"></i> Request</a>';
        }
        else
        {
            $request_button = '<a class="btn btn-danger dispbtn" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
        }
        $bom2 = "SELECT status,main_request,rp_req_qty,in_process_qty FROM `tbl_request_product` WHERE status!=2 AND rp_id=" . $rel['perent_id'];
        $bom_rel2 = mysqli_fetch_assoc($dbcon->query($bom2));
        if ($bom_rel2['main_request'] != "1")
        {
            if ($bom_rel2['status'] == "3")
            {
                $request_button = "";
            }
            else
            {
            }
        }
        $cstock = get_current_stock_new($dbcon, $rel["rp_pid"], $rel["purchase_unit"]);
        $rstock = reserve_stock($dbcon, $rel["rp_pid"], $rel["purchase_unit"]);
        $actualstock = $cstock - $rstock;
        if ($rel["status"] == 0)
        {
            $reserv_read_only = "readonly";
            $po_read_only = "readonly";
            $process_read_only = "readonly";
            $req_read_only = "readonly";
            $req_qty = $rel['rp_req_qty'];
        }
        else
        {
            $reserv_read_only = "";
            $po_read_only = "";
            $process_read_only = "";
            $req_read_only = "";

            if ($bom_rel2['in_process_qty'] != 0)
            {
                $req_qty = $bom_rel2['in_process_qty'] * $rel["req_qty_one"];
            }
            else
            {
                $req_qty = $bom_rel2['rp_req_qty'] * $rel["req_qty_one"];
            }
            $req_qty = round($req_qty, 4);

            if ($actualstock <= 0)
            {
                $reserv_read_only = "readonly";
            }
        }
        $pr_setting_arr = explode(",", $rel['product_setting_check']);
        if ($rel["status"] != 0)
        {
            $pr_setting_arr = explode(",", $rel['product_setting_check']);
        }
        else
        {
            $process_qty = $rel["in_process_qty"];
            $po_qty = $rel["rp_po_qty"];
        }

        if (in_array("process_product", $pr_setting_arr))
        {
            $process_read_only = "";
            $process_qty = $req_qty;
            $po_qty = "";
        }
        else
        {
            $process_read_only = "readonly";
            $process_qty = "";
            $po_qty = $req_qty;
        }

        //if(in_array("process_product",$pr_setting_arr))
        if ($rel['in_process_qty'] > 0)
        {
            $process_button = '<a class="btn btn-success" data-original-title="" id="reqest_btn' . $rel["rp_id"] . '" data-toggle="tooltip" data-placement="top" onclick="view_process(' . $rel["rp_id"] . ');" ><!--<i class="fa fa-paper-plane"></i>--> View </a>';
        }
        else
        {
            $process_button = "";
        }

        $po_qty_sh = "'po_qty'";
        $req_qty_sh = "'req_qty'";
        $res_qty_sh = "'res_qty'";
        $process_qty_sh = "'process_qty'";
        $str .= '<tr>
        <td>' . $rel["sr_no"] . '</td>
        <td>' . $rel["product_name"] . '</td>
        <td>' . $rel["product_min_stock"] . '</td>
        <td>
        <input type="number" min="0" class="form-control" name="current_stock' . $rel["rp_id"] . '" id="current_stock' . $rel["rp_id"] . '" onkeypress="return isNumberKey(event)"  value="' . $actualstock . '" readonly />
        </td>
        <td>
        <div class="col-md-9" >
        <input type="number" min="0" class="form-control" name="req_qty' . $rel["rp_id"] . '" id="req_qty' . $rel["rp_id"] . '" onkeypress="return isNumberKey(event)" onkeyup="error_check(' . $rel["rp_id"] . ',' . $req_qty_sh . ')"  value="' . $req_qty . '"  ' . $req_read_only . ' />

        <input type="hidden" name="req_qty_one' . $rel["rp_id"] . '" id="req_qty_one' . $rel["rp_id"] . '" value="' . $rel["req_qty_one"] . '" />

        <input type="hidden" name="basic_req_qty' . $rel["rp_id"] . '" id="basic_req_qty' . $rel["rp_id"] . '" value="' . $req_qty . '" />

        <span style="display:none;" class="error" id="req_qty_err' . $rel["rp_id"] . '" ></span>
        </div>
        <div class="col-md-2">
        <strong>' . $rel["conv_unit_name"] . '</strong>
        </div>
        </td>
        <td>
        <input type="number" min="0" class="form-control" name="res_qty' . $rel["rp_id"] . '" id="res_qty' . $rel["rp_id"] . '" onkeypress="return isNumberKey(event)" onkeyup="error_check(' . $rel["rp_id"] . ',' . $res_qty_sh . ')" value="' . $rel["reserve_stock"] . '" ' . $reserv_read_only . ' />
        <span style="display:none;" class="error" id="res_qty_err' . $rel["rp_id"] . '" ></span>
        </td>
        <td>
        <div class="col-md-9">
        <input type="number" min="0" class="form-control" name="process_qty' . $rel["rp_id"] . '" id="process_qty' . $rel["rp_id"] . '" onkeyup="error_check(' . $rel["rp_id"] . ',' . $process_qty_sh . ')" onkeypress="return isNumberKey(event)"  value="' . $process_qty . '" ' . $process_read_only . ' />

        <span style="display:none;" class="error" id="process_qty_err' . $rel["rp_id"] . '" ></span>
        </div>
        <div class="col-md-2">
        <strong>' . $rel["base_unit_name"] . '</strong>
        </div>
        </td>
        <td>
        <div class="col-md-9" >
        <input type="number" min="0" class="form-control" name="po_qty' . $rel["rp_id"] . '" id="po_qty' . $rel["rp_id"] . '" onkeypress="return isNumberKey(event)" onkeyup="error_check(' . $rel["rp_id"] . ',' . $po_qty_sh . ')"  value="' . $po_qty . '" ' . $po_read_only . ' />

        <span style="display:none;" class="error" id="po_qty_err' . $rel["rp_id"] . '" ></span>
        </div>
        <div class="col-md-2">
        <strong>' . $rel["conv_unit_name"] . '</strong>
        </div>
        </td>
        <td class="action' . $rel["rp_id"] . '">' . $request_button . ' ' . $process_button . '</td>
        </tr>';

        $str .= job_card_entry_show($dbcon, $rel["rp_id"]);
    }
    return $str;
}
//pathik end
function start_qty_avalable($dbcon, $process_id, $process_type, $product_id, $p_id, $branch_id)
{
    if (!empty($product_id))
    {
        $ser = " and ap.p_product_id=" . $product_id;
    }
    if (!empty($p_id))
    {
        $p_id_val = " and ap.p_id=" . $p_id;
    }

    /*$user_type = $_SESSION['user_type'];
    $where_user_wise = '';
    if($user_type!='2'){
    $where_user_wise = 'and ap.resource_id="'.$_SESSION['resource_id'].'"';
}*/
if (!empty($branch_id))
{
    $where_branch = " and ap.branch_id=" . $branch_id;
}
$q = $dbcon->query("select ap.*,sum(ap.p_qty) as ap_qty,sum(ap.pen_qty) as apen_qty,p.product_type,p.product_name,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 
    left join product_mst as p on p.product_id=ap.p_product_id 
    left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
    left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id 
    where ap.process_id=" . $process_id . " " . $ser . " and ap.p_status IN(0,1) " . $p_id_val . " and pr_process_type='$process_type' " . $where_branch . " and ap.company_id=" . $_SESSION['company_id'] . " group by ap.p_product_id");

$cnt = 1;
$datacheck = "";
while ($rel = brp_mysqli_fetch_array($q))
{
    $pid = $rel['p_product_id'];

    $where = '';
    if ($rel['p_status'] == 1)
    {
        $min_machine111 = 0;
            /*$q1=$dbcon->query("select ap.*,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap
            
            left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id
            
            left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id
            
            where ap.process_id=".$process_id." ".$p_id_val." and ap.p_product_id=".$rel['p_product_id']." and ap.p_status=1  and pr_process_type='$process_type'" );*/
            $q1 = $dbcon->query("select ap.*,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 

              left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 

              left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id 

              where ap.process_id=" . $process_id . " " . $p_id_val . " and ap.p_product_id=" . $rel['p_product_id'] . " and pr_process_type='$process_type' " . $where_branch . " and ap.company_id=" . $_SESSION['company_id'] . " ");
            while ($rel_n = mysqli_fetch_array($q1))
            {

                $min_machine = $rel_n['start_qty'];
                $min_machine1111 = $rel_n['strtt_qty'] - $rel_n['end_qty'];
                $pending_qty1 = $rel_n['pen_qty'];
                if ($min_machine1111 > $pending_qty1)
                {
                    $min_machine1111 = $pending_qty1;
                }
                $pending_qty = $pending_qty + $pending_qty1;
                $min_machine111 = $min_machine111 + $min_machine1111;
            }

            //var_dump($min_machine111);
            
        }
        else if ($rel['previous_process_id'] == 0)
        {
            $pending_qty = 0;
            $min_machine111 = 0;
            $machine_make_new = array();
            $q1 = $dbcon->query("select * from tbl_allocate_process as ap 
              where ap.process_id=" . $process_id . " and ap.p_product_id=" . $rel['p_product_id'] . " " . $p_id_val . " and ap.p_status=0  and pr_process_type='$process_type' " . $where_branch . " and ap.company_id=" . $_SESSION['company_id'] . " ");
            while ($rel_n = mysqli_fetch_array($q1))
            {
                $min_machine1112 = 0;
                $machine_make = array();
                $q12 = $dbcon->query("select * from tbl_request_product as ap 
                   where status=0 and perent_id=" . $rel_n['p_ref_id']);
                while ($rel_n1 = mysqli_fetch_array($q12))
                {

                    //$o_qty=convert_stock($dbcon,$rel_n1['req_qty_one'],$rel_n1['rp_id'],"base_unit");
                    //var_dump($o_qty);
                    $o_qty = $rel_n1['req_qty_one'];
                    //var_dump($o_qty);
                    /*
                    Code By Umair: 09/12/2020
                    Commnet: Round function is commneted to solve the real value
                    */
                    //$o_qty=round($o_qty,6);
                    $o_qty = $o_qty;

                    $required_qty = $rel_n['p_qty'] * $o_qty;
                    //var_dump($required_qty);
                    /*
                    Code By Umair: 09/12/2020
                    Commnet: Round function is commneted to solve the real value
                    */
                    //$required_qty=round($required_qty,4);
                    $required_qty = $required_qty;

                    //var_dump($required_qty);
                    //$cur_stock=reserve_stock($dbcon,$row['product_id'],$row['product_base_unit']);
                    $cur_stock = reserve_stock($dbcon, $rel_n1['rp_pid'], $rel_n1['purchase_unit'], "", $rel_n1['rp_id'], "", "", $branch_id);

                    //$cur_stock=reserve_stock($dbcon,$rel_n1['rp_pid'],$rel_n1['process_unit'],"",$rel_n1['rp_id']);
                    //var_dump($rel_n1['rp_id']);
                    //var_dump($cur_stock);
                    $total = $cur_stock;

                    if ($total > $required_qty)
                    {
                        $usable = $required_qty;
                    }
                    else
                    {

                        //var_dump($total."===".$o_qty);    //$usable=round(($total/$rel_n1['req_qty_one']),0,PHP_ROUND_HALF_DOWN);
                        //$usable=round(($total/$o_qty),0,PHP_ROUND_HALF_DOWN);
                        $usable = $total / $o_qty;
                        //var_dump($total);
                        //var_dump($o_qty);
                        //var_dump($total/$rel_n1['req_qty_one']);
                        //$usable=$usable*$rel_n1['req_qty_one'];
                        $usable = $usable * $o_qty;
                    }
                    //var_dump($usable);
                    //var_dump($total);
                    //$machine_make[]=round(($usable/$o_qty),0,PHP_ROUND_HALF_DOWN);
                    $chkp = $usable / $o_qty;

                    /*
                    Code By Umair: 09/12/2020
                    Commnet: number_format function is commneted to solve the real value
                    */
                    //$machine_make[]=number_format($chkp,4,".","");
                    $machine_make[] = $chkp;

                    //$machine_make[]=round(($usable/$rel_n1['req_qty_one']),0,PHP_ROUND_HALF_DOWN);
                    $min_machine = min($machine_make);
                    $min_machine1111 = $min_machine;

                    $pending_qty1 = $rel_n['pen_qty'];
                    //var_dump($pending_qty1);
                    if ($min_machine1111 > $pending_qty1)
                    {
                        $min_machine1111 = $pending_qty1;
                    }

                    if ($min_machine1111 != $rel_n['pen_qty'])
                    {
                        /*
                        Code By Umair: 09/12/2020
                        Commnet: floor function is commneted to solve the real value
                        */
                        //$min_machine1111=floor($min_machine1111);
                        $min_machine1111 = $min_machine1111; // $pending_qty1;// code by umair : 09/12/2020
                        
                    }
                    //var_dump($min_machine1111);
                    
                }
                $pending_qty = $pending_qty + $rel_n['pen_qty'];
                $min_machine1112 = $min_machine1112 + $min_machine1111;
                //$machine_make_new[]=$min_machine1111;
                //$min_machine1=min($machine_make_new);
                //$min_machine1112=$min_machine1;
                if ($min_machine1112 > $pending_qty)
                {
                    $min_machine1112 = $pending_qty;
                }
                $min_machine111 = $min_machine111 + $min_machine1112;
                //var_dump($min_machine111);
                
            }
        }
        else
        {
            $min_machine111 = 0;
            $q1 = $dbcon->query("select * from tbl_allocate_process as ap 
                where ap.process_id=" . $process_id . " " . $p_id_val . " and ap.p_product_id=" . $rel['p_product_id'] . " and ap.p_status=0  and pr_process_type='$process_type' " . $where_branch . " and ap.company_id=" . $_SESSION['company_id'] . "");
            while ($rel_n = mysqli_fetch_array($q1))
            {

                $q22 = "select * from tbl_allocate_process as bt 
                where bt.p_id=" . $rel_n['previous_process_id'];
                $q23 = $dbcon->query($q22);
                $row12 = brp_mysqli_fetch_array($q23);

                $min_machine = $row12['process_stock'] - $row12['process_used_stock'];
                //var_dump($min_machine);
                $min_machine1111 = $min_machine;
                //$pending_qty11=$min_machine;
                $pending_qty1 = $rel_n['pen_qty'];
                if ($min_machine1111 > $pending_qty1)
                {
                    $min_machine1111 = $pending_qty1;
                }
                $pending_qty = $pending_qty + $pending_qty1;
                $min_machine111 = $min_machine111 + $min_machine1111;
            }
            //var_dump($min_machine111);
            
        }
    }

    return round($min_machine111, 2);
    //echo "11";
    
}
function count_working_process_qty($dbcon, $id, $type)
{
    if (!empty($_SESSION['branch_id']))
    {
        $where_branch = " and ap.branch_id=" . $_SESSION['branch_id'];
    }

    $is_available = count_process_qty($dbcon, $id, $type);
    $p_qty = 0;
    if ($is_available > 0)
    {
        /*$user_type = $_SESSION['user_type'];
        $where_user_wise = '';
        if($user_type!='2'){
        $where_user_wise = 'and resource_id="'.$_SESSION['resource_id'].'"';
    }*/

    $q = $dbcon->query("select ap.*,p.product_type,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 
       left join product_mst as p on p.product_id=ap.p_product_id 
       left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
       left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id
       where ap.p_status IN (0,1) and process_id=" . $id . " " . $where_branch . " and ap.company_id=" . $_SESSION['company_id'] . " and pr_process_type='$type' ");

        //$dddd="select ap.*,p.product_type from tbl_allocate_process as ap left join product_mst as p on p.product_id=ap.p_product_id where ap.p_status IN (0,1) and pr_process_type='$type'";
    while ($rel = brp_mysqli_fetch_array($q))
    {
        $pid = $rel['p_product_id'];

        $where = '';
            //$pp=$rel['product_type'];
        if ($rel['product_type'] == 0)
        {
            $where .= " and parent_id = '0' and sale_product_id='$pid'";
        }
        else
        {
            $where .= " and parent_id = (select bom_trn_id from tbl_bomtrn where product_id='$pid' order by bom_trn_id desc limit 0,1)";
        }
        if ($rel['p_status'] == 1)
        {
                //$unused=$rel['p_qty']-$rel['start_qty'];
                //$min_machine=$rel['pen_qty']-$unused;
                //$pending_qty=$rel['pen_qty']-$unused;
                //$min_machine=$rel['strtt_qty']-$rel['end_qty'];
                //$pending_qty=$rel['strtt_qty']-$rel['end_qty'];
            $min_machine = $rel['start_qty'];
            $min_machine111 = $rel['strtt_qty'] - $rel['end_qty'];
            $pending_qty = $rel['pen_qty'];
            if ($min_machine111 > $pending_qty)
            {
                $min_machine111 = $pending_qty;
            }
        }
        else if ($rel['previous_process_id'] == 0)
        {
            $cur_stock = 0;
            $machine_make = array();
            $q12 = $dbcon->query("select * from tbl_request_product as ap 
             where status=0 " . $where_branch . " and ap.company_id=" . $_SESSION['company_id'] . " and perent_id=" . $rel['p_ref_id']);
            while ($rel_n1 = mysqli_fetch_array($q12))
            {

                    //$o_qty=convert_stock($dbcon,$rel_n1['req_qty_one'],$rel_n1['rp_id'],"base_unit");
                $o_qty = $rel_n1['req_qty_one'];
                $required_qty = $rel['p_qty'] * $o_qty;
                    //var_dump($required_qty);
                    //$cur_stock=reserve_stock($dbcon,$row['product_id'],$row['product_base_unit']);
                    //$cur_stock=reserve_stock($dbcon,$rel_n1['rp_pid'],$rel_n1['process_unit'],"",$rel_n1['rp_id']);
                $cur_stock = reserve_stock($dbcon, $rel_n1['rp_pid'], $rel_n1['purchase_unit'], "", $rel_n1['rp_id']);
                    //var_dump($cur_stock);
                $total = $cur_stock;
                if ($total < 0)
                {
                    $total = 0;
                }
                if ($total > $required_qty)
                {
                    $usable = $required_qty;
                }
                else
                {
                        //var_dump($total."===".$o_qty);    //$usable=round(($total/$rel_n1['req_qty_one']),0,PHP_ROUND_HALF_DOWN);
                        //$usable=round(($total/$o_qty),0,PHP_ROUND_HALF_DOWN);
                    $usable = $total / $o_qty;
                        //var_dump($total/$rel_n1['req_qty_one']);
                        //$usable=$usable*$rel_n1['req_qty_one'];
                    $usable = $usable * $o_qty;
                }
                $chkp = $usable / $o_qty;

                    /*
                    Code By Umair: 09/12/2020
                    Commnet: number_format function is commneted to solve the real value
                    */
                    //$machine_make[]=number_format($chkp,4,".","");
                    $machine_make[] = $chkp;

                    $min_machine = min($machine_make);
                    //var_dump($min_machine);
                    $min_machine111 = $min_machine;
                    //var_dump($min_machine111);
                    $pending_qty = $rel['pen_qty'];

                    if ($min_machine111 > $pending_qty)
                    {
                        $min_machine111 = $pending_qty;
                    }
                    //var_dump($min_machine111);
                    
                }

                /* $q1="select itm.product_id,itm.product_type,itm.product_purchase_rate,itm.product_name,itm.product_min_stock,itm.product_opening,bt.product_act_qty,u.unit_name,IFNULL(qcqty,0),bt.product_base_unit,(bt.product_act_qty/bt.product_base_qty) as bom_qty from tbl_bomtrn as bt
                left join product_mst as itm on itm.product_id=bt.product_id
                left join unit_mst as u on u.unitid=bt.product_base_unit
                left join (select sum(qc.qc_accepted) as qcqty,qc.qc_product
                from tbl_qc_trn as qc where qc_status=0 group by qc.qc_product) as qcd on qcd.qc_product=bt.product_id
                where bt.bom_trn_status=0 ".$where."";
                $q2=$dbcon->query($q1);
                $machine_make=array();
                $aao="";
                while($row=brp_mysqli_fetch_array($q2))
                {
                //$required_qty=$rel['p_qty']*$row['product_act_qty'];
                $required_qty=$rel['p_qty']*$row['bom_qty'];
                
                $ri1="select rp_id from tbl_request_product as ap
                where ap.perent_id=".$rel['p_ref_id']." and rp_pid=".$row['product_id'];
                $ri11=$dbcon->query($ri1);
                $r221=brp_mysqli_fetch_array($ri11);
                
                $cur_stock=reserve_stock($dbcon,$row['product_id'],$row['product_base_unit'],"",$r221['rp_id']);
                //echo $cur_stock;
                $pp=$row['product_id'];
                
                if($cur_stock<0){
                //$aao=$aao."+".$cur_stock."(".$row['product_id'].")";
                $cur_stock=0;
                }
                
                $total=$cur_stock;
                if($total>$required_qty)
                {
                $usable=$required_qty;
                
                }
                else
                {
                //$usable=round(($total/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
                $usable=round(($total/$row['bom_qty']),0,PHP_ROUND_HALF_DOWN);
                //$usable=$usable*$row['product_act_qty'];
                $usable=$usable*$row['bom_qty'];
                //$usable22=$usable22+$usable;
                }
                
                //$machine_make[]=round(($usable/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
                $machine_make[]=round(($usable/$row['bom_qty']),0,PHP_ROUND_HALF_DOWN);
                }
                
                $min_machine=min($machine_make);
                
                $min_machine111=$min_machine;
                $pending_qty=$rel['pen_qty'];
                if($min_machine111>$pending_qty){
                $min_machine111=$pending_qty;
                }
                */
            }
            else
            {
                /* $q22="select * from tbl_allocate_process as bt
                where bt.p_id=".$rel['previous_process_id'];
                $q23=$dbcon->query($q22);
                $row12=brp_mysqli_fetch_array($q23);
                
                $min_machine=$row12['process_stock']-$row12['process_used_stock'];
                $pending_qty=$min_machine; */

                $q22 = "select * from tbl_allocate_process as bt 
                where bt.p_id=" . $rel['previous_process_id'];
                $q23 = $dbcon->query($q22);
                $row12 = brp_mysqli_fetch_array($q23);

                $min_machine = $row12['process_stock'] - $row12['process_used_stock'];
                $min_machine111 = $min_machine;
                //$pending_qty11=$min_machine;
                $pending_qty = $rel['pen_qty'];
                if ($min_machine111 > $pending_qty)
                {
                    $min_machine111 = $pending_qty;
                }
            }
            //$sho=$sho."n".$min_machine."-".$pp;
            //$sho=$sho."nnnnn".$q1;
            $p_qty += $min_machine111;
        }
        return round($p_qty, 2);
        //return $dddd;
        //return $sho;
        
    }
    else
    {
        return round($p_qty, 2);
    }

    //$total=$rel['sqty']-$rel['stqty'];
    //return $total;
    
}
// Amish Soni Start 29-12-2020
function getEmailSMSTemplate($dbcon, $module_id, $task_id = '', $stage_id = '')
{
    $query = "SELECT * FROM email_sms_template WHERE email_module_id = $module_id 
    AND status = '0' AND company_id = '" . $_SESSION['company_id'] . "'";
    if ($task_id)
    {
        $query .= " AND task_id = $task_id";
    }
    if ($stage_id)
    {
        $query .= " AND stage_id = $stage_id";
    }
    $query .= " ORDER BY email_sms_id DESC LIMIT 1";
    // p($query);
    $q = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($q);
    return $row;
}

function getCustDetailById($dbcon, $id)
{
    $query = "SELECT * FROM tbl_customer WHERE cust_status = 0 AND cust_id = $id";
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel;
}

function getUserDetailById($dbcon, $id)
{
    $query = "SELECT * FROM users WHERE active = 0 AND user_id = $id";
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel;
}
// Amish Soni End 29-12-2020
//pathik start 2-01-2021
function jobcard_save_permission($dbcon, $id, $count)
{
    $queryw = "select status,rp_id from tbl_request_product where perent_id=" . $id;
    $rs_custw = $dbcon->query($queryw);
    while ($relw = brp_mysqli_fetch_array($rs_custw))
    {
        if ($relw['status'] == "3")
        {
            $count++;
        }
        jobcard_save_permission($dbcon, $relw['rp_id'], $count);
    }
    return $count;
}
function req_cancel_per($dbcon, $rp_id)
{
    $queryw = "select rp_id from tbl_request_product where status=0 and perent_id=" . $rp_id;
    $rs_custw = $dbcon->query($queryw);
    $ind_per = 0;
    $pro_per = 0;
    $tind_per = 0;
    $tpro_per = 0;
    while ($relw = brp_mysqli_fetch_array($rs_custw))
    {
        $queryw2 = "select approve_indent_id from approve_indent where approve_indent_status=0 and rp_id=" . $relw['rp_id'];
        $rs_custw2 = $dbcon->query($queryw2);
        $relw2 = brp_mysqli_fetch_array($rs_custw2);
        if (!empty($relw2['approve_indent_id']))
        {
            $ind_per = "1";
        }

        $queryw1 = "select p_id from tbl_allocate_process where p_status!=2 and p_ref_id=" . $relw['rp_id'];
        $rs_custw1 = $dbcon->query($queryw1);
        $relw1 = brp_mysqli_fetch_array($rs_custw1);
        if (!empty($relw1['p_id']))
        {
            $pro_per = "1";
        }
        $tind_per = $tind_per + $ind_per;
        $tpro_per = $tpro_per + $pro_per;
    }

    $sper = $tind_per + $tpro_per;
    if ($sper <= 0)
    {
        $queryw2 = "select approve_indent_id from approve_indent where approve_indent_status=0 and rp_id=" . $rp_id;
        $rs_custw2 = $dbcon->query($queryw2);
        $relw2 = brp_mysqli_fetch_array($rs_custw2);
        if (!empty($relw2['approve_indent_id']))
        {
            $ind_per = "1";
        }

        $queryw1 = "select p_id from tbl_allocate_process where p_status!=2 and p_ref_id=" . $rp_id;
        $rs_custw1 = $dbcon->query($queryw1);
        $relw1 = brp_mysqli_fetch_array($rs_custw1);
        if (!empty($relw1['p_id']))
        {
            $pro_per = "1";
        }
    }
    else
    {
    }
    $per = $ind_per + $pro_per;
    if ($per > 0)
    {
        //return 1;

    }
    else
    {
        //return 0;

    }

    //echo $queryw;
    
}
//pathik end 2-01-2021
function getTemplateName($dbcon, $temp_id)
{
    $template_record = '';
    $qry = "SELECT `id`,`template_name` FROM `template_access_permission` WHERE `status` = 0 and company_id = '" . $_SESSION['company_id'] . "' order by template_name";
    $template_name = $dbcon->query($qry);
    $template_record = '<option value="">SELECT TEMPLATE NAME</option>';
    while ($row = brp_mysqli_fetch_assoc($template_name))
    {
        $sel = '';
        if ($row['id'] == $temp_id)
        {
            $sel = 'selected="selected"';
        }
        $template_record .= '<option ' . $sel . ' value="' . $row['id'] . '">' . $row['template_name'] . '</option>';
    }
    return $template_record;
}

// Amish Soni Start 06-01-2021
function getSupportDetail($dbcon, $id)
{
    $template_record = '';
    $qry = "SELECT id, name FROM `tbl_support_status_mst` WHERE id >= $id AND `status` = 0";
    $template_name = $dbcon->query($qry);
    while ($row = brp_mysqli_fetch_assoc($template_name))
    {
        if ($_SESSION['user_type'] != '2' && brp_strtolower($row['name']) == 'approved')
        {
            continue;
        }
        $sel = '';
        if ($row['id'] == $id)
        {
            $sel = 'selected="selected"';
        }
        $template_record .= '<option ' . $sel . ' value="' . $row['id'] . '">' . $row['name'] . '</option>';
    }
    return $template_record;
}
// Amish Soni End 06-01-2021
// Amish Soni Start 07-01-2021
function getSupportStatusById($dbcon, $id)
{
    $query = "SELECT id, name FROM `tbl_support_status_mst` WHERE `id` = $id";
    $q = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($q);
    return $row;
}
// Amish Soni End 07-01-2021
// Amish Soni Start 08-01-2021
function getSupportById($dbcon, $id)
{
    $query = "SELECT * FROM `tbl_support_ticket` WHERE `id` = $id";
    $q = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($q);
    return $row;
}
// Amish Soni End 08-01-2021
// Amish Soni Start 12-01-2021
function getCompanySettings($dbcon, $id = false)
{
    $query = "SELECT * FROM `tbl_company_settings` WHERE status = 0 
    AND company_id = '" . $_SESSION['company_id'] . "'";

    if ($id)
    {
        $query .= " AND `id` = $id";
    }

    $query .= " ORDER BY id DESC LIMIT 1";

    $q = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($q);
    return $row;
}
function getCompanyConfiguration($dbcon, $id = false)
{
    $query = "SELECT * FROM `tbl_company_configuration` WHERE isdelete=0 and company_id = '" . @$_SESSION['company_id'] . "'";

    if ($id)
    {
        $query .= " AND `company_conf_id` = $id";
    }

     $query .= " ORDER BY company_conf_id DESC LIMIT 1";

    $q = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($q);
    return $row;
}
// Amish Soni End 12-01-2021
// Amish Soni Start 12-01-2021
function getAllTables($dbcon, $id = false)
{
    $str = '';
    $query = "SHOW TABLES";
    $q = $dbcon->query($query);

    while ($rel = brp_mysqli_fetch_assoc($q))
    {
        $database = DB;
        $sel = '';
        if ($rel['Tables_in_' . $database] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['Tables_in_' . $database] . '">' . $rel['Tables_in_' . $database] . '</option>';
    }

    return $str;
}

function getColumnsFromTable($dbcon, $table_name, $id = false)
{
    $str = '';
    $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name'";
    $q = $dbcon->query($query);

    while ($rel = brp_mysqli_fetch_assoc($q))
    {
        $sel = '';
        if ($rel['COLUMN_NAME'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['COLUMN_NAME'] . '">' . $rel['COLUMN_NAME'] . '</option>';
    }

    return $str;
}
// Amish Soni End 12-01-2021
// Amish Soni Start 13-01-2021
function getPKColumnFromTable($dbcon, $table_name)
{
    $query = "SELECT COLUMN_NAME, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_KEY = 'PRI'";
    $q = $dbcon->query($query);

    $rel = brp_mysqli_fetch_assoc($q);

    return $rel['COLUMN_NAME'];
}
// Amish Soni End 13-01-2021
// Amish Soni Start 18-01-2021
function replaceMergeFields($dbcon, $find_str, $pk_id, $module_id)
{
    if (!$find_str)
    {
        return $find_str;
    }

    $query = "SELECT * FROM `email_merge_fields` WHERE company_id = '" . $_SESSION['company_id'] . "' 
    AND status = 0 AND module_id = $module_id";

    $q = $dbcon->query($query);
    $total_records = brp_mysqli_num_rows($q);

    if ($total_records > 0)
    {
        while ($rel = brp_mysqli_fetch_assoc($q))
        {
            $field_name = $rel['field_name'];
            $table_name = $rel['table_name'];
            $column_name = $rel['replace_with'];
            $primary_field = $rel['primary_id'];

            $qry = "SELECT $column_name FROM $table_name WHERE $primary_field = '$pk_id'";
            $q1 = $dbcon->query($qry);
            $row = brp_mysqli_fetch_assoc($q1);

            $searchVal = EMAIL_INSERT_TAG_PREFIX . $field_name . EMAIL_INSERT_TAG_POSTFIX;
            $replaceVal = (isset($row) && $row && isset($row[$column_name]) && $row[$column_name]) ? $row[$column_name] : '';
            $find_str = str_replace($searchVal, $replaceVal, $find_str);
        }
    }

    return $find_str;
}

function getAllEmailSMSTemplate($dbcon, $module_id, $eid = '', $showNone = true)
{
    $query = "SELECT * FROM email_sms_template WHERE email_module_id = $module_id 
    AND status = '0' AND company_id = '" . $_SESSION['company_id'] . "'";

    // p($query);
    $q = $dbcon->query($query);
    $str = '';
    if ($showNone)
    {
        $str .= '<option value="">None</option>';
    }
    while ($rel = brp_mysqli_fetch_assoc($q))
    {
        // $sel = '';
        if ($rel['email_sms_id'] == $eid)
        {
            $sel = "selected='selected'";
        }

        $tmpl_title = $rel['template_title'];
        $title_len = 30;
        $title = (strlen($tmpl_title) > $title_len) ? substr($tmpl_title, 0, $title_len) . "..." : $tmpl_title;

        $str .= '<option ' . $sel . ' value="' . $rel['email_sms_id'] . '">' . $title . '</option>';
    }
    return $str;
}

function getEmailSMSTemplateById($dbcon, $id)
{
    $query = "SELECT * FROM email_sms_template WHERE email_sms_id = $id AND status = '0'";

    // p($query);
    $q = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($q);
    return $row;
}
// Amish Soni End 18-01-2021
//Dimple Panchal 02-01-2021
function get_po_no($dbcon)
{
    $str = '';
    $query = "select * from tbl_pono as est where status=0 and company_id=" . $_SESSION['company_id'];
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Purchase No</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        if ($rel['po_id'] == $eid)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['po_id'] . '">' . $rel['po_no'] . '</option>';
    }
    return $str;
}
//Dimple Panchal end 02-01-2021
//pathik start 13-01-2020
function sub_bom_qty($dbcon, $ids, $type)
{
    $arr = explode(",", $ids);
    //$arr = array(1, 2, 3, 4);
    $p_bom_id = 0;
    $p_qty = 0;
    $p_full_qty = 0;
    foreach ($arr as & $value)
    {
        //$value = $value * 2;
        //echo $value;
        $query = "select * from tbl_bom as est where bom_status!=2 and company_id=" . $_SESSION['company_id'] . " and bom_id =" . $value;
        $rs_dispatch = $dbcon->query($query);
        $rel = mysqli_fetch_assoc($rs_dispatch);

        //while()
        //{
        $product_id = $rel['bom_product'];
        if ($p_bom_id == "0")
        {
            if ($type == "base")
            {
                $p_full_qty = $rel['product_base_qty'];
                //$rr=$value;
                
            }
            else
            {
                $p_full_qty = $rel['product_conv_qty'];
            }
        }
        else
        {
            $set = "select * from tbl_bomtrn where bom_trn_status!=2 and bom_id=" . $p_bom_id . " and p_bom_id=" . $rel['bom_id'];
            $set_head = mysqli_fetch_assoc($dbcon->query($set));
            if ($type == "base")
            {
                $one_qty = $set_head['product_base_qty'] / $p_qty;
                $p_full_qty = $p_full_qty * $one_qty;
            }
            else
            {
                $one_qty = $set_head['product_conv_qty'] / $p_qty;
                $p_full_qty = $p_full_qty * $one_qty;
            }
        }
        $p_bom_id = $rel['bom_id'];
        if ($type == "base")
        {
            $p_qty = $rel['product_base_qty'];
        }
        else
        {
            $p_qty = $rel['product_conv_qty'];
        }
        //}
        
    }
    /* $query="select * from tbl_bom as est where bom_status!=2 and company_id=".$_SESSION['company_id']." and bom_id in (".$ids.")";
    $rs_dispatch=$dbcon->query($query);
    $p_bom_id=0;$p_qty=0;$p_full_qty=0;
    while($rel=brp_mysqli_fetch_assoc($rs_dispatch))
    {
    $product_id=$rel['bom_product'];
    if($p_bom_id=="0"){
    if($type=="base"){
    $p_full_qty=$rel['product_base_qty'];
    }else{
    $p_full_qty=$rel['product_conv_qty'];
    }
    }else{
    $set="select * from tbl_bomtrn where bom_trn_status!=2 and bom_id=".$p_bom_id." and p_bom_id=".$rel['bom_id'];
        $set_head=brp_mysqli_fetch_assoc($dbcon->query($set));
    if($type=="base"){
    $one_qty=$set_head['product_base_qty']/$p_qty;
    $p_full_qty=$p_full_qty*$one_qty;
    }else{
    $one_qty=$set_head['product_conv_qty']/$p_qty;
    $p_full_qty=$p_full_qty*$one_qty;
    }
    }
    $p_bom_id=$rel['bom_id'];
    if($type=="base"){
    $p_qty=$rel['product_base_qty'];
    }else{
    $p_qty=$rel['product_conv_qty'];
    }
} */
return $p_full_qty;
    //return $rr;

}

//pathik end 13-01-2020
function get_customer_master_type($dbcon, $sid)
{
    $qry = "select * from tbl_master_category_detail where mcd_status=0 and mcd_cat_id = '5'";
    $rs_state = $dbcon->query($qry);

    while ($row = brp_mysqli_fetch_assoc($rs_state))
    {
        $sel = '';
        if ($row['mcd_id'] == $sid)
        {
            $sel = 'selected="selected"';
        }
        else
        {
            $sel = "";
        }
        echo '<option ' . $sel . ' value="' . $row['mcd_id'] . '">' . $row['mcd_name'] . '</option>';
    }
}

function reject_request_qty_update($dbcon, $qty, $product_id, $wipstock_id, $unit_id)
{
    //var_dump($qty);
    /*$set11="select rp.*,(reject_qty-reject_request_qty) as pendind_qty from tbl_qc_process_trn as rp
    where rp.qc_process_status=0 and rp.reject_qty>0 and reject_qty>reject_request_qty and rp.product_id=".$product_id;*/
    $set2 = "select * from wip_stock_allocate as rp
    where rp.status=0 and rp.stock_flag=1 and rp.wip_stock_allocate_id=" . $wipstock_id . "";
    $ser2 = $dbcon->query($set2);
    $set_row2 = brp_mysqli_fetch_assoc($ser2);

    $set11 = "select rp.*,(reject_qty-reject_request_qty) as pendind_qty from tbl_qc_process_trn as rp
    where rp.qc_process_status=0 and rp.reject_qty>0 and CAST(reject_qty as DECIMAL(50,2)) > CAST(reject_request_qty as DECIMAL(50,2)) and rp.product_id=" . $product_id . "";
    $ser = $dbcon->query($set11);
    while ($set_row = brp_mysqli_fetch_assoc($ser))
    {
        if ($qty > "0")
        {
            if ($set_row['pendind_qty'] <= $qty)
            {
                $dbcon->query("update tbl_qc_process_trn set reject_request_qty=reject_request_qty+" . $set_row['pendind_qty'] . " where qc_process_trn_id=" . $set_row['qc_process_trn_id']);
                $gqty = $set_row['pendind_qty'];
                $qty = $qty - $set_row['pendind_qty'];
            }
            else
            {
                $dbcon->query("update tbl_qc_process_trn set reject_request_qty=reject_request_qty+" . $qty . " where qc_process_trn_id=" . $set_row['qc_process_trn_id']);
                $gqty = $qty;
                $qty = $qty - $qty;
            }

            $set_pro = "SELECT product_base_unit,product_conv_unit,product_base_qty,product_conv_qty,product_id FROM `product_mst` WHERE product_status=0 AND product_id='" . $product_id . "'";
            $setpro_rel = brp_mysqli_fetch_assoc($dbcon->query($set_pro));

            if ($setpro_rel['product_conv_unit'] == $unit_id)
            {
                $type = "base_unit";
                $con_stock = $gqty;
                $base_stock = convert_stock_new($dbcon, $gqty, $product_id, $type);
            }
            else
            {
                $type = "conv_unit";
                $base_stock = $gqty;
                $con_stock = convert_stock_new($dbcon, $gqty, $product_id, $type);
            }

            $info_wip_deduct['rp_id'] = $set_row2['rp_id'];
            $info_wip_deduct['type_flag'] = 3;
            $info_wip_deduct['po_trn_id'] = 0;
            $info_wip_deduct['sales_order_trn_id'] = 0;
            $info_wip_deduct['allocate_for_rp_id'] = $set_row['p_ref_id'];
            $info_wip_deduct['perent_id'] = $wipstock_id;
            $info_wip_deduct['allocate_base_qty'] = $base_stock;
            $info_wip_deduct['allocate_base_unit'] = $setpro_rel['product_base_unit'];
            $info_wip_deduct['allocate_conv_qty'] = $con_stock;
            $info_wip_deduct['allocate_conv_unit'] = $setpro_rel['product_conv_unit'];
            $info_wip_deduct['stock_flag'] = 2;
            $info_wip_deduct['cdate'] = date("Y-m-d H:i:s");
            $info_wip_deduct['user_id'] = $_SESSION['user_id'];
            $info_wip_deduct['company_id'] = $_SESSION['company_id'];

            $inser_wip_deduct = add_record('wip_stock_allocate', $info_wip_deduct, $dbcon, $row['branch_id']);

            $set_pro_w = "SELECT allocate_base_qty_used,allocate_conv_qty_used FROM `wip_stock_allocate` WHERE wip_stock_allocate_id='" . $info_wip_deduct['perent_id'] . "'";
            $setpro_rel_w = brp_mysqli_fetch_assoc($dbcon->query($set_pro_w));

            $bsto = $setpro_rel_w['allocate_base_qty_used'] + $info_wip_deduct['allocate_base_qty'];
            $csto = $setpro_rel_w['allocate_conv_qty_used'] + $info_wip_deduct['allocate_conv_qty'];

            $query_invoicetype1 = $dbcon->query("UPDATE wip_stock_allocate SET allocate_base_qty_used =" . $bsto . ",allocate_conv_qty_used=" . $csto . " WHERE wip_stock_allocate_id =" . $info_wip_deduct['perent_id']);
        }
    }
}

function count_team_pending_quot_approval($dbcon, $user_id)
{
    $branch_id = ($_SESSION['branch_id'] == 0) ? "" : " AND quot.branch_id = " . $_SESSION['branch_id'];
    $qry = "SELECT count(`quotation_id`) as total_pending_appro FROM `tbl_quotation` as quot, `users` as usr WHERE `approve_status` != 1 and `revise_status`=0 and FIND_IN_SET ('" . $user_id . "',quot.show_user_ids) and FIND_IN_SET ('402',usr.user_access_permission) and quot.company_id =" . $_SESSION['company_id'] . " " . $branch_id . " and quot.quotation_status=0 and usr.user_id = '" . $user_id . "'";

        $qry_rel = brp_mysqli_fetch_assoc($dbcon->query($qry));
        return floatval($qry_rel['total_pending_appro']);
    }

    function count_user_pending_quot_approval($dbcon, $user_id)
    {
        $branch_id = ($_SESSION['branch_id'] == 0) ? "" : " AND quot.branch_id = " . $_SESSION['branch_id'];
        $qry = "SELECT count(`quotation_id`) as total_pending_appro FROM `tbl_quotation` as quot, `users` as usr WHERE `approve_status` != 1 and `revise_status`=0 and `quot`.`user_id` = '" . $user_id . "' and FIND_IN_SET ('402',usr.user_access_permission) and quot.company_id = " . $_SESSION['company_id'] . " " . $branch_id . " and quot.quotation_status=0 and usr.user_id = '" . $user_id . "'";

        $qry_rel = brp_mysqli_fetch_assoc($dbcon->query($qry));
        return floatval($qry_rel['total_pending_appro']);
    }

// Dimple Panchal start: 28-january-2021
    function get_group_legder($dbcon, $groupID, $start_date, $end_date)
    {
    //get all ledgers of groups
        $sub_ledger_qry = "SELECT l_id FROM `tbl_ledger` WHERE l_status = 0 AND l_group IN (" . $groupID . ")";
            $result = mysqli_query($dbcon, $sub_ledger_qry);
            $sub_ledger_array = mysqli_fetch_all($result, MYSQLI_ASSOC);

    //get group name
            $group_name = $dbcon->query("SELECT g_name group_name FROM `tbl_group` WHERE `g_id` = " . $groupID)->fetch_object()->group_name;

            $amount = 0;
            foreach ($sub_ledger_array as $sub_ledger)
            {
                $ca_qry = "select sum(opn_balance) as opening_balance,balance_typeid,
                sum(debitamount) as debitamount ,sum(creditamount) as creditamount
                from tbl_ledger as cust 
                left join (select sum(amount) as debitamount,invoice.ledger_id 
                from tbl_general_book as invoice 
                where genral_book_status=0 and table_name!='tbl_ledger' 
                and entry_type= 2 and invoice.company_id=" . $_SESSION['company_id'] . " 
                and ref_date < '" . date('Y-m-d', strtotime($start_date)) . "' 
                group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 
                left join (select sum(amount) as creditamount,rec.ledger_id 
                from tbl_general_book as rec 
                where genral_book_status= 0 and table_name!='tbl_ledger' 
                and entry_type= 1 and company_id=" . $_SESSION['company_id'] . "
                and ref_date < '" . date('Y-m-d', strtotime($start_date)) . "' 
                group by rec.ledger_id) as creditcust on creditcust.ledger_id = cust.l_id 
                where l_status = 0 AND company_id = " . $_SESSION['company_id'] . " 
                AND cust.l_id IN (" . $sub_ledger['l_id'] . ")
                ";

                $result = mysqli_query($dbcon, $ca_qry);
                $ca_result = mysqli_fetch_all($result, MYSQLI_ASSOC);

                if ($ca_result)
                {
                    foreach ($ca_result as $value)
                    {
                //$balance_type = $value['balance_typeid'];
                        $op_balance = ($value['balance_typeid'] == "2" ? ($value['opening_balance']) : -$value['opening_balance']);
                        $balance = $op_balance + ($value['debitamount'] - $value['creditamount']);

                        $payment_qry = 'select sum(amount) as amount, entry_type from tbl_general_book as payment
                        where payment.genral_book_status=0 and payment.company_id=' . $_SESSION['company_id'] . ' 
                        and ref_date>="' . date('Y-m-d', strtotime($start_date)) . '" 
                        and ref_date<="' . date('Y-m-d', strtotime($end_date)) . '" 
                        and table_name!="tbl_ledger" and payment.ledger_id IN (' . $sub_ledger['l_id'] . ') 
                        GROUP BY payment.entry_type
                        ORDER BY payment.ref_date
                        ';
                        $result = mysqli_query($dbcon, $payment_qry);
                        $payment_result = mysqli_fetch_all($result, MYSQLI_ASSOC);

                        if ($payment_result)
                        {
                            foreach ($payment_result as $payment)
                            {
                                if ($payment['entry_type'] == 2)
                                {
                                    $balance += $payment['amount'];
                                }
                                else
                                {
                                    $balance -= $payment['amount'];
                                }
                            }
                        }
                    }
                    $amount += $balance;
                }
            }
            $ca_value['group_id'] = $groupID;
            $ca_value['group_name'] = $group_name;
            $ca_value['amount'] = abs($amount);
            return $ca_value;
        }
// Dimple Panchal end: 28-january-2021
        function get_min_max_work_order_stock($dbcon, $product_id)
        {
            $q = "select gd.*,setpro.po_req_no,setpro.po_req_date,(IFNULL(gd.rp_req_qty,0)-IFNULL(stock_add,0)) as pending_stock from tbl_request_product as gd 
            left join tbl_set_main_process as setpro on setpro.sp_id=gd.sp_id
            left join (select IFNULL(sum(qc.product_qty),0) as stock_add,qc.request_id from tbl_sales_order_production_trn as qc 
            where qc.sales_order_production_status=0 group by qc.request_id) as qc on qc.request_id=gd.rp_id
            where gd.status=0 and gd.reject_status=0 and gd.sales_order_trn_id=0 and main_request=1 and gd.rp_pid=" . $product_id . " HAVING pending_stock>0 order by rp_id";

            $rel = $dbcon->query($q);
    //$str=array();
            $str1 = '';
            $str1 .= '<table class="table ">';
            $i = 1;
            $str1 .= '
            <tr>
            <td colspan="4" > <center> <strong> WIP Stock </strong> </center> </td>
            </tr>
            <tr>
            <td>Work Order No / Jobwork No</td>
            <td>Date</td>
            <td>Stock Qty</td>
            <td>Reserve Qty</td>
            </tr>

            ';
            while ($row = mysqli_fetch_array($rel))
            {
                $pending_stock = $row['pending_stock'];

        //if($pending_stock>0){
                if (!empty($row['po_req_no']))
                {
                    $no = $row['po_req_no'];
                    $d = date('d M, Y', strtotime($row['po_req_date']));
                }
                else
                {
                    if (!empty($row['job_card_no']) && !empty($row['indent_no']))
                    {
                        $no = $row['job_card_no'] . " - " . $row['indent_no'];
                        $d = date('d M, Y', strtotime($row['job_card_date']));
                    }
                    else if (!empty($row['job_card_no']))
                    {
                        $no = $row['job_card_no'];
                        $d = date('d M, Y', strtotime($row['job_card_date']));
                    }
                    else if (!empty($row['indent_no']))
                    {
                        $no = $row['indent_no'];
                        $d = date('d M, Y', strtotime($row['indent_date']));
                    }
                }

                $str1 .= '

                <tr>
                <td>' . $no . '
                <input id="so_req_id' . $i . '" name="so_req_id[]" type="hidden" value="' . $row['rp_id'] . '" >
                </td>
                <td style="white-space:nowrap;">' . $d . '</td>
                <td>' . $pending_stock . '</td>
                <td>
                <input id="so_working_stock' . $i . '" name="so_working_stock[]" type="number" class="form-control" title="Enter Stock" value="" placeholder="' . $pending_stock . '" max="' . $pending_stock . '" >
                </td>
                </tr>';
        //}
                $i++;
            }
            $str1 .= '</table>';

            return $str1;
    //return $q;

        }
        function get_godown_stock_so($dbcon, $product_id, $unit_id)
        {
            $q = "select gd_name,gd_id from mst_godown as gd 
            where g_status=0 order by gd_id";

            $rel = $dbcon->query($q);
    //$str=array();
            $str1 = '';
            $str1 .= '<table class="table ">';
            $i = 1;
            $str1 .= '
            <tr>
            <td colspan="3" > <center> <strong> Warehouse Stock </strong> </center> </td>
            </tr>
            <tr>
            <td>Warehouse Name</td>
            <td>Stock Qty</td>
            <td>Reserve Qty</td>
            </tr>

            ';
            $rstock = reserve_stock($dbcon, $product_id, $unit_id);
            while ($row = brp_mysqli_fetch_array($rel))
            {
                $stock_new = get_current_godown_stock_new($dbcon, $product_id, $unit_id, $row['gd_id']);
                if ($rstock > 0)
                {
                    if ($stock_new >= $rstock)
                    {
                        $stock = $stock_new - $rstock;
                        $rstock = $rstock - $rstock;
                    }
                    else
                    {
                        $stock = 0;
                        $rstock = $rstock - $stock_new;
                    }
                }
                else
                {
                    $stock = $stock_new;
                }
                if ($stock > 0)
                {
                    $str1 .= '

                    <tr>
                    <td>' . $row['gd_name'] . '
                    <input id="so_godown' . $i . '" name="so_godown[]" type="hidden" value="' . $row['gd_id'] . '" >
                    </td>
                    <td>' . $stock . '</td>
                    <td>
                    <input id="so_stock' . $i . '" name="so_stock[]" type="number" class="form-control" title="Enter Stock" value="" placeholder="' . $stock . '" max="' . $stock . '" >
                    </td>
                    </tr>';
                    $i++;
                }
            }
            $str1 .= '</table>';

            return $str1;
        }
        function add_so_reserve_stock($dbcon, $reserve_pending_qty, $unit_id, $product_id, $sales_ordertrn_id, $godwn_id, $sales_order_production_trn_id, $branch_id, $stock_id = "")
        {

            $q = "select product_conv_unit,product_base_unit from product_mst as gd where product_id=" . $product_id;
            $rel = $dbcon->query($q);
            $row = mysqli_fetch_array($rel);

            if ($row['product_conv_unit'] == $unit_id)
            {
                $type = "base_unit";
                $con_stock = $reserve_pending_qty;
                $base_stock = convert_stock($dbcon, $reserve_pending_qty, $product_id, $type);
            }
            else
            {
                $type = "conv_unit";
                $base_stock = $reserve_pending_qty;
                $con_stock = convert_stock($dbcon, $reserve_pending_qty, $product_id, $type);
            }

            $info['reserve_date'] = date('Y-m-d');
            $info['product_id'] = $product_id;
            $info['base_unit'] = $row['product_base_unit'];
            $info['base_stock'] = $base_stock;
            $info['convert_unit'] = $row['product_conv_unit'];
            $info['convert_stock'] = $con_stock;
            $info['stock_flage'] = 1;
            $info['ref_name'] = "so_allocate";
            $info['godown_id'] = $godwn_id;
            $info['sales_order_trn_id'] = $sales_ordertrn_id;
            $info['stock_id'] = $stock_id;

            $info['cdate'] = date('Y-m-d H:i:s');
            $info['user_id'] = $_SESSION['user_id'];
            $info['company_id'] = $_SESSION['company_id'];
            $inserid = add_record('tbl_reserve_stock', $info, $dbcon, $branch_id);
            if ($inserid)
            {
                if (!empty($sales_order_production_trn_id))
                {
                    $q = $dbcon->query("update tbl_sales_order_production_trn set allocate_qty = allocate_qty +" . $base_stock . " where sales_order_production_trn_id=" . $sales_order_production_trn_id);
                }
            }
        }
        function add_so_reserve_stock_production($dbcon, $request_id, $stock_qty, $unit_id, $branch_id, $stock_id = "")
        {

            $q = "select * from tbl_sales_order_production_trn as gd where request_id=" . $request_id . " and product_qty>allocate_qty ";
            $rel = $dbcon->query($q);
            while ($row = mysqli_fetch_array($rel))
            {
                $pending_qty = $row['product_qty'] - $row['allocate_qty'];
                if ($unit_id == $row['unit_id'])
                {
                    if ($stock_qty >= $pending_qty)
                    {
                        add_so_reserve_stock($dbcon, $pending_qty, $row['unit_id'], $row['product_id'], $row['sales_ordertrn_id'], "", $row['sales_order_production_trn_id'], $branch_id, $stock_id);

                        $stock_qty = $stock_qty - $pending_qty;
                    }
                    else
                    {
                        add_so_reserve_stock($dbcon, $stock_qty, $row['unit_id'], $row['product_id'], $row['sales_ordertrn_id'], "", $row['sales_order_production_trn_id'], $branch_id, $stock_id);
                        $stock_qty = $stock_qty - $stock_qty;
                    }
                }
                else
                {
                    if ($stock_qty >= $pending_qty)
                    {
                        add_so_reserve_stock($dbcon, $pending_qty, $row['unit_id'], $row['product_id'], $row['sales_ordertrn_id'], "", $row['sales_order_production_trn_id'], $branch_id, $stock_id);
                        $stock_qty = $stock_qty - $pending_qty;
                    }
                    else
                    {
                        add_so_reserve_stock($dbcon, $stock_qty, $row['unit_id'], $row['product_id'], $row['sales_ordertrn_id'], "", $row['sales_order_production_trn_id'], $branch_id, $stock_id);
                        $stock_qty = $stock_qty - $stock_qty;
                    }
                }
            }
        }

/*
Code By Umair: 17/02/2021
Comment: Get Scrap Code
*/
function getScrapCode($dbcon, $id)
{
    $query = "select product_id,product_name,product_icode from product_mst where product_status=0 and product_type=9 and company_id in (0,$_SESSION[company_id])";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Scrap</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {
        $sel = '';
        if ($rel['product_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '">' . $rel['product_name'] . ' - ' . $rel['product_icode'] . '</option>';
    }
    return $str;
}

// Company wise branch dropdown function
function get_branch_name_company($dbcon, $branchid, $all = '', $select = '')

{
    $str = '';
    $i = true;
    $query = "SELECT branch_id, branch_name FROM branch_mst WHERE branch_status IN ('0','1') AND company_id =" . $_SESSION['company_id'];
    $rs_dispatch = $dbcon->query($query);
    if ($select == '')
    {
        $str = '<option value="">Select Branch</option>';
    }

    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        if ($rel['branch_id'] == $branchid)
        {
            $sel = "selected='selected'";
        }
        if ($i)
        {
            if ($branchid == '10000')
            {
                $selC = "selected='selected'";
            }
            if ($all != '')
            {
                $str .= '<option ' . $selC . ' value="10000">All Branch</option>';
            }
            $i = false;
        }

        $str .= '<option ' . $sel . ' value="' . $rel['branch_id'] . '">' . $rel['branch_name'] . ' </option>';
    }
    return $str;
}

function getBranchBox($dbcon, $branch_id, $selectedBranch = '', $isreadOnly = false, $isRequired = false, $onChange = '', $labelCol = '4', $textCol = '8', $extraclass = '', $stylecss = 'text-align: right')
{
    $html = '';

    // Check if the field should be read-only and required
    $chkReadOnly = $isreadOnly ? " disabled" : "";
    $chkRequired = $isRequired ? " required" : "";
    $onChange = $onChange ? ' onChange="' . $onChange . '" ' : '';
    $astrike = $isRequired ? "*" : "";
    $companyConfiguration = getCompanyConfiguration($dbcon);

    if (empty($selectedBranch)) {
        if (!empty($companyConfiguration['default_branch_id'])) {
            $selectedBranch = $companyConfiguration['default_branch_id'];
        }
    }

    // Create a unique ID for the select element using uniqid()
    $uniqueId = "branch_id_" . uniqid();

    // Generate the HTML for the branch selection dropdown
    if ($branch_id == '0') {
        $html .= '<div class="form-group">
                    <label class="col-md-' . $labelCol . ' control-label" style="' . $stylecss . '"><strong>Branch ' . $astrike . '</strong></label>';
        if ($isreadOnly) {
            $html .= '<input type="hidden" name="branch_id" id="branch_id" value="' . $selectedBranch . '">';
        }
        $html .= '<div class="col-md-' . $textCol . '">
                    <select class="select2 ' . $extraclass . '" name="branch_id" id="' . $uniqueId . '" ' . $chkReadOnly . $chkRequired . $onChange . ' >' . get_branch_name_company($dbcon, $selectedBranch) . '</select>
                    </div>
                  </div>';
    } else {
        $html .= '<div class="form-group">
                    <label class="col-md-' . $labelCol . ' control-label" style="' . $stylecss . '"><strong>Branch ' . $astrike . '</strong></label>';
        if ($isreadOnly) {
            $html .= '<input type="hidden" name="branch_id" id="branch_id" value="' . $selectedBranch . '">';
        }
        $html .= '<div class="col-md-' . $textCol . '">
                    <select class="select2 ' . $extraclass . '" name="branch_id" id="' . $uniqueId . '" ' . $chkReadOnly . $chkRequired . $onChange . ' >' . get_branch_name_company($dbcon, $selectedBranch) . '</select>
                    </div>
                  </div>';
    }

    return $html;
}

function w($dbcon, $branch = '', $all = '')
{

    $where = '';
    if ($all == '')
    {
        $where = " and branch_id!='1000'";
    }

    $q = "select * from branch_mst where branch_status='0' and company_id='" . $_SESSION['company_id'] . "'" . $where . " order by branch_name";

    $r = $dbcon->query($q);

    $str = "";
    //$str.= '<option value="">Choose Branch</option>';
    while ($rel = brp_mysqli_fetch_assoc($r))
    {
        $sel = '';
        if ($rel['branch_id'] == $branch)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option  value="' . $rel['branch_id'] . '" ' . $sel . '>' . $rel['branch_name'] . '</option>';
    }
    return $str;
}

function get_trasports($dbcon, $id)
{
    $q = "select * from transportation_details where status='0' order by transportation_name";
    $r = $dbcon->query($q);

    $str = "";
    $str .= '<option value="">Choose Trasportation</option>';
    while ($rel = brp_mysqli_fetch_assoc($r))
    {
        $sel = '';
        if ($rel['id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option  value="' . $rel['id'] . '">' . $rel['transportation_name'] . '</option>';
    }
    return $str;
}
function get_trasports_by_cust($dbcon, $cust_id, $id)
{
    $q = "select trp.id,trp.transportation_name from tbl_cust_tranportation as trn 
    left join transportation_details as trp on trp.id=trn.transportation_id
    where cust_transportation_status='0' and cust_id=" . $cust_id . " order by cust_transportation_id";
    $r = $dbcon->query($q);

    $str = "";
    $str .= '<option value="">Choose Trasportation</option>';
    while ($rel = brp_mysqli_fetch_assoc($r))
    {
        $sel = '';
        if ($rel['id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . '  value="' . $rel['id'] . '">' . $rel['transportation_name'] . '</option>';
    }
    return $str;
}

function get_process_by_product_id($dbcon, $product_id, $process_id)
{
    $product = '';
    $product_id = $product_id;
    $product_qry = "select p.process_id,p.process_priority,pr.process_name from tbl_product_process as p left join process_mst as pr on pr.process_id=p.process_id where p.status = 0 and p.product_id='" . $product_id . "' order by p.process_priority";
    $product_data = $dbcon->query($product_qry);
    $product .= '<option value="">Select Process</option>';
    while ($r = mysqli_fetch_assoc($product_data))
    {
        $sel = '';
        if ($r['process_id'] == $process_id)
        {
            $sel = 'selected="selected"';
        }
        $product .= '<option ' . $sel . ' value="' . $r['process_id'] . '">' . $r['process_name'] . '</option>';
    }
    return $product;
}
function find_with_tax_amount($dbcon, $formulaid, $taxablevalue)
{

    $qry = "SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=" . $formulaid . " order by tax_value desc";
    $row = $dbcon->query($qry);
    $rate_total = $total = $taxablevalue;
    $i = 1;
    while ($tax = brp_mysqli_fetch_assoc($row))
    {
        $tax_amount = ($total) * $tax['tax_value'] / 100;
        $rate_total += $tax_amount;
        //$tax_total_amount+=$info['tax_amount'.$i];
        $i++;
    }

    return $rate_total;
}
function sales_order_used_status_update($dbcon, $sales_ordertrn_id)
{
    $query_so_used = "select sales_order_id from tbl_sales_ordertrn as trn
    where trn.sales_ordertrn_status=0 and trn.sales_ordertrn_id=" . $sales_ordertrn_id;
    $result_so_used = $dbcon->query($query_so_used);
    $row_so_used = mysqli_fetch_assoc($result_so_used);

    $query = "select sales_ordertrn_id from tbl_sales_ordertrn as trn
    where trn.sales_ordertrn_status=0 and trn.invoice_status=0 and trn.sales_order_id=" . $row_so_used['sales_order_id'];
    $result = $dbcon->query($query);
    $cnt = mysqli_num_rows($result);
    //$cnt="";
    $row = mysqli_fetch_assoc($result);
    if ($cnt == "0")
    {
        $inv_trn['invoice_status'] = 1;
    }
    else
    {
        $inv_trn['invoice_status'] = 0;
    }
    $updatetrnid = update_record('tbl_sales_order', $inv_trn, "sales_order_id=" . $row_so_used['sales_order_id'], $dbcon);
}

function generateClassName($field_name)
{
    $field_name = brp_strtolower($field_name);
    $field_name = str_replace(' ', '_', $field_name);
    $field_name = str_replace('-', '_', $field_name);
    $field_name = trim($field_name);

    return $field_name;
}

function trim_lowecase($str)
{
    $str = brp_strtolower($str);
    $str = trim($str);
    return $str;
}
function parts_qc_count_process_wise($dbcon, $process_id)
{
    $branch_id = $_SESSION['branch_id'];
    $branch_id = ($_SESSION['user_type'] == '2' && isset($branch_id) && $branch_id) ? $branch_id : $_SESSION['branch_id'];
    $where_db = check_branch('trn', $branch_id);

    $partsqcpending = "SELECT COUNT(batch.batch_id) as parts_qc_pending FROM tbl_batch_data as batch
    left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id
    left join product_mst as pro on pro.product_id=trn.product_id
    left join tbl_grn as grn on grn.grn_id=trn.grn_id
    WHERE grn.grn_status=0 and trn.grn_trn_status=0 and batch.qc_status=0 and grn.ref_type!=2 and trn.process_id=" . $process_id . " and trn.company_id=" . $_SESSION['company_id'] . " " . $where_db;
    $parts_qc_pending = mysqli_fetch_assoc($dbcon->query($partsqcpending));
    return $parts_qc_pending['parts_qc_pending'];
}

function reprocess_qc_count_process_wise($dbcon, $process_id)
{
    $branch_id = $_SESSION['branch_id'];
    $branch_id = ($_SESSION['user_type'] == '2' && isset($branch_id) && $branch_id) ? $branch_id : $_SESSION['branch_id'];
    $where_db = check_branch('batch', $branch_id);

    $reprocessqcpending = "SELECT COUNT(batch.batch_id) as reprocess_qc_pending FROM tbl_batch_data as batch
    WHERE batch.status = 0 and batch.reprocess_qc = 1 and batch.qc_status = 0 and batch.process_id=" . $process_id . " and batch.company_id=" . $_SESSION['company_id'] . " " . $where_db;
    $reprocess_qc_pending = mysqli_fetch_assoc($dbcon->query($reprocessqcpending));
    return $reprocess_qc_pending['reprocess_qc_pending'];
}
function get_dynamic_bom_no_series_update($dbcon)
{
    $query = "select * from tbl_invoicetype where status=0 and type_id=5 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
    $result = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($result);
    // echo $row['invoicetype_id'];
    // exit;
    $query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = " . $row['invoicetype_id']);
}

function get_products_current_and_next_process($dbcon, $product_id, $process_id)
{
    $sql = "select wpp.process_id, pm.process_name from `tbl_wororder_product_process` as wpp left join process_mst as pm on pm.process_id = wpp.process_id  where wpp.product_id = '" . $product_id . "' and wpp.process_priority >=(select process_priority from `tbl_wororder_product_process` where product_id = '" . $product_id . "' and process_id='" . $process_id . "') group by wpp.process_id  order by wpp.process_priority";
    $result = $dbcon->query($sql);

    $product = '<option value="">Select Process</option>';
    while ($r = brp_mysqli_fetch_assoc($result))
    {

        $product .= '<option value="' . $r['process_id'] . '">' . $r['process_name'] . '</option>';
    }
    return $product;
}

function get_products_current_and_previous_process($dbcon, $product_id, $process_id,$p_id ="")
{
    $where = "";

    if(!empty($p_id)){
        $query = "select p_ref_id as rp_id from tbl_allocate_process where p_id = " . $p_id;
        $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
        $where = " and wpp.rp_id = " . $rel['rp_id'];

    }
    $sql = "select wpp.process_id, pm.process_name from `tbl_wororder_product_process` as wpp left join process_mst as pm on pm.process_id = wpp.process_id  where wpp.product_id = '" . $product_id .  $where ."' and wpp.process_priority <=(select process_priority from `tbl_wororder_product_process` where product_id = '" . $product_id . "' and process_id='" . $process_id . $where."'  group by process_priority) group by wpp.process_id  order by wpp.process_priority";
    $result = $dbcon->query($sql);

    $product = '<option value="">Select Process</option>';
    while ($r = brp_mysqli_fetch_assoc($result))
    {

        $product .= '<option value="' . $r['process_id'] . '">' . $r['process_name'] . '</option>';
    }
    return $product;
}

// Amish Soni Start 23-03-2021
function count_general_pen_tsk($dbcon, $user_id, $isTeamPending = true)
{
    $branch_id = ($_SESSION['branch_id']==0) ? "" : " AND task.branch_id = ".$_SESSION['branch_id'];
    $qry = "SELECT COUNT(task.task_id) ttl_pen_tasks FROM tbl_task AS task 
    WHERE task.task_status = 0 AND task.entry_type=1 AND task.task_type_id = '" . GENERAL_TASK_TYPE . "' AND task.company_id = " . $_SESSION['company_id'] . " " . $branch_id . " AND DATE_FORMAT(task.task_due_date,'%Y-%m-%d')<='" . date('Y-m-d') . "' AND FIND_IN_SET (" . $user_id . ",task.show_user_ids) ";
    // if ($isTeamPending) {
    // } else {
    //  $qry .= " AND FIND_IN_SET (".$user_id.",task.assign_user_ids) ";
    // }
    $qry_rel = mysqli_fetch_assoc($dbcon->query($qry));
    return floatval($qry_rel['ttl_pen_tasks']);
}
// Amish Soni End 23-03-2021
/*
    Code By Umair: 27/03/2021
*/
    function check_product_qc_paramter($dbcon, $product_id, $process_id = "")
    {
        $where = "";

        if ($process_id != "")
        {
            $where = " and process_id = " . $process_id;
        }
        $qry = "SELECT pr_param_id FROM tbl_product_parameter WHERE product_id='" . $product_id . "' " . $where;

        $qry_rel = brp_mysqli_num_rows($dbcon->query($qry));

        if ($qry_rel > 0)
        {
            return '1';
        }
        else
        {
            return '0';
        }
    }
    function getproduct_process_stock($dbcon, $product_id)
    {
        $str = '';
        $query = "select p.product_id,p.product_name,p.product_desc,p.product_type, dr.drawing_number,p.drawing_id,count(ppro.pr_process_id) as process_count from product_mst as p
        left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
        right join tbl_product_process as ppro on ppro.product_id=p.product_id
        where ppro.status = 0 and p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) group by p.product_id";

        $rs_product = $dbcon->query($query);
        $str .= '<option value="">Choose Product</option>';
        while ($rel = mysqli_fetch_assoc($rs_product))
        {
            if ($rel['process_count'] > "1")
            {
                if ($rel['drawing_id'] != 0)
                {
                    $drawing_number = $rel['drawing_number'];
                }
                else
                {
                    $drawing_number = '0';
                }
                $sel = '';
                if ($rel['product_id'] == $id)
                {
                    $sel = "selected='selected'";
                }
                $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '" data-product_type = "' . $rel['product_type'] . '">' . $rel['product_name'] . "-- (" . $drawing_number . ')' . ' --- ' . $rel['process_count'] . '</option>';
            }
        }
        return $str;
    //return $query;

    }
    function load_product_unit($dbcon, $product_id)
    {
        $query1 = "SELECT product_base_unit,product_conv_unit FROM product_mst WHERE product_id=" . $product_id;
        $rs_type1 = $dbcon->query($query1);
        $row1 = brp_mysqli_fetch_assoc($rs_type1);

        if ($row1['product_base_unit'] != $row1['product_conv_unit'])
        {
            $query = "SELECT unitid, unit_name FROM unit_mst WHERE unitid IN (" . $row1['product_base_unit'] . "," . $row1['product_conv_unit'] . ") ";
        }
        else
        {
            $query = "SELECT unitid, unit_name FROM unit_mst WHERE unitid=" . $row1['product_base_unit'];
        }

        $str = '';
        $rs_type = $dbcon->query($query);

        $str = '<option value="">SELECT Unit</option>';
        while ($row = brp_mysqli_fetch_assoc($rs_type))
        {
            $sel = '';
            if ($row['unitid'] == $id)
            {
                $sel = 'selected="selected"';
            }

            $str .= '<option ' . $sel . ' value="' . $row['unitid'] . '">' . $row['unit_name'] . '</option>';
        }
        return $str;
    }
    function deduct_so_reseve_stock($dbcon, $sales_order_trn_id, $stock_qty, $unit_id)
    {
        $query = "select res.*,pro.product_base_unit,pro.product_conv_unit from tbl_reserve_stock as res
        left join product_mst as pro on pro.product_id=res.product_id
        where stock_status!=2 and stock_flage=1 and sales_order_trn_id=" . $sales_order_trn_id;

        $result = $dbcon->query($query);

        //var_dump($query);exit;
        while ($row = mysqli_fetch_assoc($result))
        {

        //request_id
            $product_id = $row['product_id'];
            $branch_id = $row['branch_id'];
        //$stock=reserve_stock($dbcon,$product_id,$unit_id,$row['reserve_id'],"","","",$branch_id);
            $reserve_id = "";
            $request_id1 = "";
            $complaint_id = "";
            $branch_id1 = "";
            $stock = reserve_stock($dbcon, $product_id, $unit_id, $reserve_id, $request_id1, $complaint_id, $row['sales_order_trn_id'], $branch_id1);

            if ($stock_qty != "")
            {
                if ($stock_qty != 0)
                {
                    if ($stock_qty >= $stock)
                    {
                        if ($row['product_conv_unit'] == $unit_id)
                        {
                            $type = "base_unit";
                            $con_stock = $stock;
                            $base_stock = convert_stock($dbcon, $stock, $product_id, $type);
                        }
                        else
                        {
                            $type = "conv_unit";
                            $base_stock = $stock;
                            $con_stock = convert_stock($dbcon, $stock, $product_id, $type);
                        }
                        $info['reserve_date'] = date('Y-m-d');
                        $info['product_id'] = $product_id;
                        $info['base_unit'] = $row['product_base_unit'];
                        $info['base_stock'] = $base_stock;
                        $info['convert_unit'] = $row['product_conv_unit'];
                        $info['convert_stock'] = $con_stock;
                        $info['godown_id'] = $row['godown_id'];
                        $info['stock_id'] = $row['stock_id'];
                        $info['perent_id'] = $row['reserve_id'];
                        $info['stock_flage'] = 2;
                        $info['request_id'] = $row['request_id'];
                        $info['ref_name'] = "invoice_trn";
                        $info['ref_id'] = $row['ref_id'];
                        $info['sales_order_trn_id'] = $row['sales_order_trn_id'];

                        $info['cdate'] = date('Y-m-d H:i:s');
                        $info['user_id'] = $_SESSION['user_id'];
                        $info['company_id'] = $_SESSION['company_id'];
                        $inserid = add_record('tbl_reserve_stock', $info, $dbcon, $branch_id);
                        $stock_qty = $stock_qty - $stock;
                        $q = $dbcon->query("update tbl_reserve_stock set stock_status='1' where reserve_id=" . $row['reserve_id']);

                    }
                    else
                    {
                        if ($row['product_conv_unit'] == $unit_id)
                        {
                            $type = "base_unit";
                            $con_stock = $stock_qty;
                            $base_stock = convert_stock($dbcon, $stock_qty, $product_id, $type);
                        }
                        else
                        {
                            $type = "conv_unit";
                            $base_stock = $stock_qty;
                            $con_stock = convert_stock($dbcon, $stock_qty, $product_id, $type);
                        }
                        $info['reserve_date'] = date('Y-m-d');
                        $info['product_id'] = $product_id;
                        $info['base_unit'] = $row['product_base_unit'];
                        $info['base_stock'] = $base_stock;
                        $info['convert_unit'] = $row['product_conv_unit'];
                        $info['godown_id'] = $row['godown_id'];
                        $info['stock_id'] = $row['stock_id'];
                        $info['perent_id'] = $row['reserve_id'];
                        $info['convert_stock'] = $con_stock;
                        $info['stock_flage'] = 2;
                        $info['request_id'] = $row['request_id'];
                        $info['ref_name'] = "request";
                        $info['ref_id'] = $row['reserve_id'];
                        $info['sales_order_trn_id'] = $row['sales_order_trn_id'];

                        $info['cdate'] = date('Y-m-d H:i:s');
                        $info['user_id'] = $_SESSION['user_id'];
                        $info['company_id'] = $_SESSION['company_id'];
                        $inserid = add_record('tbl_reserve_stock', $info, $dbcon, $branch_id);

                        if ($row['product_conv_unit'] == $unit_id)
                        {
                        //$con_stock=$stock_qty;
                            $stock_qty = $stock_qty - $con_stock;
                        }
                        else
                        {
                        //$base_stock=$stock_qty;
                            $stock_qty = $stock_qty - $base_stock;
                        }
                    }
                }
            }
        }
    //echo $sales_order_trn_id; 
    }

    function update_grn_sub_trn_to_purchase_status($dbcon, $grn_sub_trn_id)
    {
        $query = "select product_qty,grn_trn_id from tbl_grn_sub_trn as res
        where grn_trn_sub_id=" . $grn_sub_trn_id;

        $result = $dbcon->query($query);
        $row = brp_mysqli_fetch_assoc($result);

        $query_used = "select sum(used_qty) as used_qty from tbl_po_grn_used as res
        where po_grn_used_status=0 and grn_sub_trn_id=" . $grn_sub_trn_id;

        $result_used = $dbcon->query($query_used);
        $row_used = brp_mysqli_fetch_assoc($result_used);

        if ((float)$row['product_qty']<= (float)$row_used['used_qty'])
        {
            $query_invoicetype = $dbcon->query("UPDATE tbl_grn_sub_trn SET purchase_status =1 WHERE grn_trn_sub_id = " . $grn_sub_trn_id);
        }
        else
        {
            $query_invoicetype = $dbcon->query("UPDATE tbl_grn_sub_trn SET purchase_status =0 WHERE grn_trn_sub_id = " . $grn_sub_trn_id);
        }

        $query_gtrn = "select grn_trn_id from tbl_grn_sub_trn as res
        where status=0 and purchase_status=0 and grn_trn_id=" . $row['grn_trn_id'];
        $result_gtrn = $dbcon->query($query_gtrn);
        $row_gtrn = brp_mysqli_fetch_assoc($result_gtrn);

        if (!empty($row_gtrn['grn_trn_id']))
        {
            $query_invoicetype = $dbcon->query("UPDATE tbl_grn_trn SET purchase_status=0 WHERE grn_trn_id = " . $row_gtrn['grn_trn_id']);
        }
        else
        {
            $query_invoicetype = $dbcon->query("UPDATE tbl_grn_trn SET purchase_status=1 WHERE grn_trn_id = " . $row['grn_trn_id']);
        }

        $query_g = "select grn_id from tbl_grn_trn as res
        where grn_trn_id=" . $row['grn_trn_id'];
        $result_g = $dbcon->query($query_g);
        $row_g = brp_mysqli_fetch_assoc($result_g);

        $query_gmst = "select grn_id from tbl_grn_trn as res
        where grn_trn_status=0 and purchase_status=0 and grn_id=" . $row_g['grn_id'];
        $result_gmst = $dbcon->query($query_gmst);
        $row_gmst = brp_mysqli_fetch_assoc($result_gmst);

        if (!empty($row_gmst['grn_id']))
        {
            $query_invoicetype = $dbcon->query("UPDATE tbl_grn SET purchase_status=0 WHERE grn_id = " . $row_gmst['grn_id']);
        }
        else
        {
            $query_invoicetype = $dbcon->query("UPDATE tbl_grn SET purchase_status=1 WHERE grn_id = " . $row_g['grn_id']);
        }
    }
//update purchase status for service create by maulik
    function update_service_purchase_status($dbcon, $po_id)
    {
        $query = "select * from tbl_potrancation where potrancation_status=0 and po_id =" . $po_id;
        $q_trn = $dbcon->query($query);
        while ($row = mysqli_fetch_array($q_trn))
        {
            $ser_trn = "select product_qty from tbl_service_notes_trn where service_trn_id=" . $row['service_trn_id'];
            $se_trn = $dbcon->query($ser_trn);
            $se_row = mysqli_fetch_array($se_trn);

            $potrn = "select sum(product_qty) as done from tbl_potrancation where potrancation_status=0 and service_trn_id=" . $row['service_trn_id'];
            $po_ex = $dbcon->query($potrn);
            $po_row = mysqli_fetch_array($po_ex);

            if ($se_row['product_qty'] <= $po_row['done'])
            {
                $query_invoicetype = $dbcon->query("UPDATE tbl_service_notes_trn SET purchase_status =1 WHERE service_trn_id = " . $row['service_trn_id']);
            }
            else
            {
                $query_invoicetype = $dbcon->query("UPDATE tbl_service_notes_trn SET purchase_status =0 WHERE service_trn_id = " . $row['service_trn_id']);
            }
        }
    }

// Send Mail FUnctionality Start
    function send_mail_old($dbcon, $to, $subject, $message, $from_email = "", $ccmail = [], $resume = [], $bccmail = [], $quotation = 0)
    {
    //Load Composer's autoloader
        if ($quotation)
        {
            require '../../../vendor/autoload.php';
        }
        else
        {
            require '../vendor/autoload.php';
        }

        $mail = new PHPMailer(true);

    // Passing `true` enables exceptions
        try
        {
            if (IS_SMTP == '1')
            {
            //Server settings
            //$mail->SMTPDebug = 2;
                $mail->isSMTP();
                $mail->Host = MAIL_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_USERNAME;
                $mail->Password = MAIL_PASSWORD;
                $mail->SMTPSecure = MAIL_ENCRYPTION;
                $mail->Port = MAIL_PORT;

                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            }
        //Recipients
            if (!empty($from_email))
            {
                $mail->setFrom($from_email, TITLE);
            }
            else
            {
                $mail->setFrom(MAIL_USERNAME, TITLE);
            }

        //$mail->addAddress($to);
            foreach ($to as $key => $value)
            {
            $mail->addAddress($value); // Add a recipient
            
        }

        $mail->addReplyTo(MAIL_USERNAME, TITLE);

        //CC Mail
        if (!empty($ccmail))
        {
            foreach ($ccmail as $key => $value)
            {
                $mail->addCC($value);
            }
        }

        //Bcc Mail
        if (!empty($bccmail))
        {
            foreach ($bccmail as $key => $value)
            {
                $mail->addBCC($value);
            }
        }

        //Attachments
        if (!empty($resume))
        {
            foreach ($resume as $key => $value)
            {
                //$attachment='uploads/invoice/'.$value;
                //echo $attachment;die();
                $mail->addAttachment($attachment);
                // $s = explode("/",$value);
                // $filename=end($s);
                // $mail->AddStringAttachment($value, $filename,  $encoding = 'base64', $type = 'application/pdf');
                
            }
        }
        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        return true;
    }
    catch(Exception $e)
    {

        return false;
        //echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;die();
        
    }
}

/*
    START :: SEND MAIL :: CREATED BY :: SANAT
    comment :: get smtp email & password from company setting
*/

// Send Mail FUnctionality Start
    function send_mail($dbcon, $to, $subject, $message, $from_email = "", $ccmail = [], $resume = [], $bccmail = [], $quotation = 0)
    {
    //Load Composer's autoloader
        $arrEmail = "select smtp_email,smtp_password from tbl_company where company_id = " . $_SESSION['company_id'] . " AND user_id = " . $_SESSION['user_id'];
        $email_data = brp_mysqli_fetch_assoc($dbcon->query($arrEmail));

    // Send Mail
        $from_email = $email_data['smtp_email'];
        $smtp_password = $email_data['smtp_password'];

    // $from_email = 'developer@brperp.com';
    // $smtp_password = 'developer@123';
        if ($quotation == '1')
        {
            require '../../../vendor/autoload.php';
        }
        else if ($quotation == '2')
        {
            require '../../vendor/autoload.php';
        }
        else
        {
            require '../vendor/autoload.php';
        }

        $mail = new PHPMailer(true);
    // Passing `true` enables exceptions
        try
        {
            if (IS_SMTP == '1')
            {
            //Server settings
            // $mail->SMTPDebug = 1;       // debugging: 1 = errors and messages, 2 = messages only
                $mail->isSMTP();
            $mail->Host = 'mail.brperp.com'; // MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = $from_email;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = MAIL_ENCRYPTION;
            $mail->Port = 465; // MAIL_PORT;
            /*$mail->SMTPOptions = array(
            'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            )
        );*/
    }
        //Recipients
        // if(!empty($from_email)){
        //  $mail->setFrom($from_email,TITLE);
        // }else{
        //  $mail->setFrom(MAIL_USERNAME,TITLE);
        // }
    $mail->setFrom($from_email, TITLE);

        //$mail->addAddress($to);
    foreach ($to as $key => $value)
    {
            $mail->addAddress($value); // Add a recipient
            
        }

        // $mail->addReplyTo(MAIL_USERNAME, TITLE);
        $mail->addReplyTo($from_email, TITLE);

        //CC Mail
        if (!empty($ccmail))
        {
            foreach ($ccmail as $key => $value)
            {
                $mail->addCC($value);
            }
        }

        //Bcc Mail
        if (!empty($bccmail))
        {
            foreach ($bccmail as $key => $value)
            {
                $mail->addBCC($value);
            }
        }

        //Attachments
        if (!empty($resume))
        {
            foreach ($resume as $key => $value)
            {
                $mail->addAttachment(trim($value));
            }
        }
        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        //             echo "<pre>";
        //      print_r($mail);die;
        // echo "asda00000";
        $mail->send();

        return true;
    }
    catch(Exception $e)
    {

        return false;
        // echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;die();
        
    }
}

/*
    END SEND MAIL :: CREATED BY :: SANAT
*/

// Send Mail FUnctionality End
/*
Code By Umair: 26/04/2021
Comment: Get Scrap Code
*/
function get_generaltask_all($dbcon, $id)
{
    $query = "select * from general_task_mst where task_status=0 and company_id in (0,$_SESSION[company_id])";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose General Task</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {
        // $sel = '';
        if ($rel['gt_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['gt_id'] . '">' . $rel['general_task_name'] . '</option>';
    }
    return $str;
}
function get_consignee($dbcon, $id, $ledger_id)
{
    $str = '';
    $where = "";

    $query = "select l.cust_id,l.company_name from tbl_custmer_consignee as l where l.cust_ref_id = '" . $ledger_id . "' and l.cust_status=0 and l.company_id in (0," . $_SESSION['company_id'] . ")";
    $rs_cust = $dbcon->query($query);

    $str .= '<option value="">Select Consignee</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {
        // $sel = '';
        if ($rel['cust_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['cust_id'] . '">' . $rel['company_name'] . '</option>';
    }
    return $str;
}

/*Code By Umair: Get Item Price From Purchase Crad Transaction Table based on the Vendor Selection
Date: 11-06-2021
*/
function getItemPriceByCustomerId($dbcon, $vender_id, $product_id)
{

    $query = "select tpt.*, `u`.`user_name` from tbl_customer_wise_producttrn as tpt left join users as u ON `tpt`.`user_id`=`u`.`user_id` where `tpt`.`customer_wise_producttrn_status`=0 AND `tpt`.`vendor_id`='" . $vender_id . "' AND `tpt`.`product_id`='" . $product_id . "'  AND `tpt`.`company_id`='" . $_SESSION['company_id'] . "' AND `tpt`.`affected_date` <= '" . date('Y-m-d') . "' order by `tpt`.`affected_date` desc limit 1";

    $result = $dbcon->query($query);
    if (brp_mysqli_num_rows($result) > 0)
    {
        $row = mysqli_fetch_assoc($result);
        return $row;
    }
    else
    {
        return [];
    }
}

// function getCompanyConfiguration($dbcon){
//  $query="select * from tbl_company_configuration where isdelete=0 and company_id=".$_SESSION['company_id'];
//  $result=$dbcon->query($query);
//  $row=brp_mysqli_fetch_assoc($result);
//  return $row;
// }
function get_table_details_option($dbcon, $table, $table_id, $field_name, $where = '')
{
    $query = "select * from $table where 1=1 " . $where;
    $str = "";
    $sel = $dbcon->query($query);
    while ($row = mysqli_fetch_array($sel))
    {
        $str .= "<option value='" . $row[$table_id] . "'>" . $row[$field_name] . "</option>";
    }

    return $str;
}

function total_multicurrency($dbcon, $ledger_id)
{
    $qry = "SELECT sum(curreency_opening_balance_rs) as total FROM `tbl_ledger_currency_opening` where isdelete=0 and currency_ledger_id=$ledger_id";
    $result = $dbcon->query($qry);
    $row = brp_mysqli_fetch_assoc($result);
    return $row['total'];
}

function total_multibranch($dbcon, $ledger_id)
{
    $qry = "SELECT sum(branch_opening_balance) as total FROM `tbl_ledger_branch_opening` where isdelete=0 and branch_ledger_id=$ledger_id";
    $result = $dbcon->query($qry);
    $row = brp_mysqli_fetch_assoc($result);
    return $row['total'];
}

//Added by dhruv for TDS Tax category
function get_tds_tax_payee($dbcon, $temp_id, $text, $edit_id = '') {
    // Prepare the SQL query
    $qry = "SELECT
                ttc.tds_cat_detail_id,
                cm.common_mst_name,
                ttc.tds_thresold_limit,
                ttc.tds_with_pan,
                ttc.tds_without_pan,
                ttc.tds_surcharge
            FROM
                tbl_tds_tax_category_detail ttc
            JOIN
                tbl_common_mst as cm ON cm.common_mst_id = ttc.tds_payee
            WHERE
                ttc.isdelete = 0 AND ttc.tds_cat_id = ?";

    // Prepare the statement
    $stmt = $dbcon->prepare($qry);
    if (!$stmt) {
        die("Prepare failed: " . $dbcon->error);
    }

    // Bind the parameter and execute
    $stmt->bind_param("i", $temp_id);
    $stmt->execute();
    $template_name = $stmt->get_result();

    // Build the dropdown options
    $template_record = '<option value="">SELECT ' . htmlspecialchars($text) . '</option>';
    while ($row = $template_name->fetch_assoc()) {
        $sel = ($row['tds_cat_detail_id'] == $edit_id) ? 'selected="selected"' : '';
        $template_record .= sprintf(
            '<option %s data-catid="%s" value="%s">%s</option>',
            $sel,
            htmlspecialchars($row['tds_cat_detail_id']),
            htmlspecialchars($row['tds_cat_detail_id']),
            htmlspecialchars($row['common_mst_name'])
        );
    }

    // Close the statement
    $stmt->close();

    return $template_record;
}


function get_common_category($dbcon, $temp_id, $text, $edit_id = '')
{
    $qry = "SELECT ccm.common_category_id,cm.common_mst_id,cm.common_mst_name FROM tbl_common_category_mst as ccm left join `tbl_common_mst` as cm on ccm.common_category_id = cm.common_category_id and cm.isdelete=0 where ccm.isdelete=0 and ccm.common_category_id=$temp_id";

    $template_name = $dbcon->query($qry);
    $template_record = '<option value="">SELECT ' . $text . '</option>';

    while ($row = brp_mysqli_fetch_assoc($template_name))
    {
        $sel = '';
        if ($row['common_mst_id'] == $edit_id)
        {
            $sel = 'selected="selected"';
        }
        $template_record .= '<option ' . $sel . ' data-catid="' . $row['common_category_id'] . '" value="' . $row['common_mst_id'] . '" >' . $row['common_mst_name'] . '</option>';
    }
    return $template_record;
}

function getAddedDepreciation($dbcon)
{
    $id = $_REQUEST['id'];
    if (isset($id))
    {
        $ledger_id = $id;
    }
    else
    {
        $ledger_id = 0;
    }
    $qry = "SELECT * FROM `tbl_ledger_depreciation` where isdelete=0 and depreciate_ledger_id=$ledger_id";
    $result = $dbcon->query($qry);
    $row = brp_mysqli_fetch_assoc($result);
    return $row;
}

function getAnnualBudget($dbcon)
{
    $id = $_REQUEST['id'];
    if (isset($id))
    {
        $ledger_id = $id;
    }
    else
    {
        $ledger_id = 0;
    }
    $qry = "SELECT mb.budget_id,mb.annual_budget,mbd.budget_month,mbd.budget_month_amount,mbd.budget_detail_id FROM `tbl_ledger_month_budget` as mb left join tbl_ledger_month_budget_details as mbd on mb.`budget_id`= mbd.`budget_id` where mb.isdelete=0 and mb.budget_ledger_id=$ledger_id";
    $result = $dbcon->query($qry);
    $row = brp_mysqli_fetch_all($result);
    return $row;
}

function getledger($dbcon)
{

    $query = "select * from tbl_ledger where l_status=0 and company_id = $_SESSION[company_id] order by TRIM(l_name) ASC";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Ledger</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust))
    {

        $str .= '<option value="' . $rel['l_id'] . '">' . $rel['l_name'] . '</option>';
    }
    return $str;
}

function total_deposite_bankcheque($dbcon, $ledger_id)
{

    $qry = "SELECT sum(cheque_amount) as total FROM `tbl_ledger_cheque_opening` where isdelete=0 and cheque_ledger=$ledger_id and cheque_entry_type=1";
    $result = $dbcon->query($qry);
    $row = brp_mysqli_fetch_assoc($result);
    return !empty($row['total']) ? $row['total'] : 0;
}

function total_issued_bankcheque($dbcon, $ledger_id)
{

    $qry = "SELECT sum(cheque_amount) as total FROM `tbl_ledger_cheque_opening` where isdelete=0 and cheque_ledger=$ledger_id and cheque_entry_type=2";
    $result = $dbcon->query($qry);
    $row = brp_mysqli_fetch_assoc($result);
    return !empty($row['total']) ? $row['total'] : 0;
}

function total_billbybill($dbcon, $ledger_id)
{
    $qry = "SELECT sum(bill_amount) as total FROM `tbl_ledger_billbybill_opening` where isdelete=0 and bill_ledger_id=$ledger_id";
    $result = $dbcon->query($qry);
    $row = brp_mysqli_fetch_assoc($result);
    return $row['total'];
}
/*
Code By Umair: 23-06-2021
Comment : Get Inquiry Type
START
*/
function getInquiryType($dbcon, $inquiry_id)
{
    $str = '';
    $inqqry = "SELECT * FROM mst_inquiry_type WHERE status = 0 AND company_id = " . $_SESSION['company_id'];
    $inq_data = $dbcon->query($inqqry);

    while ($rel = mysqli_fetch_assoc($inq_data))
    {
        $sel = '';
        if ($rel['inquiry_type_id'] == $inquiry_id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['inquiry_type_id'] . '">' . $rel['inquiry_type_name'] . '</option>';
    }
    return $str;
}

// Comment : Get Project List Form tbl_project_assign tabel
function getProjectList($dbcon, $id)
{
    $str = '';

    $proj_qry = "SELECT * FROM product_mst WHERE product_status = 0 AND product_type = '-1'";
    $proj_data = $dbcon->query($proj_qry);

    while ($rel = mysqli_fetch_assoc($proj_data))
    {
        $sel = '';
        if ($rel['product_id'] == $id)
        {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '" data-pid="p_' . $rel['product_id'] . '" data-type="projectwise">' . $rel['product_name'] . '</option>';
    }
    return $str;
}

/*END*/

/* Sanat  add for bom product filter -  30-07-2021  START */

/* Sanat  add for bom product filter -  30-07-2021  END */

function getspecialConfiguration($dbcon, $id = false)
{

    $query = "SELECT * FROM `tbl_company_special_field_permission` WHERE company_id=".$_SESSION['company_id'];

    if ($id)
    {
        $query .= " AND `sp_field_permission_id` = $id";
    }

    $query .= " ORDER BY sp_field_permission_id DESC LIMIT 1";

    $q = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($q);
    return $row;
}
function get_product_history($dbcon, $cust_id = false, $product_id = false, $eid="", $type="")
{
    $row = '';
    $where = '';
    if ($cust_id)
    {
        $where .= ' AND quot.cust_id = ' . $cust_id;
    }
    if ($product_id)
    {
        $where .= ' AND quottrn.product_id = ' . $product_id;
    }
    if($type==1){
        if ($eid)
        {
            $where .= ' AND quot.quotation_id != ' . $eid;
        }
        $query = "SELECT quot.quotation_no, quot.quotation_date, quot.quotation_id, quottrn.product_qty, quottrn.product_rate, quottrn.product_discount, quottrn.discount_per, quottrn.product_id, pro.product_name FROM tbl_quotation AS quot LEFT JOIN tbl_quotation_trn AS quottrn ON quot.quotation_id = quottrn.quotation_id LEFT JOIN product_mst AS pro ON pro.product_id = quottrn.product_id WHERE quottrn.quot_trn_status = 0 " . $where . " AND quot.quotation_date between '" . date('Y-m-d', strtotime('-365 days')) . "' and '" . date('Y-m-d', strtotime(date('Y-m-d'))) . "' ORDER BY quot.quotation_id DESC LIMIT 50";

        $cust_qry = "select cust.cust_name as cust_name from tbl_customer as cust where cust.cust_id=" . $cust_id;
    }else if($type==2){
        if ($eid)
        {
            $where .= ' AND quot.sales_order_id != ' . $eid;
        }
        $query = "SELECT quot.sales_order_no, quot.sales_order_date, quot.sales_order_id, quottrn.product_qty, quottrn.product_rate, quottrn.product_discount, quottrn.discount_per, quottrn.product_id, pro.product_name FROM tbl_sales_order AS quot LEFT JOIN tbl_sales_ordertrn AS quottrn ON quot.sales_order_id = quottrn.sales_order_id LEFT JOIN product_mst AS pro ON pro.product_id = quottrn.product_id WHERE quottrn.sales_ordertrn_status = 0 " . $where . " AND quot.sales_order_date between '" . date('Y-m-d', strtotime('-365 days')) . "' and '" . date('Y-m-d', strtotime(date('Y-m-d'))) . "' ORDER BY quot.sales_order_id DESC LIMIT 50";

        $cust_qry = "select cust.l_name as cust_name from tbl_ledger as cust where cust.l_id=" . $cust_id;
    }else if($type==3){
        if ($eid)
        {
            $where .= ' AND inq.inquiry_id != ' . $eid;
        }
         $query = "SELECT quot.inquiry_no, quot.inquiry_date,quottrn.product_qty, quottrn.product_rate, quottrn.product_id, pro.product_name FROM tbl_inquiry AS quot LEFT JOIN tbl_inquiry_trn AS quottrn ON quot.inquiry_id = quottrn.inquiry_id LEFT JOIN product_mst AS pro ON pro.product_id = quottrn.product_id WHERE quottrn.inquiry_trn_status = 0 " . $where . " AND quot.inquiry_date between '" . date('Y-m-d', strtotime('-365 days')) . "' and '" . date('Y-m-d', strtotime(date('Y-m-d'))) . "' ORDER BY quot.inquiry_id DESC LIMIT 50";

        $cust_qry = "select cust.cust_name from tbl_customer as cust where cust.cust_id=" . $cust_id;
    }

    $result = $dbcon->query($query);
    $cust_rel = mysqli_fetch_assoc($dbcon->query($cust_qry));
    if (brp_mysqli_num_rows($result) > 0)
    {
        $i = 0;
        while ($res = brp_mysqli_fetch_assoc($result))
        {
            if ($res['product_discount'] != 0)
            {
                $actual = $res['product_rate'] * $res['product_discount'];
            }
            else
            {
                $actual = $res['product_rate'];
            }
            if ($i == 0)
            {
                $row .= '<table class="display table table-bordered table-striped">
                <thead>
                <tr>
                <th colspan="3"><strong>Customer Name: ' . $cust_rel['cust_name'] . '</strong></th>
                <th colspan="3"><strong>Product Name: ' . $res['product_name'] . '</strong></th>
                </tr>
                <tr>';
                if($type==1){
                    $row .= '<th>Quotation No</th>
                    <th>Quotation Date</th>';
                }else if($type==2){
                    $row .= '<th>Sales Order No</th>
                    <th>Sales Order Date</th>';
                }else if($type==2){
                    $row .= '<th>Inquiry No</th>
                    <th>Inquiry Date</th>';
                }
                $row .= '<th>Qty</th>
                <th>Product Rate</th>
                <th>Product Discount</th>
                <th>Actual Rate</th>
                </tr>
                </thead>';
            }
            $row .= '<tr>';
            if($type==1){
                $row .= '<td>' . $res['quotation_no'] . '</td>
                <td>' . $res['quotation_date'] . '</td>';
            }else if($type==2){
                $row .= '<td>' . $res['sales_order_no'] . '</td>
                <td>' . $res['sales_order_date'] . '</td>';
            }else if($type==2){
                $row .= '<td>' . $res['inquiry_no'] . '</td>
                <td>' . $res['inquiry_date'] . '</td>';
            }
            $row .= '<td>' . $res['product_qty'] . '</td>
            <td>' . $res['product_rate'] . '</td>
            <td>' . $res['discount_per'] . '</td>
            <td>' . $actual . '</td>
            </tr>';
            $i++;
        }
    }
    else
    {
        $row .= '<table class="display table table-bordered table-striped">
        <tr>
        <td class="text-center">No Data Found</td>
        </tr>';
    }
    $row .= '</table>';
    return $row;
}

function count_workorder_permission($dbcon)
{
    $query = "select count(rp_id) from tbl_request_product where approval_status ='0' AND main_request != 1 AND STATUS = 3 and company_id = ".$_SESSION['company_id']." group by rp_id";
    $rs = $dbcon->query($query);
    $cnt = brp_mysqli_num_rows($rs);
    return $cnt;
}

function get_product_item_type_company($dbcon, $productitemtypeid = '', $all = '')
{
    $str = '';
    $i = true;
    $query = "SELECT product_item_type_id, product_item_type_name FROM pro_ms_item_type WHERE product_item_type_status IN ('0','1') AND company_id =" . $_SESSION['company_id'];
    $rs_dispatch = $dbcon->query($query);
    if ($all == '')
    {
        $str = '<option value="">Select Item Type</option>';
    }
    if ($all != '')
    {
        $str .= '<option value="">--ALL--</option>';
    }

    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        if ($rel['product_item_type_id'] == $productitemtypeid)
        {
            $sel = "selected='selected'";
        }

        $str .= '<option ' . $sel . ' value="' . $rel['product_item_type_id'] . '">' . $rel['product_item_type_name'] . '</option>';
    }
    return $str;
}
/* END JAYESH 21-07-2021 */

/* START JAYESH 21-07-2021  PURPOSE : For product type Status  */
function get_product_item_status_company($dbcon, $productitemstatusid = '')
{
    $str = '';
    $i = true;
    $query = "SELECT product_item_status_id, product_item_status_name FROM pro_ms_item_status WHERE product_item_status_status IN ('0','1') AND company_id =" . $_SESSION['company_id'];
    $rs_dispatch = $dbcon->query($query);

    $str = '<option value="">Select Item Status</option>';

    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        if ($rel['product_item_status_id'] == $productitemstatusid)
        {
            $sel = "selected='selected'";
        }

        $str .= '<option ' . $sel . ' value="' . $rel['product_item_status_id'] . '">' . $rel['product_item_status_name'] . '</option>';
    }
    return $str;
}
/* END JAYESH 21-07-2021 */
/* START JAYESH 21-07-2021  PURPOSE : For product type item reason  */
function get_product_item_type_reason_company($dbcon, $reasonid = '', $all = '')
{
    $str = '';
    $i = true;
    $query = "SELECT product_item_type_reason_id, product_item_type_reason_name FROM pro_ms_item_type_reason WHERE product_item_type_reason_status IN ('0','1') AND company_id =" . $_SESSION['company_id'];
    $rs_dispatch = $dbcon->query($query);
    if ($all == '')
    {
        $str = '<option value="">Select Reason</option>';
    }
    if ($all != '')
    {
        $str .= '<option value="">--ALL--</option>';
    }

    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        if ($rel['product_item_type_reason_id'] == $reasonid)
        {
            $sel = "selected='selected'";
        }

        $str .= '<option ' . $sel . ' value="' . $rel['product_item_type_reason_id'] . '">' . $rel['product_item_type_reason_name'] . '</option>';
    }
    return $str;
}

/* END JAYESH 21-07-2021 */

/* START JAYESH 15-07-2021  PURPOSE : common boolean value yes or no  */
function get_common_boolean_value($dbcon, $common_id)
{
    $str = '';
    $selyes = '';
    $selno = '';
    if ($common_id == 'yes')
    {
        $selyes = "selected='selected'";
    }
    if ($common_id == 'no')
    {
        $selno = "selected='selected'";
    }

    $str .= '<option ' . $selyes . '  value="yes">Yes</option>';
    $str .= '<option ' . $selno . ' value="no">No</option>';

    return $str;
}
/* END JAYESH 15-07-2021 */
/* START JAYESH 15-07-2021  PURPOSE :clone godown, purchase party, jobwork party, process list, qc parameter, Make  */
function clone_items_add_multiple_tabbing_data($dbcon, $latest_product_id, $product_id)
{

    $product_godown_res = $dbcon->query("SELECT * FROM tbl_branch_product_stock WHERE product_id = '$product_id'");
    $product_godown_counter = brp_mysqli_num_rows($product_godown_res);
    if ($product_godown_counter > o)
    {
        while ($pg_row = brp_mysqli_fetch_array($product_godown_res))
        {
            $pg_data = array();
            $pg_data['branch_id'] = $pg_row['branch_id'];
            $pg_data['product_id'] = $latest_product_id;
            $pg_data['priority'] = $pg_row['priority'];
            $pg_data['product_stock'] = $pg_row['product_stock'];
            $pg_data['status'] = $pg_row['status'];
            $pg_data['user_id'] = $_SESSION['user_id'];
            $pg_data['cdate'] = date("Y-m-d H:i:s");
            $pg_data['company_id'] = $_SESSION['company_id'];
            $table = 'tbl_branch_product_stock';
            $tableid = 'branch_product_stock_id';
            $inserid = add_record($table, $pg_data, $dbcon);
        }
    }

    $purchase_party_res = $dbcon->query("SELECT * FROM tbl_product_party_purchase WHERE party_product = '$product_id'");
    $purchase_party_counter = brp_mysqli_num_rows($purchase_party_res);
    if ($purchase_party_counter > o)
    {
        while ($pp_row = brp_mysqli_fetch_array($purchase_party_res))
        {
            $pp_data = array();
            $pp_data['party_id'] = $pp_row['party_id'];
            $pp_data['party_rate'] = $pp_row['party_rate'];
            $pp_data['party_product'] = $latest_product_id;
            $pp_data['cdate'] = date("Y-m-d H:i:s");
            $pp_data['user_id'] = $_SESSION['user_id'];
            $pp_data['company_id'] = $_SESSION['company_id'];
            $pp_data['branch_id'] = $pp_row['branch_id'];

            $table = 'tbl_product_party_purchase';
            $tableid = 'party_purchase_id';
            $inserid = add_record($table, $pp_data, $dbcon);
        }
    }

    $job_purchase_party_res = $dbcon->query("SELECT * FROM tbl_product_job_party_purchase WHERE job_party_product = '$product_id'");
    $job_purchase_party_counter = brp_mysqli_num_rows($job_purchase_party_res);
    if ($job_purchase_party_counter > o)
    {
        while ($jpp_row = brp_mysqli_fetch_array($job_purchase_party_res))
        {
            $jpp_data = array();
            $jpp_data['job_party_process_id'] = $jpp_row['job_party_process_id'];
            $jpp_data['job_party_id'] = $jpp_row['job_party_id'];
            $jpp_data['job_party_rate'] = $jpp_row['job_party_rate'];
            $jpp_data['job_party_product'] = $latest_product_id;
            $jpp_data['cdate'] = date("Y-m-d H:i:s");
            $jpp_data['user_id'] = $_SESSION['user_id'];
            $jpp_data['company_id'] = $_SESSION['company_id'];
            $jpp_data['branch_id'] = $jpp_row['branch_id'];
            $table = 'tbl_product_job_party_purchase';
            $tableid = 'job_party_purchase_id';
            $inserid = add_record($table, $jpp_data, $dbcon);
        }
    }

    $product_process_res = $dbcon->query("SELECT * FROM tbl_product_process WHERE status = 0 and product_id = '$product_id'");
    $product_process_counter = brp_mysqli_num_rows($product_process_res);
    if ($product_process_counter > o)
    {
        while ($ppq_row = brp_mysqli_fetch_array($product_process_res))
        {
            $ppq_data = array();
            $ppq_data['product_id'] = $latest_product_id;
            $ppq_data['resource_id'] = $ppq_row['resource_id'];
            $ppq_data['process_rate'] = $ppq_row['process_rate'];
            $ppq_data['process_priority'] = $ppq_row['process_priority'];
            $ppq_data['process_time'] = $ppq_row['process_time'];
            $ppq_data['process_type'] = $ppq_row['process_type'];
            $ppq_data['process_opening'] = $ppq_row['process_opening'];
            $ppq_data['process_id'] = $ppq_row['process_id'];
            $ppq_data['process_loss'] = $ppq_row['process_loss'];
            $ppq_data['process_scrap_tolerance_plus'] = $ppq_row['process_scrap_tolerance_plus'];
            $ppq_data['process_scrap_tolerance_minus'] = $ppq_row['process_scrap_tolerance_minus'];
            $ppq_data['cdate'] = date("Y-m-d H:i:s");
            $ppq_data['user_id'] = $_SESSION['user_id'];
            $ppq_data['company_id'] = $_SESSION['company_id'];
            //$ppq_data['branch_id']            = $jpp_row['branchid'];
            $table = 'tbl_product_process';
            $tableid = 'pr_process_id';
            $inserid = add_record($table, $ppq_data, $dbcon);
        }
    }

    $product_parameter_res = $dbcon->query("SELECT * FROM tbl_product_parameter WHERE product_id = '$product_id'");
    $product_parameter_counter = brp_mysqli_num_rows($product_parameter_res);
    if ($product_parameter_counter > o)
    {
        while ($prpq_row = brp_mysqli_fetch_array($product_parameter_res))
        {
            $prpq_data = array();
            $prpq_data['product_id'] = $latest_product_id;
            $prpq_data['param_value'] = $prpq_row['param_value'];
            $prpq_data['param_id'] = $prpq_row['param_id'];
            $prpq_data['tolerance_plus'] = $prpq_row['tolerance_plus'];
            $prpq_data['tolerance_minus'] = $prpq_row['tolerance_minus'];
            $prpq_data['unit_id'] = $prpq_row['unit_id'];
            $prpq_data['cdate'] = date("Y-m-d H:i:s");
            $prpq_data['user_id'] = $_SESSION['user_id'];
            $prpq_data['company_id'] = $_SESSION['company_id'];
            $prpq_data['branch_id'] = $prpq_row['branch_id'];
            $prpq_data['process_id'] = $prpq_row['process_id'];
            $table = 'tbl_product_parameter';
            $tableid = 'pr_param_id';
            $inserid = add_record($table, $prpq_data, $dbcon);
        }
    }

    $product_make_res = $dbcon->query("SELECT * FROM tbl_product_make_purchase WHERE make_product = '$product_id'");
    $product_make_counter = brp_mysqli_num_rows($product_make_res);
    if ($product_make_counter > o)
    {
        while ($pm_row = brp_mysqli_fetch_array($product_make_res))
        {
            $pm_data = array();
            $pm_data['make_id'] = $pm_row['make_id'];;
            $pm_data['make_number_id'] = $pm_row['param_value'];
            $pm_data['make_value'] = $pm_row['param_id'];
            $pm_data['make_rate'] = $pm_row['tolerance_plus'];
            $pm_data['make_stock'] = $pm_row['tolerance_minus'];
            $pm_data['make_product'] = $latest_product_id;
            $pm_data['cdate'] = date("Y-m-d H:i:s");
            $pm_data['user_id'] = $_SESSION['user_id'];
            $pm_data['company_id'] = $_SESSION['company_id'];
            $pm_data['branch_id'] = $pm_row['branch_id'];
            $table = 'tbl_product_make_purchase';
            $tableid = 'make_purchase_id';
            $inserid = add_record($table, $pm_data, $dbcon);
        }
    }

    $product_alternate_res = $dbcon->query("SELECT * FROM tbl_product_alternative_product WHERE product_id = '$product_id'");
    $product_alternate_counter = brp_mysqli_num_rows($product_alternate_res);
    if ($product_alternate_counter > o)
    {
        while ($pa_row = brp_mysqli_fetch_array($product_alternate_res))
        {
            $pa_data = array();
            $pa_data['alternative_product_id'] = $pa_row['alternative_product_id'];;
            $pa_data['product_id'] = $latest_product_id;
            $pa_data['cdate'] = date("Y-m-d H:i:s");
            $pa_data['user_id'] = $_SESSION['user_id'];
            $pa_data['company_id'] = $_SESSION['company_id'];
            $pa_data['branch_id'] = $pa_row['branch_id'];
            $table = 'tbl_product_alternative_product';
            $tableid = 'product_alternative_product_id';
            $inserid = add_record($table, $pa_data, $dbcon);
        }
    }

    $product_image_res = $dbcon->query("SELECT * FROM tbl_product_images WHERE im_product = '$product_id'");
    $product_image_counter = brp_mysqli_num_rows($product_image_res);
    if ($product_image_counter > o)
    {
        while ($pi_row = brp_mysqli_fetch_array($product_image_res))
        {
            $pi_data = array();
            $pi_data['im_product'] = $latest_product_id;
            $pi_data['im_name'] = $pi_row['im_name'];
            $pi_data['im_status'] = $pi_row['im_status'];
            $pi_data['cdate'] = date("Y-m-d H:i:s");
            $pi_data['user_id'] = $_SESSION['user_id'];
            $pi_data['company_id'] = $_SESSION['company_id'];
            $pi_data['branch_id'] = $pi_row['branch_id'];
            $table = 'tbl_product_images';
            $tableid = 'im_product';
            $inserid = add_record($table, $pi_data, $dbcon);
        }
    }
}

function check_document_type($dbcon, $file_name, $temp_file = '', $path = '', $height = '', $width = '', $size = '')
{

    $product_document_res = $dbcon->query("SELECT * FROM pro_ms_document_extensions WHERE document_extension_status IN(0,1)");
    $product_document_counter = brp_mysqli_num_rows($product_document_res);
    if ($product_document_counter > o)
    {
        $dcoument_array = array();
        while ($doc_row = brp_mysqli_fetch_array($product_document_res))
        {
            $dcoument_array[] = $doc_row['document_extension_name'];
        }
    }
    $test = explode('.', $file_name);
    $ext = end($test);
    //var_dump($ext);
    if (!file_exists($temp_file))
    {
        $response = array(
            "type" => "error",
            "message" => "Choose image file to upload."
        );
    } // Validate file input to check if is with valid extension
    else if (!in_array($ext, $dcoument_array))
    {
        $response = array(
            "type" => "error",
            "message" => "Upload valiid images. Only PNG and JPEG are allowed."
        );
    } // Validate image file size
    else if (($_FILES["file-input"]["size"] > 5000000))
    {
        $response = array(
            "type" => "error",
            "message" => "Image size exceeds 5MB"
        );
    } // Validate image file dimension
    else if ($width > "300" || $height > "200")
    {
        $response = array(
            "type" => "error",
            "message" => "Image dimension should be within 300X200"
        );
    }
    else
    {
        $name = time() . '.' . $ext;
        $location = $path . $name;
        /*var_dump($location);
        var_dump($temp_file);*/
        if (move_uploaded_file($temp_file, $location))
        {
            $response = array(
                "type" => "success",
                "message" => "Image uploaded successfully.",
                "name" => $name
            );
        }
        else
        {
            $response = array(
                "type" => "error",
                "message" => "Problem in uploading image files."
            );
        }
    }
    /*var_dump($response);*/
    return $response;
}

function get_hsn($dbcon, $hsn_id, $where)
{
    //add pathik
    $str = '';

    $query = "SELECT hsn_id,hsn_code,sale_gst,hsn_desc FROM `mst_hsn_code` where hsn_status=0 and company_id=".$_SESSION['company_id'];
    $rs_dispatch = $dbcon->query($query);
    $str .= '<option value="0">--Select HSN Code--</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        if ($rel['hsn_id'] == $hsn_id)
        {
            $sel = "selected='selected'";
        }

        $str .= '<option data-salegst=' . $rel['sale_gst'] . '  ' . $sel . ' value="' . $rel['hsn_id'] . '">' . $rel['hsn_code'] . ' - ' . $rel['hsn_desc'] . '</option>';
    }
    return $str;
}

function get_group_ledger_admin($dbcon, $sales_group, $where)
{

    $str = '';

    $query = "select * from tbl_ledger as pro where l_status=0 " . $where . " and company_id = $_SESSION[company_id] and l_group IN ($sales_group) order by TRIM(l_name) ASC";

    $rs_dispatch = $dbcon->query($query);
    $str .= '<option value="0">--select ledger--</option>';
    while ($rel = mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        if ($rel['l_id'] == $ledger_id)
        {
            $sel = "selected='selected'";
        }

        $str .= '<option ' . $sel . ' value="' . $rel['l_id'] . '">' . $rel['l_name'] . '</option>';
    }
    return $str;
}
//Added by dhruv
function getAddedBillSundry($dbcon)
{
    $id = $_REQUEST['id'];
    if (isset($id))
    {
        $ledger_id = $id;
    }
    else
    {
        $ledger_id = 0;
    }
    $qry = "SELECT * FROM `tbl_ledger_bill_sundry` where isdelete=0 and sundry_ledger_id=" . $ledger_id . "";
    $result = $dbcon->query($qry);
    $row = brp_mysqli_fetch_assoc($result);
    return $row;
}
//End dhruv function
//Added by Maulik Kapatel
function load_process_out_side($dbcon, $prod_id, $eid)
{
    $pro = '';
    $s_pro = "select process.*,proc.process_name,proc.process_id from tbl_product_process as process
    left join process_mst as proc on proc.process_id=process.process_id
    where process.status = 0 and process.product_id=" . $prod_id . " GROUP BY proc.process_id";

    //var_dump($s_pro);
    $rs_pro = $dbcon->query($s_pro);
    $pro .= '<option value="">Choose Process</option>';
    while ($r = brp_mysqli_fetch_assoc($rs_pro))
    {
        $sel = '';
        if ($r['process_id'] == $eid)
        {
            $sel = 'selected="selected"';
        }
        $pro .= '<option ' . $sel . ' value="' . $r['process_id'] . '">' . $r['process_name'] . '</option>';
    }
    return $pro;
}

// Added by Sanat :: 22-09-21
function count_store_request($dbcon)
{
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
    $whre = "";
    if (!empty($_SESSION['branch_id']))
    {
        $whre = " and branch_id=" . $branch_id;
    }

    $query = "Select Count(*) as total_request
    From    (
    select count(store_request_id) as total_request from tbl_store_request where store_request_status = 0 and (base_qty - release_qty) > 0 and company_id=" . $_SESSION['company_id'] . $whre . "  group by product_id
) As total_request";
/*$query="select count(store_request_id) as total_request from tbl_store_request where (base_qty - release_qty) != 0 and company_id=".$_SESSION['company_id'].$whre. " group by product_id";*/
    // echo $query;
$rs_cust = $dbcon->query($query);
$rel = brp_mysqli_fetch_array($rs_cust);

$total = $rel['total_request'];

if ($total == 0)
{
    return 0;
}
else
{
    return $total;
}
}

// Sanat Start :: 17/09/2021 :: Comment : Add Stock batch wise


// Sanat end :: 17/09/2021


function load_bom_product($dbcon, $bom_id, $p_bom_id)
{
    $sqls = $dbcon->query("SELECT trn.*, pro.product_name from tbl_bomtrn as trn LEFT JOIN product_mst AS pro ON pro.product_id=trn.product_id where bom_id='" . $p_bom_id . "' AND bom_trn_status=0");
    if (brp_mysqli_num_rows($sqls) > 0)
    {
        while ($res = brp_mysqli_fetch_assoc($sqls))
        {
            $response .= '<option value="' . $res['product_id'] . '">' . $res['product_name'] . '</option>';
            $response .= load_bom_product($dbcon, $res['bom_id'], $res['p_bom_id']);
        }
        return $response;
    }
}

// End Sanat function
//  Sanat Start :: 17/09/2021 :: Comment : Add Stock batch wise
function update_batch_no($dbcon, $product_id)
{
    $company_config = getCompanyConfiguration($dbcon);
    if ($company_config['batch_wise_stock'] == '1' && $company_config['batch_no_stock'] == '1')
    {
        $dbcon->query("UPDATE product_mst SET taxinvoice_start = taxinvoice_start +1 WHERE product_id = " . $product_id);
    }
    else
    {
        $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = 30 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id']);
    }
}
function get_batch_no($dbcon, $product_id)
{
    $company_config = getCompanyConfiguration($dbcon,'');

    if ($company_config['batch_wise_stock'] == '1')
    { // batch_wise_stock  0 : no , 1 : yes
        // batch wise stock permission - yes
        if ($company_config['batch_no_stock'] == '0')
        { // batch_no_stock  0 : general wise, 1 : product wise
            // GENERAL WISE STOCK BATCH NO
            $query = "select * from tbl_invoicetype where status=0 and type_id=30 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
            $result = $dbcon->query($query);
            $row = brp_mysqli_fetch_assoc($result);
            $rows = array();
            $query1 = "select * from  tbl_invoicetype where invoicetype_id=" . $row['invoicetype_id'];
            $rows = brp_mysqli_fetch_assoc($dbcon->query($query1));
            $id = $rows['taxinvoice_start'];
            $id = $id + 1;

            if ($rows['invoice_format'] == '2')
            {
                return $row['invoiceno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
            }
            else if ($rows['invoice_format'] == '1')
            {
                return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
            }
            else if ($rows['invoice_format'] == '3')
            {
                return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
            }
            else
            {
                return $row['invoiceno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
            }
        }
        else
        {
            // PRODUCT WISE STOCK BATCH NO
            $query = "select * from  product_mst where product_id = " . $product_id;
            $result = $dbcon->query($query);
            $rows = brp_mysqli_fetch_assoc($result);

            $id = $rows['taxinvoice_start'];
            $id = $id + 1;

            return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
        }
    }
    else
    {
        // batch wise stock permission - no
        $query = "select * from tbl_invoicetype where status=0 and type_id=30 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
        $result = $dbcon->query($query);
        $row = brp_mysqli_fetch_assoc($result);
        $rows = array();
        $query1 = "select * from  tbl_invoicetype where invoicetype_id=" . $row['invoicetype_id'];
        $rows = brp_mysqli_fetch_assoc($dbcon->query($query1));
        $id = $rows['taxinvoice_start'];
        $id = $id + 1;

        if ($rows['invoice_format'] == '2')
        {
            return $row['invoiceno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
        }
        else if ($rows['invoice_format'] == '1')
        {
            return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
        }
        else if ($rows['invoice_format'] == '3')
        {
            return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
        }
        else
        {
            return $row['invoiceno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
        }
    }
}


function get_issue_no($dbcon)
{
    $query = "select * from tbl_invoicetype where status=0 and type_id=32 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
    $result = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($result);
    $rows = array();
    $query1 = "select * from  tbl_invoicetype where invoicetype_id=" . $row['invoicetype_id'];
    $rows = brp_mysqli_fetch_assoc($dbcon->query($query1));
    $id = $rows['taxinvoice_start'];
    $id = $id + 1;

    if ($rows['invoice_format'] == '2')
    {
        return $row['invoiceno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
    }
    else if ($rows['invoice_format'] == '1')
    {
        return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
    }
    else if ($rows['invoice_format'] == '3')
    {
        return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
    }
    else
    {
        return $row['invoiceno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
    }
}

function update_issue_no($dbcon)
{
    $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = 32 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id']);
}

/* changes by jayesh for getting work in process data */
function get_current_stock_new_data($dbcon, $pro_id, $unit_id, $godown_id, $customer_id = "")
{
    $where = "";
    if ($customer_id != "")
    {
        $where = " AND customer_id = " . $customer_id;
    }

    $query = 'SELECT pro.product_id,base_stock_add,base_stock_minus,con_stock_minus,con_stock_add FROM `product_mst` as pro 

    left join (select sum(qc.base_stock) as base_stock_add,qc.product_id from tbl_stock_trn as qc 

      where qc.stock_status!=2 and stock_flage=1 and qc.base_unit=' . $unit_id . ' and qc.company_id=' . $_SESSION['company_id'] . ' AND qc.godown_id=' . $godown_id . $where . ' 
      group by qc.product_id ) as qc on qc.product_id=pro.product_id

      left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status!=2 and stock_flage=2 and qc.base_unit=' . $unit_id . ' and qc.company_id=' . $_SESSION['company_id'] . ' AND qc.godown_id=' . $godown_id . $where . ' 
      group by qc.product_id) as qc1 on qc1.product_id=pro.product_id

      left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . ' and qc.company_id=' . $_SESSION['company_id'] . ' AND qc.godown_id=' . $godown_id . $where . '
      group by qc.product_id) as qc2 on qc2.product_id=pro.product_id

      left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status!=2 and stock_flage=2 and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . ' and qc.company_id=' . $_SESSION['company_id'] . ' AND qc.godown_id=' . $godown_id . $where . ' 

      group by qc.product_id) as qc3 on qc3.product_id=pro.product_id

      where pro.product_id=' . $pro_id;
      $rows = brp_mysqli_fetch_assoc($dbcon->query($query));
    //echo "<pre>"; print_r($rows);
      $stock = ($rows['base_stock_add'] + $rows['con_stock_add']) - ($rows['base_stock_minus'] + $rows['con_stock_minus']);

    //$stock=($row['base_stock_add']+$row['con_stock_add'])-($row['base_stock_minus']+$row['con_stock_minus']);


      return floatval($stock);

      /*SELECT * FROM `product_mst` as pro left join tbl_stock_trn as qc  on qc.product_id=pro.product_id where pro.product_id='.$pro_id.' and qc.company_id='.$_SESSION['company_id'];*/

    /*$query='SELECT * FROM `product_mst` as pro
    
    left join (select *,sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc
    where qc.stock_status != 2 and stock_flage=1 and qc.base_unit='.$unit_id.' and qc.company_id='.$_SESSION['company_id'].'
    ) as qc on qc.product_id=pro.product_id
    
    left join (select *  from tbl_stock_trn as qc
    where qc.stock_status != 2 and stock_flage=2 and qc.base_unit='.$unit_id.' and qc.company_id='.$_SESSION['company_id'].'
    ) as qc1 on qc1.product_id=pro.product_id
    
    left join (select * from tbl_stock_trn as qc
    where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit='.$unit_id.' and qc.company_id='.$_SESSION['company_id'].'
    ) as qc2 on qc2.product_id=pro.product_id
    
    left join (select * from tbl_stock_trn as qc
    where qc.stock_status != 2 and stock_flage=2 and qc.base_unit!=qc.convert_unit and qc.convert_unit='.$unit_id.' and qc.company_id='.$_SESSION['company_id'].'
    ) as qc3 on qc3.product_id=pro.product_id
    
    where pro.product_id='.$pro_id;
    return $rows=brp_mysqli_fetch_assoc($dbcon->query($query));*/
}
function reserve_stock_data($dbcon, $product_id, $unit_id, $reserve_id, $request_id, $complaint_id, $sales_order_trn_id, $branch_id, $is_store_approval, $godown_id = "")
{

    if (!empty($reserve_id))
    {
        $rwhser = " and reserve_id=" . $reserve_id;
        $rwhser22 = " and ref_id=" . $reserve_id;
    }
    if (!empty($request_id))
    {
        $rwhser1 = " and request_id=" . $request_id;
    }
    if (!empty($complaint_id))
    {
        $rwhser2 = " and complaint_id=" . $complaint_id;
    }
    if (!empty($sales_order_trn_id))
    {
        $rwhser23 = " and sales_order_trn_id=" . $sales_order_trn_id;
    }
    if (!empty($branch_id))
    {
        $where_branch = " and branch_id=" . $branch_id;
    }

    if (!empty($godown_id))
    {
        $where_branch = " and godown_id=" . $godown_id;
    }

    if ($is_store_approval)
    {

        $query1 = "select sum(approve_base_stock) as base_addqty from tbl_reserve_stock where stock_status !=2 and stock_flage=1 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id;
        $result1 = $dbcon->query($query1);
        $row1 = mysqli_fetch_assoc($result1);

        $query2 = "select sum(approve_convert_stock) as conv_addqty from tbl_reserve_stock where stock_status !=2 and base_unit!=convert_unit and stock_flage=1 " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id;
        $result2 = $dbcon->query($query2);
        $row2 = mysqli_fetch_assoc($result2);

        $query3 = "select sum(approve_base_stock) as base_usedqty from tbl_reserve_stock where stock_status !=2 and stock_flage=2 and base_unit=" . $unit_id . " " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id;
        $result3 = $dbcon->query($query3);
        $row3 = mysqli_fetch_assoc($result3);

        $query4 = "select sum(approve_convert_stock) as conv_usedqty from tbl_reserve_stock where stock_status !=2 and base_unit!=convert_unit " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " and company_id=" . $_SESSION['company_id'] . " and stock_flage=2 and convert_unit=" . $unit_id . " and product_id=" . $product_id;

        $result4 = $dbcon->query($query4);
        $row4 = mysqli_fetch_assoc($result4);
    }
    else
    {
        $query1 = "select sum(base_stock) as base_addqty from tbl_reserve_stock where stock_status !=2 and stock_flage=1 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id;
        $result1 = $dbcon->query($query1);
        $row1 = mysqli_fetch_assoc($result1);

        $query2 = "select sum(convert_stock) as conv_addqty from tbl_reserve_stock where stock_status !=2 and base_unit!=convert_unit and stock_flage=1 " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id;
        $result2 = $dbcon->query($query2);
        $row2 = mysqli_fetch_assoc($result2);

        $query3 = "select sum(base_stock) as base_usedqty from tbl_reserve_stock where stock_status !=2 and stock_flage=2 and base_unit=" . $unit_id . " " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id;
        $result3 = $dbcon->query($query3);
        $row3 = mysqli_fetch_assoc($result3);

        $query4 = "select sum(convert_stock) as conv_usedqty from tbl_reserve_stock where stock_status !=2 and base_unit!=convert_unit " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " and company_id=" . $_SESSION['company_id'] . " and stock_flage=2 and convert_unit=" . $unit_id . " and product_id=" . $product_id;

        $result4 = $dbcon->query($query4);
        $row4 = mysqli_fetch_assoc($result4);

        $query5 = "select sum(approve_base_stock) as base_addqty from tbl_reserve_stock where stock_status !=2 and stock_flage=1 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id;
        $result5 = $dbcon->query($query5);
        $row5 = mysqli_fetch_assoc($result5);

        $query6 = "select sum(approve_convert_stock) as conv_addqty from tbl_reserve_stock where stock_status !=2 and base_unit!=convert_unit and stock_flage=1 " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id;
        $result6 = $dbcon->query($query6);
        $row6 = mysqli_fetch_assoc($result6);
    }
    $res_qty = ($row1['base_addqty'] + $row2['conv_addqty']) - ($row3['base_usedqty'] + $row4['conv_usedqty']);

    return $res_qty;
}
/* chagnes by jayesh */

//  Sanat end :: 17/09/2021
//Start Maulik 08-10-2021
function get_tax_cetegory_ledger($dbcon, $category)
{
    $query = "SELECT * FROM tbl_tax_category WHERE isdelete = 0 and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);
    $str .= '<option value="" >--Choose Tax Type--</option>';
    while ($row = mysqli_fetch_assoc($rs_type))
    {
        $sel = '';
        if ($row['tax_cat_id'] == $category)
        {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['tax_cat_id'] . '">' . $row['tax_cat_name'] . '</option>';
    }
    return $str;
}
function get_tax_cat_val($dbcon, $tax_id)
{
    $query = "select * from tbl_tax_category where tax_cat_id=" . $tax_id;
    $rs_type = $dbcon->query($query);
    return brp_mysqli_fetch_assoc($rs_type);
}

function get_shift_type($dbcon, $shift_time)
{
    $query = "SELECT * FROM hrms_shift_type WHERE  company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);
    $str .= '<option value="" >--Choose Shift Type--</option>';
    while ($row = mysqli_fetch_assoc($rs_type))
    {
        $sel = '';
        if ($row['id'] == $shift_time)
        {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['id'] . '">' . $row['shift_type_name'] . '(' . $row['shift_start_time'] . '-' . $row['shift_end_time'] . ')</option>';
    }
    return $str;
}
function get_current_opening_stock($dbcon, $pro_id, $unit_id, $start_date, $end_date = "")
{
    $whr = "";

    if ($end_date != "")
    {
        $whr = ' and stock_date <= "' . date('Y-m-d', strtotime($end_date)) . '"';
    }
    $query = 'SELECT pro.product_id,base_stock_add,base_stock_minus,con_stock_minus,con_stock_add,opening_stock_pl,opening_stock_mi FROM `product_mst` as pro 

    left join (select sum(qc.base_stock) as opening_stock_pl,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status != 2 and stock_flage=1 and qc.base_unit=' . $unit_id . ' and stock_date >= "' . date('Y-m-d', strtotime($start_date)) . '" and ref_name="opening_stock" and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
      group by qc.product_id) as qc4 on qc4.product_id=pro.product_id

      left join (select sum(qc.base_stock) as opening_stock_mi,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status != 2 and stock_flage=2 and qc.base_unit=' . $unit_id . ' and stock_date >= "' . date('Y-m-d', strtotime($start_date)) . '" and ref_name="opening_stock" and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
      group by qc.product_id) as qc5 on qc5.product_id=pro.product_id

      left join (select sum(qc.base_stock) as base_stock_add,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status != 2 and stock_flage=1 and qc.base_unit=' . $unit_id . ' and stock_date =< "' . date('Y-m-d', strtotime($start_date)) . '" and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
      group by qc.product_id) as qc on qc.product_id=pro.product_id

      left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status != 2 and stock_flage=2 and qc.base_unit=' . $unit_id . ' and stock_date =< "' . date('Y-m-d', strtotime($start_date)) . '" and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
      group by qc.product_id) as qc1 on qc1.product_id=pro.product_id

      left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . ' and stock_date =< "' . date('Y-m-d', strtotime($start_date)) . '" and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
      group by qc.product_id) as qc2 on qc2.product_id=pro.product_id

      left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status != 2 and stock_flage=2 and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . ' and stock_date =< "' . date('Y-m-d', strtotime($start_date)) . '" and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
      group by qc.product_id) as qc3 on qc3.product_id=pro.product_id

      where pro.product_id=' . $pro_id;
      $rows = mysqli_fetch_assoc($dbcon->query($query));
      $stock = ($rows['base_stock_add'] + $rows['con_stock_add'] + $rows['opening_stock_pl']) - ($rows['base_stock_minus'] + $rows['con_stock_minus'] - $rows['opening_stock_mi']);

    //$stock=($row['base_stock_add']+$row['con_stock_add'])-($row['base_stock_minus']+$row['con_stock_minus']);


      return floatval($stock);
    //return $query;

  }
  function get_stock_ledger($dbcon, $ref_name, $ref_id)
  {
    if ($ref_name == "tbl_grn_trn" && $ref_id != "")
    {
        $q = "select grn.grn_no,led.l_name,gtrn.grn_id from tbl_grn_trn as gtrn
        left join tbl_grn as grn on grn.grn_id=gtrn.grn_id
        left join tbl_ledger as led on led.l_id=grn.vender_id
        where gtrn.grn_trn_id=" . $ref_id;

        $rows = mysqli_fetch_assoc($dbcon->query($q));
        $auto_no = "Grn No. : " . $rows['grn_no'];
        $vender_name = "(" . $rows['l_name'] . ")";
        $desc = "<a href='" . ROOT . PRODUCTION_ROOT . "grn_print/" . $rows['grn_id'] . "' target='_blank' title='" . $auto_no . "'>" . $auto_no . " " . $vender_name . "</a>";
    }
    else if ($ref_name == "tbl_qc" && $ref_id != "")
    {
        $q = "select qc.qc_no, gtrn.grn_id from tbl_qc as qc left join tbl_grn_trn as gtrn on qc.grn_trn_id = gtrn.grn_trn_id where qc_id =" . $ref_id;
        $rows = mysqli_fetch_assoc($dbcon->query($q));
        $auto_no = "QC No. : " . $rows['qc_no'];
        $vender_name = "";
        $desc = "<a href='" . ROOT . PRODUCTION_ROOT . "grn_print/" . $rows['grn_id'] . "' target='_blank' title='" . $auto_no . "'>" . $auto_no . " " . $vender_name . "</a>";
    }
    else if ($ref_name == "invoice_trn" && $ref_id != "")
    {
        $q = "select inv.invoice_no,led.l_name,itrn.invoice_id from tbl_invoicetrn as itrn
        left join tbl_invoice as inv on inv.invoice_id = itrn.invoice_id
        left join tbl_ledger as led on led.l_id = inv.cust_id
        where itrn.trancation_id =" . $ref_id;
        $rows = mysqli_fetch_assoc($dbcon->query($q));
        $auto_no = "Invoice No. : " . $rows['invoice_no'];
        $vender_name = "(" . $rows['l_name'] . ")";
        $desc = "<a href='" . ROOT . PRINT_ROOT . "invoicereceipt/" . $rows['invoice_id'] . "' target='_blank' title='" . $auto_no . "'>" . $auto_no . " " . $vender_name . "</a>";
    }
    else if ($ref_name == "Grn" && $ref_id != "")
    {
        $q = "select grn.grn_no,led.l_name,grn.vender_id from tbl_grn as grn
        left join tbl_ledger as led on led.l_id = grn.vender_id
        where grn.grn_id=" . $ref_id;
        $rows = mysqli_fetch_assoc($dbcon->query($q));
        $auto_no = "Grn No. : " . $rows['grn_no'];
        if ($rows['vender_id'] != '-1')
        {
            $vender_name = "(" . $rows['l_name'] . ")";
        }
        else
        {
            $vender_name = "(INHOUSE)";
        }
        $desc = "<a href='" . ROOT . PRODUCTION_ROOT . "grn_print/" . $ref_id . "' target='_blank' title='" . $auto_no . "'>" . $auto_no . " " . $vender_name . "</a>";
    }
    else if ($ref_name == "returning_receipt" && $ref_id != "")
    {
        $q = "select chn.id,chn.channal_id,chn.returnable_type from tbl_returnable_channal_item as c
        left join tbl_returnable_channal as chn on chn.id = c.returnable_id
        where c.id =" . $ref_id;
        $rows = mysqli_fetch_assoc($dbcon->query($q));
        if ($rows['returnable_type'] == "non-returnable")
        {
            $auto_no = "Non Returnable Chalan. : " . $rows['channal_id'];
        }
        else
        {
            $auto_no = "Returnable Chalan. : " . $rows['channal_id'];
        }

        $desc = "<a href='" . ROOT . PRINT_ROOT . "challan_print/" . $rows['id'] . "' target='_blank' title='" . $auto_no . "'>" . $auto_no . "</a>";
    }
    else if ($ref_name == "Grn_sub_trn" && $ref_id != "")
    {
        $q = "select grn.grn_no,led.l_name,gtrn.grn_id from tbl_grn_sub_trn as strn
        left join tbl_grn_trn as gtrn on gtrn.grn_trn_id=strn.grn_trn_id
        left join tbl_grn as grn on grn.grn_id=gtrn.grn_id
        left join tbl_ledger as led on led.l_id=grn.vender_id
        where strn.grn_trn_sub_id=" . $ref_id;

        $rows = mysqli_fetch_assoc($dbcon->query($q));
        $auto_no = "Grn No. : " . $rows['grn_no'];
        $vender_name = "(" . $rows['l_name'] . ")";
        $desc = "<a href='" . ROOT . PRODUCTION_ROOT . "grn_print/" . $rows['grn_id'] . "' target='_blank' title='" . $auto_no . "'>" . $auto_no . " " . $vender_name . "</a>";
    }
    else if ($ref_name == "opening_stock" && $ref_id != "")
    {
        $desc = "Opening Stock";
    }
    else if ($ref_name == "purchase_return_trn" && $ref_id != "")
    {

    }
    else if ($ref_name == "tbl_store_release_trn" && $ref_id != "")
    {
        $q = "select release_id,issue_no,p_id from tbl_store_release
        where release_id=" . $ref_id;

       $rows = mysqli_fetch_assoc($dbcon->query($q));
      $vender_name = "";
        $desc = "<a href='" . ROOT . PRINT_ROOT . "material_release_print/" . $rows['p_id'] ."/". $rows['release_id'] . "' target='_blank' title='Issue No : " . $rows['issue_no'] . "'> Store Released : " . $rows['issue_no'] . " " . $vender_name . "</a>";
    } else if ($ref_name == "store_release_deduct" && $ref_id != "")
    {
        $q = "select release_id,issue_no,p_id from tbl_store_release
        where release_id=" . $ref_id;

       $rows = mysqli_fetch_assoc($dbcon->query($q));
      $vender_name = "";
        $desc = "<a href='" . ROOT . PRINT_ROOT . "material_release_print/" . $rows['p_id'] ."/". $rows['release_id'] . "' target='_blank' title='Issue No : " . $rows['issue_no'] . "'> Store Released : " . $rows['issue_no'] . " " . $vender_name . "</a>";

    } else if ($ref_name == "store_release" && $ref_id != "")
    {
        $q = "select release_id,issue_no,p_id from tbl_store_release
        where release_id=" . $ref_id;

        $rows = mysqli_fetch_assoc($dbcon->query($q));
        $desc = "ON FLOOR STOCK ADDED :  ". $rows['issue_no'] ;
    }
    else if ($ref_name == "returnable" && $ref_id != "")
    {
        $q = "select chn.id,chn.channal_id,chn.returnable_type from tbl_returnable_channal_item as c
        left join tbl_returnable_channal as chn on chn.id = c.returnable_id
        where c.id =" . $ref_id;
        $rows = mysqli_fetch_assoc($dbcon->query($q));
        if ($rows['returnable_type'] == "non-returnable")
        {
            $auto_no = "Non Returnable Chalan. : " . $rows['channal_id'];
        }
        else
        {
            $auto_no = "Returnable Chalan. : " . $rows['channal_id'];
        }

        $desc = "<a href='" . ROOT . PRINT_ROOT . "challan_print/" . $rows['invoice_id'] . "' target='_blank' title='" . $auto_no . "'>" . $auto_no . "</a>";
        

    }
    else if ($ref_name == "tbl_store_return_material" && $ref_id != "")
    {

    }
     else if ($ref_name == "production_bypass" && $ref_id != "")
    {
        $q = "select trn.general_stock_trn_id,gen.general_stock_id,gen.general_stock_no from tbl_general_stock_trn as trn
        left join tbl_general_stock as gen on gen.general_stock_id = trn.general_stock_id
        where trn.general_stock_trn_id =" . $ref_id;
        $rows = brp_mysqli_fetch_assoc($dbcon->query($q));
        $auto_no = "Stock General Production Bypass : " . $rows['general_stock_no'];
        
        $desc = "<a href='" . ROOT . PRINT_ROOT . "stock_general_print/".$rows['general_stock_id']."' target='_blank' title='" . $auto_no . "'>" . $auto_no . "</a>";
        
    }
    else
    {
        $desc = $ref_name . " " . $ref_id;
    }
    return $desc;
}

function count_grn_apporve($dbcon)
{
    /*$query="select count(grn_trn_id) as total_request from tbl_grn_trn where store_accept=0 and product_qc=1 and company_id=".$_SESSION['company_id'].$whre;*/

   /* $query = "SELECT count(batch.batch_id) as total_request FROM tbl_batch_data as batch left join tbl_grn_trn as sr on sr.grn_trn_id=batch.grn_trn_id left join product_mst as p on p.product_id=sr.product_id left join tbl_grn as grn on grn.grn_id=sr.grn_id left join unit_mst as umst on umst.unitid=sr.unit_id left join mst_godown as gda on gda.gd_id=sr.grn_godown where ( 1 AND  batch.status = 0 and batch.qc_status = 1 and batch.accept_qty > 0 and batch.stock_approval_status = 0 and sr.grn_trn_status=0 and sr.product_qc=1 and batch.reprocess_qc = 0 and sr.store_accept=0 and sr.company_id=" . $_SESSION['company_id'] . ") ORDER BY batch.batch_id";*/

   $query = "SELECT  count(batch.batch_id) as total_request FROM tbl_batch_data as batch left join tbl_grn_trn as sr on sr.grn_trn_id=batch.grn_trn_id left join product_mst as p on p.product_id=batch.product_id left join tbl_grn as grn on grn.grn_id=sr.grn_id left join unit_mst as umst on umst.unitid=batch.batch_unit left join mst_godown as gda on gda.gd_id=sr.grn_godown left join tbl_qc_reject_new_product as rej_qc on rej_qc.batch_id = batch.batch_id and rej_qc.qc_id = batch.qc_id and rej_qc.product_id = batch.product_id left join tbl_qc as qc on qc.qc_id = rej_qc.qc_id where ( 1 AND batch.status = 0 and batch.qc_status = 1 and batch.grn_accept_qty > 0 and batch.accept_qty > 0  and batch.stock_approval_status = 0 and batch.reprocess_qc = 0 and batch.company_id=".$_SESSION['company_id'].") ORDER BY batch.batch_id desc";

        $rs_cust = $dbcon->query($query);
        $rel = brp_mysqli_fetch_array($rs_cust);

        $total = $rel['total_request'];

        if ($total == 0)
        {
            return 0;
        }
        else
        {
            return $total;
        }
    }

// added by sanat :: 14-12-21
    function count_returnable_chalan_grn_pending($dbcon)
    {
        /*$query="select count(grn_trn_id) as total_request from tbl_grn_trn where store_accept=0 and product_qc=1 and company_id=".$_SESSION['company_id'].$whre;*/

        $query = "SELECT count(grn.id) as total_pending_grn FROM tbl_returnable_channal_item as grn 
        left JOIN tbl_returnable_channal as chn ON chn.id = grn.returnable_id
        where grn.company_id=" . $_SESSION['company_id'] . " and grn.status  = 0 and grn.approve_status = 1 and grn.grn_status = 0 and chn.returnable_type = 'returnable'";

        $rs_cust = $dbcon->query($query);
        $rel = brp_mysqli_fetch_array($rs_cust);

        $total = $rel['total_pending_grn'];

        if ($total == 0)
        {
            return 0;
        }
        else
        {
            return $total;
        }
    }

// added by sanat :: 14-10-21
    function count_direct_material_approval_request($dbcon)
    {
        $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
        $whre = "";
        if (!empty($_SESSION['branch_id']))
        {
            $whre = " and branch_id=" . $branch_id;
        }

        $query = "select count(release_id) as total_apv_request from tbl_store_release where release_type = 1 and release_status = 0 and company_id=" . $_SESSION['company_id'] . $whre;

    // echo $query;
        $rs_cust = $dbcon->query($query);
        $rel = brp_mysqli_fetch_array($rs_cust);

        $total = $rel['total_request'];

        $total = $rel['total_apv_request'];

        if ($total == 0)
        {
            return 0;
        }
        else
        {
            return $total;
        }
    }
   function tbl_transcation_entry($dbcon, $type, $transaction_no, $transaction_id, $description, $amount)
{
    $info = array();
    $branch_id = $_SESSION['branch_id'];

    $info['type'] = $type;
    $info['transaction_no'] = $transaction_no;
    $info['transaction_id'] = $transaction_id;
    $info['description'] = $description;
    $info['amount'] = $amount;
    $info['branch_id'] = $branch_id;
    $info['user_id'] = $_SESSION['user_id'];
    $info['company_id'] = $_SESSION['company_id'];
    $info['cdate'] = date("Y-m-d H:i:s");
    $info['show_date'] = date('Y-m-d');

    $tran_dashboard_id = add_record('tbl_trnsaction_dashbord', $info, $dbcon, $branch_id);

    if ($tran_dashboard_id)
    {
        $companyConfiguration = getCompanyConfiguration($dbcon);
        $user_type_list = trim($companyConfiguration['trans_dash_user_type']);

        if (!empty($user_type_list)) {

            $q = $dbcon->query("
                SELECT GROUP_CONCAT(user_id SEPARATOR ',') AS user_ids 
                FROM users 
                WHERE active = 0 
                  AND company_id = " . (int)$_SESSION['company_id'] . " 
                  AND user_type IN ($user_type_list)
            ");

            $res = brp_mysqli_fetch_assoc($q);
            $users = $res['user_ids'] ?? '';
            $user_ids = !empty($users) ? explode(',', $users) : [];

            foreach ($user_ids as $user) {
                $infotrn = [
                    'user_id'        => $_SESSION['user_id'],
                    'trn_dashbord_id'=> $tran_dashboard_id,
                    'company_id'     => $_SESSION['company_id']
                ];
                add_record('tbl_trnsaction_dashbord_user', $infotrn, $dbcon);
            }

        } else {
            // log or handle missing configuration
            error_log("tbl_transcation_entry skipped user mapping — empty trans_dash_user_type\n", 3, __DIR__ . "/query_debug.log");
        }
    }
}

function get_all_so_for_grn($dbcon, $vender_id, $mode, $potype)
{
    $ven = '';
    $ty = '';
    if (!empty($vender_id))
    {
        $ven = " and cust_id=" . $vender_id;
    }
    if ($potype == '5')
    {
        $ty = " and jobwork_type=1";
    }
    $str = '';
    $query = "select * from tbl_sales_order as so where so.approve_status=3  " . $ven . " " . $ty . " and company_id=" . $_SESSION['company_id'];

    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Order</option>';
    while ($res = brp_mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        /*if($res['purchaseorder_id']==$purchaseorder_id)
        {$sel ="selected='selected'";}*/
        $str .= '<option ' . $sel . ' value="' . $res['sales_order_id'] . '">' . $res['sales_order_no'] . '</option>';
    }

    return $str;
}

function get_all_returnable_for_grn($dbcon, $vender_id, $mode, $potype)
{
    $ven = '';
    $ty = '';
    if (!empty($vender_id))
    {
        $ven = " and cust_id=" . $vender_id;
    }

    $str = '';
    $query = "select id,channal_id from tbl_returnable_channal where returnable_type = 'returnable' and grn_status = 0 and status = 0 and company_id=" . $_SESSION['company_id'];

    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Order</option>';
    while ($res = brp_mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        /*if($res['purchaseorder_id']==$purchaseorder_id)
        {$sel ="selected='selected'";}*/
        $str .= '<option ' . $sel . ' value="' . $res['id'] . '">' . $res['channal_id'] . '</option>';
    }

    return $str;
}

function getrequiredproductcat($dbcon, $id)
{
    $query = "select * from product_mst as p inner join tbl_category as c on c.cat_id = p.product_category where p.product_id='$id'";
    $row = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($row);
    return $rel['cat_name'];
}
// added by maulik kapatel
function get_godown_stock_check($dbcon, $product_id, $unit_id, $branch_id)
{
    $temp = $response = [];
    $query = "select gd_id,gd_name from mst_godown where g_status=0";
    $rs_dispatch = $dbcon->query($query);
    while ($rel = mysqli_fetch_assoc($rs_dispatch))
    {
        $godown_id = $rel['gd_id'];
        $getcurrentStock = get_current_godown_stock_new($dbcon, $product_id, $unit_id, $godown_id, $branch_id);
        if ($getcurrentStock)
        {
            $temp[$rel['gd_id']] = $getcurrentStock;
        }
    }
    return $temp;
}

function get_store_accept_no($dbcon)
{
    $query = "select * from tbl_invoicetype where status=0 and type_id=36 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
    $result = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($result);
    $rows = array();
    $query1 = "select * from  tbl_invoicetype where invoicetype_id=" . $row['invoicetype_id'];
    $rows = brp_mysqli_fetch_assoc($dbcon->query($query1));
    $id = $rows['taxinvoice_start'];
    $id = $id + 1;

    if ($rows['invoice_format'] == '2')
    {
        return $row['invoiceno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
    }
    else if ($rows['invoice_format'] == '1')
    {
        return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
    }
    else if ($rows['invoice_format'] == '3')
    {
        return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
    }
    else
    {
        return $row['invoiceno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
    }
}

function update_store_accept_no($dbcon)
{
    $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = 36 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id']);
}

function count_so_wise_bom($dbcon)
{

    /*if($_SESSION['user_type'] != '2'){
        $where_db = check_branch('so', $_SESSION['branch_id']);
    }*/
    $companyConfiguration = getCompanyConfiguration($dbcon);
    if($companyConfiguration['sales_wise_branch_planning_before_bom']==0){
        $bomsetting="";
            if($_SESSION['user_type'] != '2'){
                $where_db=" and so_trn.branch_id=".$_SESSION['branch_id'];
            }else{
                $where_db="";
            }
        }else{
            $bomsetting=" and so_trn.production_branch_id!=0";
            if($_SESSION['user_type'] != '2'){
                $where_db=" and so_trn.production_branch_id=".$_SESSION['branch_id'];
            }else{
                    $where_db="";
               
            }
        }        

     $query = "SELECT so.sales_order_no, so_trn.sales_ordertrn_id, so.sales_order_date, pro.product_name, so_trn.product_id, bov.bom_no, bom.bom_id, so_trn.product_qty, so.branch_id, pro.bom_required, so.jobwork_type FROM tbl_sales_ordertrn as so_trn left join product_mst as pro on pro.product_id=so_trn.product_id left join tbl_sales_order as so on so.sales_order_id = so_trn.sales_order_id left join pro_ms_bom_version as bov on bov.product_id = so_trn.product_id and is_default_bom=1 left join tbl_bom as bom on bom.bom_version_id=bov.bom_version_id where ( 1 AND 1 and so_trn.bom_status=0 and so_trn.short_close_status=0 and so_trn.invoice_status=0 and so_trn.sales_ordertrn_status=0 AND so.order_accept_status = '1' ".$bomsetting." and so.company_id=" . $_SESSION['company_id'] . " ".$where_db.") Group by so_trn.sales_ordertrn_id ORDER BY so.sales_order_no desc ";

        $rs = $dbcon->query($query);
        $cnt = brp_mysqli_num_rows($rs);

        return $cnt;
    }
//Devloped by Maulik Kapatel
    function get_series_type($dbcon, $id)
    {
        $query = "SELECT * FROM tbl_module_type WHERE status = 0";
        $rs_type = $dbcon->query($query);
        $str .= '<option value="" >--Choose Series Type--</option>';
        while ($row = mysqli_fetch_assoc($rs_type))
        {
            $sel = '';
            if ($row['module_type_id'] == $id)
            {
                $sel = 'selected="selected"';
            }
            $str .= '<option ' . $sel . ' value="' . $row['module_type_id'] . '">' . $row['module_name'] . '</option>';
        }
        return $str;
    }

    function get_po_card_rate($dbcon, $product_id, $vender_id, $unit_id)
    {
        $today_date = date('Y-m-d');
        $query = "select cardtrn.price, cardtrn.purchasecardtrn_id, cardtrn.discount_percentage from tbl_purchasecardtrn as cardtrn 
        left join tbl_product_party_purchase as pcard on pcard.party_purchase_id=cardtrn.party_purchase_id
        where pcard.card_status=0 and cardtrn.purchasecardtrn_status=0 and cardtrn.valid_date>='$today_date' and cardtrn.affected_date<='$today_date' and pcard.is_aproove=1 and pcard.is_active=0 and cardtrn.product_id='$product_id' and cardtrn.vendor_id='$vender_id' and cardtrn.unit_id='$unit_id'";

    //var_dump($query);
        $rs_type = $dbcon->query($query);
        $row = mysqli_fetch_assoc($rs_type);
        return $row;
    }

//maulik start 16-12-2021
    function get_po_disapproved_reason($dbcon, $tbl_name, $column_name, $po_id, $check_status, $status_value, $tbl_id)
    {
        $query = "select " . $column_name . " as remark from " . $tbl_name . " where purchaseorder_id=" . $po_id . " and " . $check_status . " = " . $status_value . " ORDER BY " . $tbl_id . " DESC LIMIT 1";
        $rs_type = $dbcon->query($query);
        $row = brp_mysqli_fetch_array($rs_type);
    //var_dump($query);
        return $row['remark'];
    }
//maulik start 20-12-2021
//hardi start 6-1-2022
    function get_so_disapproved_reason($dbcon, $tbl_name, $column_name, $po_id, $check_status, $status_value, $tbl_id)
    {
        $query = "SELECT " . $column_name . " as remark from " . $tbl_name . " where sales_order_id=" . $po_id . " and " . $check_status . " = " . $status_value . " ORDER BY " . $tbl_id . " DESC LIMIT 1";
        $rs_type = $dbcon->query($query);
        $row = brp_mysqli_fetch_array($rs_type);
    // var_dump($query);
        return $row['remark'];
    }
    function get_quot_disapproved_reason($dbcon, $tbl_name, $column_name, $po_id, $check_status, $status_value, $tbl_id)
    {
        $query = "SELECT " . $column_name . " as remark from " . $tbl_name . " where quotation_id=" . $po_id . " and " . $check_status . " = " . $status_value . " ORDER BY " . $tbl_id . " DESC LIMIT 1";
        $rs_type = $dbcon->query($query);
        $row = brp_mysqli_fetch_array($rs_type);
    //var_dump($query);
        return $row['remark'];
    }
    function get_oa_disapproved_reason($dbcon, $tbl_name, $column_name, $po_id, $check_status, $status_value, $tbl_id)
    {
        $query = "SELECT " . $column_name . " as remark from " . $tbl_name . " where so_id=" . $po_id . " and " . $check_status . " = " . $status_value . " ORDER BY " . $tbl_id . " DESC LIMIT 1";
        $rs_type = $dbcon->query($query);
        $row = brp_mysqli_fetch_array($rs_type);
    //var_dump($query);
        return $row['remark'];
    }
//hardi end 6-1-2022
    function get_salesorder_no_returnable($dbcon, $cust_id, $sales_id)
    {
        if ($sales_id == '')
        {
            //$query = "select sales_order_id,sales_order_no,(select count(returnable_status) from tbl_sales_ordertrn as trn where trn.sales_ordertrn_status = 0 and returnable_status!=1  and trn.sales_order_id = so.sales_order_id ) as cnt from tbl_sales_order as so
            //where so.sales_order_status=0 and so.order_accept_status=1 and so.sales_type='sales' and so.cust_id =" . $cust_id . " HAVING cnt>0 ";

            $query = "select sales_order_id,sales_order_no,(select count(returnable_status) from tbl_sales_ordertrn as trn where trn.sales_ordertrn_status = 0 and returnable_status!=1  and trn.sales_order_id = so.sales_order_id ) as cnt from tbl_sales_order as so where so.sales_order_status=0 and so.order_accept_status=1  and so.cust_id =" . $cust_id . " HAVING cnt>0 ";
        }
        else
        {
            $query = "select sales_order_id,sales_order_no from tbl_sales_order where sales_order_id=" . $sales_id;
        }

    //var_dump($query);
        $rs_dispatch = $dbcon->query($query);
        $str .= '<option value="" >Choose Sales Order No</option>';
        while ($res = brp_mysqli_fetch_assoc($rs_dispatch))
        {
            $sel = '';
            if ($res['sales_order_id'] == $sales_id)
            {
                $sel = "selected='selected'";
            }
            $str .= '<option ' . $sel . ' value="' . $res['sales_order_id'] . '">' . $res['sales_order_no'] . '</option>';
        }

        return $str;
    }

/*
    START ::  Added by Sanat :: 16-12-21
*/

    function count_re_process_start_qty($dbcon, $id, $type)
    {

        $branch_whre = "";
        if (!empty($_SESSION['branch_id']))
        {
            $branch_whre = " and branch_id=" . $_SESSION['branch_id'];
        }

        $query = "select IFNULL(sum(pen_qty),0) as sqty,IFNULL(sum(start_qty),0) as start_qty from tbl_allocate_re_process where process_id='$id' " . $branch_whre . " and company_id=" . $_SESSION['company_id'] . " and pr_process_type='$type'";

        $rs_cust = $dbcon->query($query);
        $rel = brp_mysqli_fetch_array($rs_cust);

    //$total=$rel['sqty']-$rel['stqty'];
        $total = $rel['sqty'] - $rel['start_qty'];

        if ($total == 0)
        {
            return 0;
        }
        else
        {
            return $total;
        }
    }

    function count_re_process_end_qty($dbcon, $id, $type)
    {

        $branch_whre = "";
        if (!empty($_SESSION['branch_id']))
        {
            $branch_whre = " and branch_id=" . $_SESSION['branch_id'];
        }

        $query = "select IFNULL(sum(start_qty),0) as start_qty from tbl_allocate_re_process where process_id='$id' " . $branch_whre . " and company_id=" . $_SESSION['company_id'] . " and pr_process_type='$type'";

        $rs_cust = $dbcon->query($query);
        $rel = brp_mysqli_fetch_array($rs_cust);

    //$total=$rel['sqty']-$rel['stqty'];
        $total = $rel['start_qty'];

        if ($total == 0)
        {
            return 0;
        }
        else
        {
            return $total;
        }
    }

    function get_returnable_salesorderwise_done($dbcon, $returnable_id)
    {
        $query = "select rtrn.* from tbl_returnable_channal_item as rtrn where rtrn.returnable_id=" . $returnable_id . " and rtrn.status=0";
        $rs_cust = $dbcon->query($query);
        while ($rel = brp_mysqli_fetch_array($rs_cust))
        {
            $query1 = "select strn.*,(select sum(item_qty) from tbl_returnable_channal_item as rtrn where rtrn.sales_ordertrn_id = strn.sales_ordertrn_id and status=0) as challan_qty from tbl_sales_ordertrn as strn where strn.sales_ordertrn_id=" . $rel['sales_ordertrn_id'];
            $rs_cust1 = $dbcon->query($query1);
            $rel1 = brp_mysqli_fetch_array($rs_cust1);
            if ($rel1['challan_qty'] >= $rel1['product_qty'])
            {
                $info['returnable_status'] = 1;
                update_record('tbl_sales_ordertrn', $info, "sales_ordertrn_id=" . $rel1['sales_ordertrn_id'], $dbcon);
            }
        }
    }

    function get_ledger_by_group_new($dbcon, $group_id)
    {
        $sub_groups = array_column($dbcon->query("SELECT g_id FROM `tbl_group` WHERE g_status = 0 And `g_pid`= " . $group_id)->fetch_all(MYSQLI_ASSOC) , 'g_id');
        $legders = '';
        if ($sub_groups)
        {
            foreach ($sub_groups as $subgroup)
            {
                $sub_ledger_qry = "SELECT group_concat(l_id) as sub_ledger FROM `tbl_ledger` WHERE `l_group` IN (" . $subgroup . ")";
                    $sub_ledger = $dbcon->query($sub_ledger_qry)->fetch_object()->sub_ledger;
                    $legders .= $sub_ledger;
                    get_ledger_by_group_new($dbcon, $subgroup);
                }
            }
            $sub_ledger_qry1 = "SELECT group_concat(l_id) as sub_ledger1 FROM `tbl_ledger` WHERE `l_group` IN ('$group_id')";
            $sub_ledger1 = $dbcon->query($sub_ledger_qry1)->fetch_object()->sub_ledger1;
            $legders .= $sub_ledger1;
            return $legders;
        }

        function get_all_tds_cat($dbcon, $edit_id = '')
        {
            $str = "";
            $q = "select * from tbl_tds_tax_category where isdelete='0'";
            $sel = $dbcon->query($q);
            $str .= "<option value=''>--Select Paty Category--</option>";
            while ($row = brp_mysqli_fetch_assoc($sel))
            {
                if ($edit_id == $row['tds_cat_id'])
                {
                    $select = 'selected';
                }
                else
                {
                    $select = '';
                }

                $str .= "<option value='" . $row['tds_cat_id'] . "' " . $select . ">" . $row['tds_cat_name'] . "</option>";
            }

            echo $str;
        }
        function check_img_type($dbcon, $file_name, $temp_file = '', $path = '', $height = '', $width = '', $size = '')
        {

            $product_document_res = $dbcon->query("SELECT * FROM pro_ms_document_extensions WHERE document_extension_status IN(0,1)");
            $product_document_counter = brp_mysqli_num_rows($product_document_res);
            if ($product_document_counter > o)
            {
                $dcoument_array = array();
                while ($doc_row = brp_mysqli_fetch_array($product_document_res))
                {
                    $dcoument_array[] = $doc_row['document_extension_name'];
                }
            }
            $test = explode('.', $file_name);
            $ext = end($test);
            if (!file_exists($temp_file))
            {
                $response = array(
                    "type" => "error",
                    "message" => "Choose image file to upload."
                );
    } // Validate file input to check if is with valid extension
    else if (!in_array($ext, $dcoument_array))
    {
        $response = array(
            "type" => "error",
            "message" => "Upload valiid images. Only PNG and JPEG are allowed."
        );
    } // Validate image file size
    else if (($_FILES["file-input"]["size"] > 5000000))
    {
        $response = array(
            "type" => "error",
            "message" => "Image size exceeds 5MB"
        );
    } // Validate image file dimension
    else if ($width > "300" || $height > "200")
    {
        $response = array(
            "type" => "error",
            "message" => "Image dimension should be within 300X200"
        );
    }
    else
    {
        $name = $test[0] . '.' . $ext;
        $location = $path . $name;
        if (move_uploaded_file($temp_file, $location))
        {
            $response = array(
                "type" => "success",
                "message" => "Image uploaded successfully.",
                "name" => $name
            );
        }
        else
        {
            $response = array(
                "type" => "error",
                "message" => "Problem in uploading image files."
            );
        }
    }
    return $response;
}
function get_tempimages_product($dbcon, $id)
{
    $q = "select * from product_mst_images where im_product=$id and im_status=1";
    $rel = $dbcon->query($q);
    $path = 'view/upload/umaboy_erp_data/';
    $str = "";
    $str .= "<table><tr>";
    while ($row = brp_mysqli_fetch_assoc($rel))
    {
        $str .= '<td>
        <div class="img-wrap">
        <span class="close">&times;</span>
        <img src="' . ROOT . 'view/img/close_img.jpg" width="30" height="30">
        </div>
        <img src="' . ROOT . $path . $row['im_name'] . '" height="150" width="225" class="img-thumbnail" />

        </td>';
    }
    $str .= "</tr></table>";
    return $str;
}

function reserve_stock_new($dbcon, $product_id, $unit_id, $reserve_id, $request_id, $complaint_id, $sales_order_trn_id, $branch_id, $is_store_approval, $p_id, $godown_id, $batch_id)
{

    if (!empty($reserve_id))
    {
        $rwhser = " and reserve_id=" . $reserve_id;
        $rwhser22 = " and ref_id=" . $reserve_id;
    }
    if (!empty($request_id))
    {
        $rwhser1 = " and request_id=" . $request_id;
    }
    if (!empty($complaint_id))
    {
        $rwhser2 = " and complaint_id=" . $complaint_id;
    }
    if (!empty($sales_order_trn_id))
    {
        $rwhser23 = " and sales_order_trn_id=" . $sales_order_trn_id;
    }
    if (!empty($branch_id))
    {
        $where_branch = " and branch_id=" . $branch_id;
    }

    if (!empty($p_id))
    {
        $where_branch = " and p_id=" . $p_id;
    }

    if (!empty($godown_id))
    {
        $where_godown = " and godown_id=" . $godown_id;
    }
    if (!empty($batch_id))
    {
        $where_batch = " and stock_id=" . $batch_id;
    }

    if ($is_store_approval)
    {
        $query1 = "select IFNULL(sum(approve_base_stock),0) as base_addqty from tbl_reserve_stock where customer_id = '' and customer_id = 0 and stock_status in (0,1) and stock_flage=1 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id;
        $result1 = $dbcon->query($query1);
        $row1 = mysqli_fetch_assoc($result1);

        $query2 = "select IFNULL(sum(approve_convert_stock),0) as conv_addqty from tbl_reserve_stock where customer_id = '' and customer_id = 0 and stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id;
        $result2 = $dbcon->query($query2);
        $row2 = mysqli_fetch_assoc($result2);

        $query3 = "select IFNULL(sum(approve_base_stock),0) as base_usedqty from tbl_reserve_stock where customer_id = '' and customer_id = 0 and stock_status=0 and stock_flage=2 and base_unit=" . $unit_id . " " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id;
        $result3 = $dbcon->query($query3);
        $row3 = mysqli_fetch_assoc($result3);

        $query4 = "select IFNULL(sum(approve_convert_stock),0) as conv_usedqty from tbl_reserve_stock where customer_id = '' and customer_id = 0 and stock_status=0 and base_unit!=convert_unit " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and stock_flage=2 and convert_unit=" . $unit_id . " and product_id=" . $product_id;

        $result4 = $dbcon->query($query4);
        $row4 = mysqli_fetch_assoc($result4);

        $res_qty = ($row1['base_addqty'] + $row2['conv_addqty']) - ($row3['base_usedqty'] + $row4['conv_usedqty']);
    }
    else
    {

        $query1 = "select IFNULL(sum(base_stock),0) as base_addqty from tbl_reserve_stock where customer_id = '' and customer_id = 0 and stock_status in (0,1) and stock_flage=1 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id;
        $result1 = $dbcon->query($query1);
        $row1 = mysqli_fetch_assoc($result1);

        $query2 = "select IFNULL(sum(convert_stock),0) as conv_addqty from tbl_reserve_stock where customer_id = '' and customer_id = 0 and stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id;
        $result2 = $dbcon->query($query2);
        $row2 = mysqli_fetch_assoc($result2);

        $query3 = "select IFNULL(sum(base_stock),0) as base_usedqty from tbl_reserve_stock where customer_id = '' and customer_id = 0 and stock_status=0 and stock_flage=2 and base_unit=" . $unit_id . " " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id;
        $result3 = $dbcon->query($query3);
        $row3 = mysqli_fetch_assoc($result3);

        $query4 = "select IFNULL(sum(convert_stock),0) as conv_usedqty from tbl_reserve_stock where customer_id = '' and customer_id = 0 and stock_status=0 and base_unit!=convert_unit " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and stock_flage=2 and convert_unit=" . $unit_id . " and product_id=" . $product_id;

        $result4 = $dbcon->query($query4);
        $row4 = mysqli_fetch_assoc($result4);

        $query5 = "select IFNULL(sum(approve_base_stock),0) as base_addqty from tbl_reserve_stock where customer_id = '' and customer_id = 0 and stock_status in (0,1) and stock_flage=1 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id;
        $result5 = $dbcon->query($query5);
        $row5 = mysqli_fetch_assoc($result5);

        $query6 = "select IFNULL(sum(approve_convert_stock),0) as conv_addqty from tbl_reserve_stock where customer_id = '' and customer_id = 0 and stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id;
        $result6 = $dbcon->query($query6);
        $row6 = mysqli_fetch_assoc($result6);

        $res_qty = ($row1['base_addqty'] + $row2['conv_addqty']) - ($row3['base_usedqty'] + $row4['conv_usedqty']);
    }
    //$j=$row1['base_addqty']."-1".$row2['conv_addqty']."-2".$row3['base_usedqty']."-3".$row4['conv_usedqty'];
    return $res_qty;
    //return $query1;
    //return $j;
    
}

function get_all_tds_cat_ledger($dbcon, $edit_id = '')
{
    $str = "";
    $q = "select * from tbl_tds_tax_category where isdelete='0'";
    $sel = $dbcon->query($q);
    $str .= "<option value=''>--Select Paty Category--</option>";
    while ($row = brp_mysqli_fetch_assoc($sel))
    {
        if ($edit_id == $row['tds_cat_id'])
        {
            $select = 'selected';
        }
        else
        {
            $select = '';
        }

        $str .= "<option value='" . $row['effected_ledger_id'] . "' " . $select . ">" . $row['tds_cat_name'] . "</option>";
    }

    echo $str;
}

function get_purchaseorder_no($dbcon, $purchaseorder_id)
{
    $str = "";
    $q = "select * from tbl_purchaseorder where status='0' and company_id=" . $_SESSION['company_id'];
    $sel = $dbcon->query($q);
    $str .= "<option value=''>--Select PO No.--</option>";
    while ($row = brp_mysqli_fetch_assoc($sel))
    {
        if ($purchaseorder_id == $row['purchaseorder_id'])
        {
            $select = 'selected';
        }
        else
        {
            $select = '';
        }

        $str .= "<option value='" . $row['purchaseorder_id'] . "' " . $select . ">" . $row['purchaseorder_no'] . "</option>";
    }

    echo $str;
}

function get_shift_days_company($dbcon, $daysid = '', $all = '')
{
    $str = '';
    $i = true;
    $query = "SELECT * from days_mst WHERE status = '1'";
    $rs_dispatch = $dbcon->query($query);
    if ($all == '')
    {
        $str = '<option value="">Select Shift Days</option>';
    }
    if ($all != '')
    {
        $str .= '<option value="">--ALL--</option>';
    }

    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch))
    {
        $sel = '';
        $daysids = explode(",", $daysid);
        if (in_array($rel['days_id'], $daysids))
        {
            $sel = "selected='selected'";
        }

        $str .= '<option ' . $sel . ' value="' . $rel['days_id'] . '">' . $rel['days_name'] . '</option>';
    }
    return $str;
}

function get_product_name($dbcon, $id)
{
    $query = "select p.product_id,p.product_name from product_mst as p
    where p.product_id='" . $id . "'";

    $product_info = $dbcon->query($query);
    $rel = mysqli_fetch_assoc($product_info);

    return $rel['product_name'];
}

function in_po_sales_order_no($dbcon, $purchaseorder_id)
{
    $sales_order_no = "select trn.purchaseordertrn_id, trn.po_ref_id, req.sp_id from tbl_purchaseordertrn as trn
    left join tbl_request_product as req on req.rp_id = trn.po_ref_id
    where purchaseorder_id=" . $purchaseorder_id . " group by req.sp_id";
    //var_dump($sales_order_no);
    $sales_order_no_e = $dbcon->query($sales_order_no);
    $sales_no = "";
    $client_name = "";

    while ($rel = brp_mysqli_fetch_array($sales_order_no_e))
    {

        //$sales_order_trn_id=get_so_no_po_ref($dbcon,$rel['perent_id']);
        $q = "SELECT sales_order_trn_id FROM tbl_request_product WHERE sp_id='" . $rel['sp_id'] . "' AND main_request=1 GROUP BY sp_id";
        $e = $dbcon->query($q);
        $r = brp_mysqli_fetch_array($e);

        $so_no = "select so.sales_order_no,led.l_name from tbl_sales_ordertrn as strn
        left join tbl_sales_order as so on so.sales_order_id = strn.sales_order_id
        left join tbl_ledger as led on led.l_id = so.cust_id
        where strn.sales_ordertrn_id=" . $r['sales_order_trn_id'];

        //var_dump($so_no);
        $so_no_e = $dbcon->query($so_no);
        $so_no_r = brp_mysqli_fetch_array($so_no_e);
        $sales_no .= $so_no_r['sales_order_no'] . "<br>";
        $client_name .= $so_no_r['l_name'] . "<br>";
    }
    return $sales_no;
}

function quotation_print_with_bom($dbcon, $bom_id, $qty, $num, $call, $space)
{
    $query_m = "select * from tbl_bom as bom where bom_status=0 and bom_id=" . $bom_id;
    $result_m = $dbcon->query($query_m);
    $rel_m = mysqli_fetch_assoc($result_m);

    $query1 = "select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name 
    from tbl_bomtrn as bom_trn 
    left join product_mst as pro on pro.product_id=bom_trn.product_id
    left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
    left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
    where bom_trn_status=0 and bom_id=" . $bom_id;
    $result1 = $dbcon->query($query1);

    $text_color = '';
    if ($call == '0')
    {
        // $text_color = 'background-color:cornflowerblue;color:white;';

    }
    $k = 1;
    $new_call = $call + 1;
    for ($x = 1;$x <= $call;$x++)
    {
        $space = $space . "&nbsp;&nbsp;";
    }
    while ($rel1 = mysqli_fetch_assoc($result1))
    {
        // $brand_qry = "SELECT group_concat(pb_name SEPARATOR '/') as brand_name FROM `tbl_product_brand`
        //                   WHERE `pb_id` IN (".$rel1['product_brand'].")";
        //               //$brands = $dbcon->query($brand_qry)->fetch_object()->brand_name;
        //               $result = mysqli_query($dbcon,$brand_qry);
        //               $brands = mysqli_fetch_all($result,MYSQLI_ASSOC);
        $new_num = ($num != 0) ? $num . "." . $k : $k;
        $base_one_qty = $rel1['product_base_qty'] / $rel_m['product_base_qty'];
        $base_qty = $base_one_qty * $qty;
        //$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");
        $html .= '<tr>
        <td style="' . $text_color . '">' . $new_num . '</td>
        <td style="' . $text_color . '">' . $rel1['product_name'] . '</td>
        <td style="text-align: center;' . $text_color . '">' . get_product_type_by_id($dbcon, $rel1['product_type']) . '</td>
        <td style="text-align: center;' . $text_color . '">' . $rel1['base_unit_name'] . '</td>
        <td style="text-align: center;' . $text_color . '">' . number_format((float)$base_one_qty, 2, '.', '') . '</td>
        <td style="text-align: center;' . $text_color . '">' . number_format((float)$base_qty, 2, '.', '') . '</td>
        </tr>';
        $html .= quotation_print_with_bom($dbcon, $rel1['p_bom_id'], $base_qty, $new_num, $new_call, $space);
        $k++;
    }
    return $html;
}

function quotation_print_with_bom_for_gew_divya($dbcon, $bom_id, $qty, $num, $call, $space)
{
     $html = '';
    $query_m = "select * from tbl_bom as bom where bom_status=0 and bom_id=" . $bom_id;
    $result_m = $dbcon->query($query_m);
    $rel_m = mysqli_fetch_assoc($result_m);

    $query1 = "select bom_trn.*,pro.product_name,pro.make_by,pro.part_number,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name 
    from tbl_bomtrn as bom_trn 
    left join product_mst as pro on pro.product_id=bom_trn.product_id
    left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
    left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
    where bom_trn_status=0 and bom_id=" . $bom_id;
    $result1 = $dbcon->query($query1);

    $text_color = '';
    if ($call == '0')
    {
        // $text_color = 'background-color:cornflowerblue;color:white;';

    }
    $k = 1;
    $new_call = $call + 1;
    for ($x = 1;$x <= $call;$x++)
    {
        $space = $space . "&nbsp;&nbsp;";
    }
    while ($rel1 = mysqli_fetch_assoc($result1))
    {
        // $brand_qry = "SELECT group_concat(pb_name SEPARATOR '/') as brand_name FROM `tbl_product_brand`
        //                   WHERE `pb_id` IN (".$rel1['product_brand'].")";
        //               //$brands = $dbcon->query($brand_qry)->fetch_object()->brand_name;
        //               $result = mysqli_query($dbcon,$brand_qry);
        //               $brands = mysqli_fetch_all($result,MYSQLI_ASSOC);
        $new_num = ($num != 0) ? $num . "." . $k : $k;
        $base_one_qty = $rel1['product_base_qty'] / $rel_m['product_base_qty'];
        $base_qty = $base_one_qty * $qty;
        //$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");
        $html .= '<tr>
        <td style="text-align:center;' . $text_color . '">' . $new_num . '</td>
        <td style="text-align:left;' . $text_color . '">' . $rel1['product_name'] . '</td>
        <td style="text-align:center;' . $text_color . '">' . $rel1['make_by'] . '</td>
        <td style="text-align:center;' . $text_color . '">' . $rel1['part_number'] . '</td>
        <td style="text-align: center;' . $text_color . '">' . $rel1['base_unit_name'] . '</td>
        <td style="text-align: center;' . $text_color . '">' . number_format((float)$base_qty, 2, '.', '') . '</td>
        </tr>';
        $html .= quotation_print_with_bom_for_gew_divya($dbcon, $rel1['p_bom_id'], $base_qty, $new_num, $new_call, $space);
        $k++;
    }
    return $html;
}

function work_hours_diff($date1, $date2)
{
    if ($date1 > $date2)
    {
        $tmp = $date1;
        $date1 = $date2;
        $date2 = $tmp;
        unset($tmp);
        $sign = - 1;
    }
    else $sign = 1;
    if ($date1 == $date2) return 0;

    $days = 0;
    $working_days = array(
        1,
        2,
        3,
        4,
        5
    ); // Monday-->Friday
    $working_hours = array(
        8.5,
        17.5
    ); // from 8:30(am) to 17:30
    $current_date = $date1;
    $beg_h = floor($working_hours[0]);
    $beg_m = ($working_hours[0] * 60) % 60;
    $end_h = floor($working_hours[1]);
    $end_m = ($working_hours[1] * 60) % 60;

    // setup the very next first working timestamp
    if (!in_array(date('w', $current_date) , $working_days))
    {
        // the current day is not a working day
        // the current timestamp is set at the begining of the working day
        $current_date = mktime($beg_h, $beg_m, 0, date('n', $current_date) , date('j', $current_date) , date('Y', $current_date));
        // search for the next working day
        while (!in_array(date('w', $current_date) , $working_days))
        {
            $current_date += 24 * 3600; // next day
            
        }
    }
    else
    {
        // check if the current timestamp is inside working hours
        $date0 = mktime($beg_h, $beg_m, 0, date('n', $current_date) , date('j', $current_date) , date('Y', $current_date));
        // it's before working hours, let's update it
        if ($current_date < $date0) $current_date = $date0;

        $date3 = mktime($end_h, $end_m, 59, date('n', $current_date) , date('j', $current_date) , date('Y', $current_date));
        if ($date3 < $current_date)
        {
            // outch ! it's after working hours, let's find the next working day
            $current_date += 24 * 3600; // the day after
            // and set timestamp as the begining of the working day
            $current_date = mktime($beg_h, $beg_m, 0, date('n', $current_date) , date('j', $current_date) , date('Y', $current_date));
            while (!in_array(date('w', $current_date) , $working_days))
            {
                $current_date += 24 * 3600; // next day
                
            }
        }
    }

    // so, $current_date is now the first working timestamp available...
    // calculate the number of seconds from current timestamp to the end of the working day
    $date0 = mktime($end_h, $end_m, 59, date('n', $current_date) , date('j', $current_date) , date('Y', $current_date));
    $seconds = $date0 - $current_date + 1;

    printf("\nFrom %s To %s : %d hours\n", date('d/m/y H:i', $date1) , date('d/m/y H:i', $date0) , $seconds / 3600);

    // calculate the number of days from the current day to the end day
    $date3 = mktime($beg_h, $beg_m, 0, date('n', $date2) , date('j', $date2) , date('Y', $date2));
    while ($current_date < $date3)
    {
        $current_date += 24 * 3600; // next day
        if (in_array(date('w', $current_date) , $working_days)) $days++; // it's a working day
        
    }
    if ($days > 0) $days--; //because we've allready count the first day (in $seconds)
    printf("\nFrom %s To %s : %d working days\n", date('d/m/y H:i', $date1) , date('d/m/y H:i', $date3) , $days);

    // check if end's timestamp is inside working hours
    $date0 = mktime($beg_h, 0, 0, date('n', $date2) , date('j', $date2) , date('Y', $date2));
    if ($date2 < $date0)
    {
        // it's before, so nothing more !

    }
    else
    {
        // is it after ?
        $date3 = mktime($end_h, $end_m, 59, date('n', $date2) , date('j', $date2) , date('Y', $date2));
        if ($date2 > $date3) $date2 = $date3;
        // calculate the number of seconds from current timestamp to the final timestamp
        $tmp = $date2 - $date0 + 1;
        $seconds += $tmp;
        printf("\nFrom %s To %s : %d hours\n", date('d/m/y H:i', $date2) , date('d/m/y H:i', $date3) , $tmp / 3600);
    }

    // calculate the working days in seconds
    $seconds += 3600 * ($working_hours[1] - $working_hours[0]) * $days;

    printf("\nFrom %s To %s : %d hours\n", date('d/m/y H:i', $date1) , date('d/m/y H:i', $date2) , $seconds / 3600);

    return $sign * $seconds / 3600; // to get hours
    
}

function get_total_hours($shour, $ehour)
{
    $hourdiff = round((strtotime($shour) - strtotime($ehour)) / 3600, 1);
    return $hourdiff;
}

function get_month_wise_stock($dbcon, $product_id, $inout_status)
{
    $where = "";

    $s_day = 1;
    $s_month = 4;
    $current_year = date("Y");
    $current_month = date("m");

    $e_day = 31;
    $e_month = 3;

    if ($current_month >= 4)
    {
        $date = mktime(12, 0, 0, $s_month, $s_day, $current_year);
        $cdate = mktime(12, 0, 0, $e_month, $e_day, $current_year + 1);
    }
    else
    {
        $date = mktime(12, 0, 0, $s_month, $s_day, $current_year - 1);
        $cdate = mktime(12, 0, 0, $e_month, $e_day, $current_year);
    }
    $start_date = date("Y-m-d", $date);
    $ending_date = date("Y-m-d", $cdate);
    if ($inout_status == 1)
    {
        $where .= "and stock.stock_flage=1";
    }
    else
    {
        $where .= "and stock.stock_flage=2";
    }
    $opening_bal = 'select sum(stock.base_stock) as opening_bal,unit.unit_name from tbl_stock_trn as stock 
    left join unit_mst as unit on unit.unitid = stock.base_unit
    where stock.stock_status = 0 and stock.stock_date<="' . date('Y-m-d', strtotime($start_date)) . '" and stock.company_id=' . $_SESSION['company_id'] . ' and stock.product_id=' . $product_id . ' ' . $where;
    $opening_bal1 = $dbcon->query($opening_bal);
    $opening_balr = brp_mysqli_fetch_array($opening_bal1);

    $opening_balance = 'select sum(stock.base_stock) as opening_balance,unit.unit_name from tbl_stock_trn as stock
    left join unit_mst as unit on unit.unitid = stock.base_unit
    where stock.stock_status = 0 and stock.stock_date>="' . date('Y-m-d', strtotime($start_date)) . '" and stock.ref_name = "opening_stock" and stock.company_id=' . $_SESSION['company_id'] . ' and stock.product_id=' . $product_id . ' ' . $where;
    $opening_balance1 = $dbcon->query($opening_balance);
    $opening_balancer = brp_mysqli_fetch_array($opening_balance1);

    $month_query = "select  pro.product_name,unit.unit_name,
    sum(case when 4  = MONTH(stock.stock_date) then stock.base_stock else 0 end) 'April',
    sum(case when 5  = MONTH(stock.stock_date) then stock.base_stock else 0 end) 'May',
    sum(case when 6  = MONTH(stock.stock_date) then stock.base_stock else 0 end) 'June',
    sum(case when 7  = MONTH(stock.stock_date) then stock.base_stock else 0 end) 'July',
    sum(case when 8  = MONTH(stock.stock_date) then stock.base_stock else 0 end) 'August',
    sum(case when 9  = MONTH(stock.stock_date) then stock.base_stock else 0 end) 'September',
    sum(case when 10 = MONTH(stock.stock_date) then stock.base_stock else 0 end) 'October',
    sum(case when 11 = MONTH(stock.stock_date) then stock.base_stock else 0 end) 'November',
    sum(case when 12 = MONTH(stock.stock_date) then stock.base_stock else 0 end) 'December',
    sum(case when 1  = MONTH(stock.stock_date) then stock.base_stock else 0 end) 'January',
    sum(case when 2  = MONTH(stock.stock_date) then stock.base_stock else 0 end) 'February',
    sum(case when 3  = MONTH(stock.stock_date) then stock.base_stock else 0 end) 'March'
    from tbl_stock_trn as stock 
    left join product_mst as pro on pro.product_id=stock.product_id
    left join unit_mst as unit on unit.unitid = stock.base_unit
    where stock.stock_status = 0 and stock.ref_name != 'opening_stock' and stock.stock_date between '" . date('Y-m-d', strtotime($start_date)) . "' and '" . date('Y-m-d', strtotime($ending_date)) . "' and stock.company_id=" . $_SESSION['company_id'] . " and stock.product_id=" . $product_id . ' ' . $where;

    $result = $dbcon->query($month_query);

    $row = brp_mysqli_fetch_array($result);
    $row['opening_bal'] = $opening_balr['opening_bal'];
    $row['opening_balance'] = $opening_balancer['opening_balance'];
    if ($opening_balr['unit_name'])
    {
        $row['opening_unit'] = $opening_balr['unit_name'];
    }
    else
    {
        $row['opening_unit'] = $opening_balancer['unit_name'];
    }
    return $row;
}
function get_print_path($dbcon, $print_type_id)
{
    $str = '';
    $menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='" . $_SESSION['company_id'] . "'");
    $rels = mysqli_fetch_assoc($menusql);
    $menu_show_permissions = explode(",", $rels['print_permission']);
    $sql = $dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = '" . $print_type_id . "' AND approve_status = 1 AND status = 0 ORDER BY priority ASC LIMIT 1");
    while ($res = mysqli_fetch_assoc($sql))
    {
        if (in_array($res['id'], $menu_show_permissions))
        {
            $str = $res['page_path'];
        }
    }
    return $str;
}

function get_automatic_po_approval($dbcon, $inserpoid)
{
    $info1['approve_remark'] = 'System Approval';
    $info1['approve_status'] = 3;
    $info1['purchaseorder_id'] = $inserpoid;
    $info1['user_id'] = $_SESSION['user_id'];
    $info1['company_id'] = $_SESSION['company_id'];
    $info1['cdate'] = date('Y-m-d H:i:s');

    $inserid = add_record("tbl_purchaseorder_aprv_log", $info1, $dbcon);

    $info['po_approval_status'] = 3;

    $updateid = update_record("tbl_purchaseorder", $info, "purchaseorder_id=" . $inserpoid, $dbcon);
}
function get_automatic_po_finance_approval($dbcon, $inserpoid)
{
    $info1['approve_remark'] = 'System Approval';
    $info1['approve_status'] = 1;
    $info1['purchaseorder_id'] = $inserpoid;
    $info1['user_id'] = $_SESSION['user_id'];
    $info1['company_id'] = $_SESSION['company_id'];
    $info1['cdate'] = date('Y-m-d H:i:s');

    $inserid = add_record("tbl_purchaseorder_finance_aprv_log", $info1, $dbcon);

    $info['po_approval_status'] = 1;
    $info['po_aproove_finance'] = 1;

    $updateid = update_record("tbl_purchaseorder", $info, "purchaseorder_id=" . $inserpoid, $dbcon);
}

function get_automatic_po_shortclose_approval($dbcon, $purchaseorder_id, $purchaseorder_trn_id)
{
    $companyConfiguration = getCompanyConfiguration($dbcon);
    $comp_spec_setting = getspecialConfiguration($dbcon);
    $branch_id = $_SESSION['branch_id'];
    $info1['approve_remark'] = 'System Approval';
    $info1['approve_status'] = 1;
    $info1['purchaseorder_id'] = $purchaseorder_id;
    $info1['purchaseorder_trn_id'] = $purchaseorder_trn_id;
    $info1['user_id'] = $_SESSION['user_id'];
    $info1['company_id'] = $_SESSION['company_id'];
    $info1['cdate'] = date('Y-m-d H:i:s');

    $inserid = add_record("tbl_po_shortclose_aprv_log", $info1, $dbcon);

    // Update For Po Trn table
    if ($info1['approve_status'] == 1)
    {

        $info_pur['used_status'] = 1;
        $info_pur['shortclose_status'] = 0;

        $req_q = "select * from tbl_request_product where rp_req_type='short_close' and purchaseordertrn_id=" . $purchaseorder_trn_id;

        $req_e = $dbcon->query($req_q);
        $cnt = brp_mysqli_num_rows($req_e);
        if ($cnt > 0)
        {
        }
        else
        {
            if($comp_spec_setting['power_drive'] == '0'){

            $query = "select po_ref_id from tbl_purchaseordertrn where purchaseordertrn_id=" . $purchaseorder_trn_id;
            $que_e = $dbcon->query($query);
            if ($cnt = brp_mysqli_num_rows($que_e) > 0)
            {
                $que = "select reqs.*,ptrn.branch_id as bnch_id,(select sum(used_qty) from tbl_purchaseorder_req_trn as sr where sr.purchaseordertrn_req_status=0 and sr.rp_id=rtrn.rp_id) as re_conv_qty,(select sum(product_conv_qty) from tbl_grn_sub_trn as inv where inv.status=0 and inv.rp_id = rtrn.rp_id)  as inw_conv_qty from tbl_purchaseordertrn as ptrn 
                left join tbl_purchaseorder_req_trn as rtrn on rtrn.purchaseordertrn_id = ptrn.purchaseordertrn_id
                left join tbl_request_product as reqs on reqs.rp_id = rtrn.rp_id
                where rtrn.purchaseordertrn_req_status=0 and ptrn.purchaseordertrn_id =" . $purchaseorder_trn_id;

                $que_ex = $dbcon->query($que);

                while ($row = brp_mysqli_fetch_array($que_ex))
                {
                    $pending_qty = $row['re_conv_qty'] - $row['inw_conv_qty'];
                    $branch_id = $row['branch_id'];
                    if ($pending_qty > 0)
                    {
                        $indenttrn['indent_no'] = load_common_no($dbcon, 17);

                        $query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=17 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id']);

                        $indenttrn['indent_date'] = date('Y-m-d');
                        $indenttrn['rp_req_date'] = date('Y-m-d');
                        $indenttrn['rp_req_qty'] = $pending_qty;
                        $indenttrn['purchase_unit'] = $row['purchase_unit'];
                        $indenttrn['rp_pid'] = $row['rp_pid'];
                        $indenttrn['branch_id'] = $row['branch_id'];
                        $indenttrn['indent_status'] = 1;
                        $indenttrn['rp_req_type'] = "short_close";
                        $indenttrn['rp_po_req_no'] = $row['rp_po_req_no'];
                        $indenttrn['rp_process_req_no'] = $row['rp_process_req_no'];
                        $indenttrn['sr_no'] = $row['sr_no'];
                        $indenttrn['sp_id'] = $row['sp_id'];
                        $indenttrn['rp_req_no'] = $row['rp_req_no'];
                        $indenttrn['req_qty_one'] = $row['req_qty_one'];
                        $indenttrn['rp_po_qty'] = $pending_qty;
                        $indenttrn['in_process_qty'] = $row['in_process_qty'];
                        $indenttrn['out_process_qty'] = $row['out_process_qty'];
                        $indenttrn['row_cnt'] = $row['row_cnt'];
                        $indenttrn['process_unit'] = $row['process_unit'];

                        $indenttrn['perent_id'] = $row['parent_id'];
                        $indenttrn['reserve_stock'] = $row['reserve_stock'];
                        $indenttrn['main_request'] = $row['main_request'];
                        $indenttrn['pre_trn_id'] = $row['pre_trn_id'];
                        $indenttrn['purchaseordertrn_id'] = $purchaseorder_trn_id;

                        $indenttrn['job_card_no'] = $row['job_card_no'];
                        $indenttrn['job_card_date'] = $row['job_card_date'];
                        $indenttrn['job_card_status'] = $row['job_card_status'];
                        $indenttrn['sales_order_trn_id'] = $row['sales_order_trn_id'];
                        $indenttrn['product_version'] = $row['product_version'];
                        $indenttrn['work_order_no'] = $row['work_order_no'];
                        $indenttrn['work_order_date'] = $row['work_order_date'];
                        $indenttrn['work_order_status'] = $row['work_order_status'];
                        $indenttrn['bom_id'] = $row['bom_id'];
                        $indenttrn['approval_status'] = $row['approval_status'];
                        $indenttrn['jobwork_type'] = $row['jobwork_type'];
                        $indenttrn['customer_id'] = $row['customer_id'];

                        $indenttrn['cdate'] = date("Y-m-d H:i:s");
                        $indenttrn['user_id'] = $_SESSION['user_id'];
                        $indenttrn['company_id'] = $_SESSION['company_id'];

                        $indenttid = add_record('tbl_request_product', $indenttrn, $dbcon, $branch_id);

                        if ($companyConfiguration['automatic_approval_indent'] == 1)
                        {
                            $approve_no = load_common_no($dbcon, 18);
                            update_common_no($dbcon, 18);

                            $info['approve_no'] = $approve_no;
                            $info['approve_date'] = date("Y-m-d");
                            $info['rp_id'] = $indenttid;
                            $info['approve_qty'] = $pending_qty;
                            $info['approve_unit'] = $row['purchase_unit'];
                            $info['delivery_date'] = date("Y-m-d H:i:s");
                            $info['quotation_requirement'] = 0;
                            $info['cdate'] = date("Y-m-d H:i:s");
                            $info['user_id'] = $_SESSION['user_id'];
                            $info['company_id'] = $_SESSION['company_id'];

                            $inserpoid = add_record('approve_indent', $info, $dbcon, $branch_id);

                            if ($pending_qty == $pending_qty)
                            {

                                $inftrn['indent_status'] = 3;
                                $updatetrnid = update_record('tbl_request_product', $inftrn, "rp_id=" . $indenttid, $dbcon, $branch_id);
                            }

                            $query_used = "select * from tbl_request_product as rpro
                            where rp_id=" . $indenttid . " and company_id = '" . $_SESSION['company_id'] . "' ";
                            $rel_used = brp_mysqli_fetch_assoc($dbcon->query($query_used));

                            $rate = get_pro_field($dbcon, $rel_used['rp_pid'], 'product_purchase_rate');

                            $total = $pending_qty * $rate;

                            $infpotrn['purchaseorder_id'] = '0';
                            $infpotrn['product_type'] = '';
                            $infpotrn['product_id'] = $rel_used['rp_pid'];
                            $infpotrn['product_qty'] = $pending_qty;
                            $infpotrn['product_rate'] = $rate;
                            $infpotrn['product_hsn_code'] = get_pro_field($dbcon, $rel_used['rp_pid'], 'product_hsn');
                            //$infpotrn['unit_id']          = get_pro_field($dbcon,$pr_id,'product_base_unit');
                            $infpotrn['unit_id'] = $row['purchase_unit'];
                            $infpotrn['product_amount'] = $total;
                            $infpotrn['total'] = $total;
                            $infpotrn['parent_pro'] = 0;
                            $infpotrn['main_pro_status'] = 1; //Requested products
                            $infpotrn['user_id'] = $_SESSION['user_id'];
                            $infpotrn['po_ref_id'] = $indenttid;
                            $infpotrn['po_ref_type'] = '0';
                            $infpotrn['po_bom_id'] = '';
                            $infpotrn['po_bom_trn_id'] = '';
                            $infpotrn['mdate'] = date('Y-m-d');
                            $infpotrn['company_id'] = $_SESSION['company_id'];

                            if ($info['quotation_requirement'] == 0)
                            {
                                $inserpotrnid = add_record('tbl_purchasetrntemp', $infpotrn, $dbcon, $branch_id);
                            }
                        }
                    }
                }
            }
        }
        }
    }

    $updateid = update_record("tbl_purchaseordertrn", $info_pur, "purchaseordertrn_id=" . $purchaseorder_trn_id, $dbcon);

    // Aproove For Short Close Log Table
    $infoshort['aproove_status'] = 1;

    $updateid = update_record("tbl_log_po_short_close", $infoshort, "po_trn_id=" . $purchaseorder_trn_id, $dbcon);
}
function get_work_order($dbcon, $edit_id = '')
{
    $str = "";
    $q = "select * from tbl_set_main_process where company_id=" . $_SESSION['company_id'];
    $sel = $dbcon->query($q);
    $str .= "<option value=''>--Select Work Order No--</option>";
    while ($row = brp_mysqli_fetch_assoc($sel))
    {
        if ($edit_id == $row['sp_id'])
        {
            $select = 'selected';
        }
        else
        {
            $select = '';
        }

        $str .= "<option value='" . $row['sp_id'] . "' " . $select . ">" . $row['po_req_no'] . "</option>";
    }
    echo $str;
}
function get_product_rate_sales_time($dbcon, $product_id, $unit_id,$cust_id=0)
{
    $query = "select * from tbl_ledger where l_id='".$cust_id."'";
    $result = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($result);

    $qry = $dbcon->query("SELECT trn.* FROM tbl_salescardtrn as trn LEFT JOIN tbl_product_party_sales as sales ON sales.party_sales_id = trn.party_sales_id WHERE trn.salescardtrn_status= 0 AND sales.is_aproove = 1 AND sales.is_active = 0 AND trn.product_id = " . $product_id . " AND trn.affected_date<='" . date("Y-m-d") . "' AND trn.valid_date>='" . date("Y-m-d") . "' AND trn.company_id = " . $_SESSION['company_id']);

    $re_po = brp_mysqli_fetch_assoc($qry);

    $pro_de = get_product_detail($dbcon, $product_id);

    $pr_rate = 0;
    $disc = 0;
    $unit = (!empty($unit_id)) ? $unit_id : $re_po['unit_id'];
     $scqry = "SELECT * FROM tbl_salescardelcontrn WHERE salescardelcontrn_id = '" . @$re_po['salescardelcontrn_id'] . "'";
    $chksales = $dbcon->query($scqry);
    $getsales = brp_mysqli_fetch_assoc($chksales);

    if($cust_id){
        $qryc = $dbcon->query("SELECT trn.* FROM tbl_salescardtrn as trn LEFT JOIN tbl_product_party_sales as sales ON sales.party_sales_id = trn.party_sales_id WHERE trn.salescardtrn_status= 0 AND sales.is_aproove = 1 AND sales.is_active = 0 AND trn.product_id = " . $product_id . " and vendor_id=".$cust_id." AND trn.affected_date<='" . date("Y-m-d") . "' AND trn.valid_date>='" . date("Y-m-d") . "' AND trn.company_id = " . $_SESSION['company_id']." order by trn.salescardtrn_id  desc LIMIT 1 ");
        
        $cntc = brp_mysqli_num_rows($qryc);
        $rowc = brp_mysqli_fetch_array($qryc);
        if($cntc>0){
            if (!empty($rowc['discount_percentage']))
            {
                $disc = $rowc['price'] * $rowc['discount_percentage'] / 100;
                $pr_rate = ($rowc['price'] - $disc);
                $disc_per = $rowc['discount_percentage'];
            }
            else
            {
                $pr_rate = $rowc['price'];
                $disc_per = $rowc['discount_percentage'];
            }
        }else{
            $qrycat = $dbcon->query("SELECT trn.* FROM tbl_salescardtrn as trn LEFT JOIN tbl_product_party_sales as sales ON sales.party_sales_id = trn.party_sales_id WHERE trn.salescardtrn_status= 0 AND sales.is_aproove = 1 AND sales.is_active = 0 AND trn.category_id = " . $pro_de['product_category'] . " and vendor_id=".$cust_id." AND trn.affected_date<='" . date("Y-m-d") . "' AND trn.valid_date>='" . date("Y-m-d") . "' AND trn.company_id = " . $_SESSION['company_id']." order by trn.salescardtrn_id desc LIMIT 1 ");

            $rowcat = brp_mysqli_fetch_array($qrycat);

            if (!empty($rowcat['discount_percentage']))
            {
                $disc = $rowcat['price'] * $rowcat['discount_percentage'] / 100;
                $pr_rate = ($rowcat['price'] - $disc);
                $disc_per = $rowcat['discount_percentage'];
            }
            else
            {
                $pr_rate = $rowcat['price'];
                $disc_per = $rowcat['discount_percentage'];
            }
        }
    }else{
        $pr_rate = $pro_de['product_sale_rate'];
    }

    if(empty($disc_per)){
        if($row['l_group']){
            $qrygr = $dbcon->query("SELECT trn.* FROM tbl_salescardtrn as trn LEFT JOIN tbl_product_party_sales as sales ON sales.party_sales_id = trn.party_sales_id WHERE trn.salescardtrn_status= 0 AND sales.is_aproove = 1 AND sales.is_active = 0 AND trn.product_id = " . $product_id . " and trn.group_id=".$row['l_group']." AND trn.affected_date<='" . date("Y-m-d") . "' AND trn.valid_date>='" . date("Y-m-d") . "' AND trn.company_id = " . $_SESSION['company_id']." order by trn.salescardtrn_id desc LIMIT 1 ");
            
            $cntgr = brp_mysqli_num_rows($qrygr);
            $rowgr = brp_mysqli_fetch_array($qrygr);
            if($cntc>0){
                if (!empty($rowgr['discount_percentage']))
                {
                    $disc = $rowgr['price'] * $rowgr['discount_percentage'] / 100;
                    $pr_rate = ($rowgr['price'] - $disc);
                    $disc_per = $rowgr['discount_percentage'];
                }
                else
                {
                    $pr_rate = $rowgr['price'];
                    $disc_per = $rowgr['discount_percentage'];
                }
            }else{
                $qrygr = $dbcon->query("SELECT trn.* FROM tbl_salescardtrn as trn LEFT JOIN tbl_product_party_sales as sales ON sales.party_sales_id = trn.party_sales_id WHERE trn.salescardtrn_status= 0 AND sales.is_aproove = 1 AND sales.is_active = 0 AND trn.category_id = " . $pro_de['product_category'] . " and trn.group_id=".$row['l_group']." AND trn.affected_date<='" . date("Y-m-d") . "' AND trn.valid_date>='" . date("Y-m-d") . "' AND trn.company_id = " . $_SESSION['company_id']." order by trn.salescardtrn_id desc LIMIT 1 ");
                $rowgr = brp_mysqli_fetch_array($qrygr);

                if (!empty($rowgr['discount_percentage']))
                {
                    $disc = $rowgr['price'] * $rowgr['discount_percentage'] / 100;
                    $pr_rate = ($rowgr['price'] - $disc);
                    $disc_per = $rowgr['discount_percentage'];
                }
                else
                {
                    $pr_rate = $rowgr['price'];
                    $disc_per = $rowgr['discount_percentage'];
                }
            }
        }
    } 
    if(empty($disc_per)){
        if ($pro_de['product_base_unit'] == $unit)
        {
            if ($pro_de['product_base_unit'] == $re_po['unit_id'])
            {
                if (!empty($re_po['discount_percentage']))
                {
                    $disc = $re_po['price'] * $re_po['discount_percentage'] / 100;
                    $pr_rate = ($re_po['price'] - $disc);
                    $disc_per = $re_po['discount_percentage'];
                }
                else
                {
                    $pr_rate = $re_po['price'];
                    $disc_per = $re_po['discount_percentage'];
                }
            }
            else if ($pro_de['product_conv_unit'] == $re_po['unit_id'])
            {
                $prc = $re_po['price'] - $getsales['rate1'] - $getsales['rate2'] - $getsales['rate3'];
                $prcs = ($prc * $pro_de['base_weight'] / $pro_de['conv_weight']) + $getsales['rate1'] + $getsales['rate2'] + $getsales['rate3'];
                if (!empty($re_po['discount_percentage']))
                {
                    $disc = $prcs * $re_po['discount_percentage'] / 100;
                    $pr_rate = ($prcs - $disc);
                    $disc_per = $re_po['discount_percentage'];
                }
                else
                {
                    $pr_rate = $prcs;
                    $disc_per = $re_po['discount_percentage'];
                }
            }
            else
            {
                $pr_rate = $pro_de['product_sale_rate'];
            }
        }
        else if ($pro_de['product_conv_unit'] == $unit)
        {
            if ($pro_de['product_conv_unit'] == $re_po['unit_id'])
            {
                if (!empty($re_po['discount_percentage']))
                {
                    $disc = $re_po['price'] * $re_po['discount_percentage'] / 100;
                    $pr_rate = ($re_po['price'] - $disc);
                    $disc_per = $re_po['discount_percentage'];
                }
                else
                {
                    $pr_rate = $re_po['price'];
                    $disc_per = $re_po['discount_percentage'];
                }
            }
            else if ($pro_de['product_base_unit'] == $re_po['unit_id'])
            {
                $prc = $re_po['price'] - $getsales['rate1'] - $getsales['rate2'] - $getsales['rate3'];
                $prcs = ($prc * $pro_de['conv_weight'] / $pro_de['base_weight']) + $getsales['rate1'] + $getsales['rate2'] + $getsales['rate3'];
                if (!empty($re_po['discount_percentage']))
                {
                    $disc = $prcs * $re_po['discount_percentage'] / 100;
                    $pr_rate = ($prcs - $disc);
                    $disc_per = $re_po['discount_percentage'];
                }
                else
                {
                    $pr_rate = $prcs;
                    $disc_per = $re_po['discount_percentage'];
                }
            }
            else
            {
                $pr_rate = $pro_de['product_sale_rate'];
            }
        }
        else
        {
            $pr_rate = $pro_de['product_sale_rate'];
        } 
    }
    $row['discount_per']  = $disc_per;
    $row['pr_rate']  = $pr_rate;
    return $row;
}
function get_all_sales_card($dbcon, $id)
{
    $str = "";
    $q = "select * from tbl_product_party_sales where card_status = 0 AND is_aproove = 1 AND is_active = 0 AND company_id=" . $_SESSION['company_id'];
    $sel = $dbcon->query($q);
    $str .= "<option value=''>--Select Sales Card No--</option>";
    while ($row = brp_mysqli_fetch_assoc($sel))
    {
        if ($id == $row['party_sales_id'])
        {
            $select = 'selected';
        }
        else
        {
            $select = '';
        }

        $str .= "<option value='" . $row['party_sales_id'] . "' " . $select . ">" . $row['sales_card_no'] . "</option>";
    }
    echo $str;
}
function get_elcon_sales_card($dbcon, $id)
{
    $str = "";
    $q = "select * from tbl_product_sales_elcon where card_status = 0 AND is_approve = 1 AND is_active = 0 AND company_id=" . $_SESSION['company_id'];
    $sel = $dbcon->query($q);
    $str .= "<option value=''>--Select Sales Card No--</option>";
    while ($row = brp_mysqli_fetch_assoc($sel))
    {
        if ($id == $row['elcon_sales_id'])
        {
            $select = 'selected';
        }
        else
        {
            $select = '';
        }

        $str .= "<option value='" . $row['elcon_sales_id'] . "' " . $select . ">" . $row['sales_card_no'] . "</option>";
    }
    echo $str;
}
//Maulik Start
function get_indent_no_date($dbcon, $rp_id)
{
    $query = "select * from tbl_request_product where rp_id in (" . $rp_id . ")";
    $sel = $dbcon->query($query);

    $str = '';
    while ($row = brp_mysqli_fetch_assoc($sel))
    {
        $str .= $row['indent_no'] . '  <br>' . date('d-m-Y', strtotime($row['indent_date'])) . '<br>';
    }
    return $str;
}
function get_delivery_date_qty_po($dbcon, $purchaseordertrn_id)
{
    $query = "select dt.*,um.unit_name from tbl_purchaseorder_delivery_date as dt
    left join unit_mst as um on um.unitid = dt.unit_id
    where po_delivery_date_status=0 and purchaseordertrn_id=" . $purchaseordertrn_id;
    $sel = $dbcon->query($query);

    $str = '';
    while ($row = brp_mysqli_fetch_assoc($sel))
    {
        $str .= '<strong>Date : </strong>' . date('d-m-Y', strtotime($row['delivery_date'])) . ' <strong>Qty : </strong>' . $row['product_qty'] . ' ' . $row['unit_name'] . '<br>';
    }
    return $str;
}
function get_indent_data($dbcon, $refid, $field)
{
    $query = "select " . $field . " from tbl_request_product where rp_id in(" . $refid . ")";
    $sel = $dbcon->query($query);

    $str = '';
    while ($row = brp_mysqli_fetch_array($sel))
    {
        if ($field == 'indent_date')
        {
            $str .= date('d-m-Y', strtotime($row[$field])) . "<br>";
        }
        else
        {
            $str .= $row[$field] . "<br>";
        }
    }
    return $str;
}
function workorder_no($dbcon)
{
    $query = "select po.sp_id,spro.po_req_no FROM tbl_request_product as po 
    left join tbl_set_main_process as spro on spro.sp_id=po.sp_id
    where po.jobwork_type = 0 AND po.status !=2 AND po.sp_id !='' and po.indent_status not in (0,2)  and po.company_id in (" . $_SESSION['company_id'] . ") Group by po.sp_id ORDER BY po.rp_id desc";
    $sel = $dbcon->query($query);
    $str = '';
    $str .= "<option value=''>Choose Work Order No</option>";
    while ($row = brp_mysqli_fetch_array($sel))
    {
        $str .= '<option value="' . $row['sp_id'] . '">' . $row['po_req_no'] . '</option>';
    }
    return $str;
}
function salesorder_no($dbcon)
{
    $query = "select req.sp_id,so.sales_order_no from tbl_request_product as req
    left join tbl_sales_ordertrn as strn on strn.sales_ordertrn_id = req.sales_order_trn_id
    left join tbl_sales_order as so on so.sales_order_id=strn.sales_order_id
    where req.main_request=1 and so.sales_order_no!='' and req.company_id=" . $_SESSION['company_id'] . " group by req.sp_id";

    $sel = $dbcon->query($query);

    $str = '';
    $str .= '<option value="">Choose Sales Order No</option>';
    while ($row = brp_mysqli_fetch_array($sel))
    {
        $str .= '<option value="' . $row['sp_id'] . '">' . $row['sales_order_no'] . '</option>';
    }
    return $str;
}

function po_detail_so($dbcon){
    $query = "select req.sales_order_id,so.sales_order_no from tbl_request_product as req
    left join tbl_sales_order as so on so.sales_order_id=req.sales_order_id
    where req.sales_order_id!=0 and req.company_id=" . $_SESSION['company_id'] . " group by req.sales_order_id";

    $sel = $dbcon->query($query);

    $str = '';
    $str .= '<option value="">Choose Sales Order No</option>';
    while ($row = brp_mysqli_fetch_array($sel))
    {
        $str .= '<option value="' . $row['sales_order_id'] . '">' . $row['sales_order_no'] . '</option>';
    }
    return $str;
}

function getFinacialyear_data($dbcon, $id = false)
{
    $query = "SELECT * FROM `tbl_financial_year` WHERE current_status = 1 AND company_id = '" . $_SESSION['company_id'] . "'";
    $query .= " ORDER BY financial_year_id DESC LIMIT 1";

    $q = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($q);
    return $row;
}

function po_to_indent_no($dbcon, $rp_id)
{
    $query = "select GROUP_CONCAT(indent_no) as indent from tbl_request_product where rp_id in (" . $rp_id . ")";
    $q = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($q);
    return $row['indent'];
}

function po_to_so_no($dbcon, $rp_id)
{
    $query = "select DISTINCT(group_concat(sp_id)) as sp_id from tbl_request_product where
    rp_id in (" . $rp_id . ")";
    $q = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($q);

    $query1 = "select sales_order_no from tbl_request_product as req
    left join tbl_sales_ordertrn as strn on strn.sales_ordertrn_id = req.sales_order_trn_id
    left join tbl_sales_order as so on so.sales_order_id=strn.sales_order_id
    where req.sp_id in (" . $row['sp_id'] . ") and main_request=1 and so.sales_order_no !='' group by so.sales_order_id";

    $q1 = $dbcon->query($query1);
    $str = '';
    while ($row1 = brp_mysqli_fetch_array($q1))
    {
        $str .= $row1['sales_order_no'] . ", ";
    }
    return $str;
}

function get_find_stock_detail($dbcon, $potrancation_id, $rate_unit, $unit_id, $rate)
{
    $query = "select ptr.grn_trn_id,prd.product_base_qty,prd.product_conv_qty from tbl_potrancation as ptr 
    left join product_mst as prd on prd.product_id = ptr.product_id
    where ptr.potrancation_id=" . $potrancation_id;
    $q = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($q);

    if ($rate_unit == $unit_id)
    {
        $brate = $rate;
        $crate = ($row['product_base_qty'] / $row['product_conv_qty']) * $brate;
    }
    else
    {
        $crate = $rate;
        $brate = ($row['product_conv_qty'] / $row['product_base_qty']) * $crate;
    }
    $stock_rate['base_rate'] = $brate;
    $stock_rate['conv_rate'] = $crate;

    $query1 = "select * from tbl_stock_trn where ref_name='tbl_grn_trn' and ref_id=" . $row['grn_trn_id'];
    $q1 = $dbcon->query($query1);
    while ($row1 = brp_mysqli_fetch_array($q1))
    {
        $update_rate = update_record('tbl_stock_trn', $stock_rate, 'stock_id' . "=" . $row1['stock_id'], $dbcon);
    }
}
//Maulik End
function get_sub_type($dbcon, $type, $id)
{
    $str = "";
    $q = "select eway_sub_type_name,eway_sub_type_id,code from eway_sub_type where status = 0 AND supply_type=" . $type;
    $sel = $dbcon->query($q);
    $str .= "<option value=''>--Select Sub Type--</option>";

    while ($row = brp_mysqli_fetch_assoc($sel))
    {
        $select = '';
        if ($id == $row['code'])
        {
            $select = 'selected';
        }
        $str .= "<option value='" . $row['code'] . "' " . $select . ">" . $row['eway_sub_type_name'] . "</option>";
    }
    echo $str;
}
function get_eway_transport_mode($dbcon, $id)
{
    $str = "";
    $q = "select eway_bill_transport_type,eway_transport_mode_id from eway_transport_mode where status = 0 ";
    $sel = $dbcon->query($q);
    $str .= "<option value=''>--Select Transport Mode--</option>";

    while ($row = brp_mysqli_fetch_assoc($sel))
    {
        $select = '';
        if ($id == $row['eway_transport_mode_id'])
        {
            $select = 'selected';
        }
        $str .= "<option value='" . $row['eway_transport_mode_id'] . "' " . $select . ">" . $row['eway_bill_transport_type'] . "</option>";
    }
    echo $str;
}

function work_order_production_track($dbcon, $p_id, $rp_id, $product_id, $process_id,$priority)
{
    // var_dump($p_id);
    $batch_qty = 0;
    $store_req_pen = 0;
    $company_config = getCompanyConfiguration($dbcon);


    
    $pr_q = "select product_base_unit,product_conv_unit from product_mst where product_id = " . $product_id;
    $pr_rw = brp_mysqli_fetch_assoc($dbcon->query($pr_q));

    $base_unit_name = getunitname($dbcon,$pr_rw['product_base_unit']);
    $conv_unit_name = getunitname($dbcon,$pr_rw['product_conv_unit']);
    
    if ($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '0')
    {
        $s_ql = "select GROUP_CONCAT(ap.p_id) as allocate_id,sum(ap.p_qty) as total_qty,sum(ap.pen_qty) as total_pending,sum(ap.start_qty) as total_start_qty,branch.branch_name,ap.p_product_id,p.product_name,tc.cat_name,p_status,ap.cdate from tbl_allocate_process as ap
        left join product_mst as p on p.product_id=ap.p_product_id
        left join tbl_category as tc on p.product_category=tc.cat_id
        left join branch_mst as branch on branch.branch_id=ap.branch_id
        where ap.batch_process_start_time = 1 and  ap.batch_no ='' and  ap.process_id=" . $process_id . "  and ap.company_id=" . $_SESSION['company_id'] . " and ap.p_status IN(0,1) and ap.p_product_id = " . $product_id . " and p_ref_id = " . $rp_id . " and ap.process_priority=".$priority." and pr_process_type= 1 and ap.p_id in(".$p_id.") group by ap.p_product_id,ap.branch_id,ap.product_version";
        $q = $dbcon->query($s_ql);

        $rel = brp_mysqli_fetch_array($q);
        $batch_qty = store_production_start_count_using_p_id($dbcon, $rel['allocate_id']);
        $batch_conv_qty = convert_stock($dbcon,$batch_qty, $rel['p_product_id'], 'conv_unit');
    }
    $is_store_approval = $company_config['store_approval'];
    if ($is_store_approval)
    {
        $where = "";
        if ($company_config['batch_wise_stock'] == 1 && $company_config['batch_process'] == 0)
        {
            $where = " and ((ap.batch_no ='' and ap.batch_process_start_time = 0) OR (ap.batch_process_start_time = 1 AND ap.batch_no != '')) ";
        }
        $s_ql = "select GROUP_CONCAT(p_id) as allocate_id,sum(p_qty) as total_qty,sum(pen_qty) as total_pending,sum(start_qty) as total_start_qty,branch.branch_name,p.product_name,tc.cat_name,p_status,ap.cdate from tbl_allocate_process as ap
        left join product_mst as p on p.product_id=ap.p_product_id
        left join tbl_category as tc on p.product_category=tc.cat_id
        left join branch_mst as branch on branch.branch_id=ap.branch_id
        where ap.extra_stock = 0 and ap.process_id=" . $process_id . " and p_product_id =" . $product_id . " and ap.process_id = " . $process_id . " and p_ref_id = " . $rp_id . " and ap.company_id=" . $_SESSION['company_id'] . $where . " and ap.p_status IN(0,1) and pr_process_type  = 1 and ap.p_id in(".$p_id.")  group by ap.p_product_id,ap.branch_id,ap.product_version";
        $q = $dbcon->query($s_ql);
        $rel_2 = brp_mysqli_fetch_assoc($q);

        // $store_req_pen_dt = $rel_2['cdate'];
        $store_p_id = $rel_2['allocate_id'];
        $store_req_pen = store_production_start_count_using_p_id($dbcon, $rel_2['allocate_id']);

        if($company_config['round_up_qty'] == '1'){
                $store_req_pen =  round($store_req_pen);
        }
        
        // echo "</br></br>";
        $s_ql = "select sum(base_qty) as total_qty,sum(release_qty) as total_release_qty, GROUP_CONCAT(tsr.p_id) AS pids,ap.batch_no,ap.cdate from tbl_store_request as tsr
        left join tbl_allocate_process as ap on tsr.p_id=ap.p_id
        where tsr.store_request_status IN (0,1) and ap.p_id in( " . $p_id . ") and ap.p_product_id = " . $product_id . " and ap.p_status in (0,1) and ap.process_id = " . $process_id . " and tsr.company_id=" . $_SESSION['company_id'] . "  group by tsr.product_id, tsr.branch_id, tsr.process_id";
        // echo "</br></br>";
        $q = $dbcon->query($s_ql);
        $rel_2 = brp_mysqli_fetch_assoc($q);
        $store_rel_pen = $rel_2['total_qty'];
        $store_rel_qty = $rel_2['total_release_qty'];

        // $store_req_pen = $store_req_pen - $store_rel_qty;
        $store_rel_pen = $store_rel_pen - $store_rel_qty;

        $store_dt = $rel_2['cdate'];

    }
    /*var_dump("==>".$store_rel_pen);
    var_dump("==>".$store_rel_qty);*/
    // $query1 = "select batch.* from tbl_batch_data as batch
    // left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id and trn.grn_trn_status = 0
    // left join tbl_grn_sub_trn as strn on trn.grn_trn_id = strn.grn_trn_id and strn.status = 0
    // where  batch.qc_status = 0 and strn.rp_id=" . $rp_id . " and batch.process_id = " . $process_id;
    $qry_g = "select GROUP_CONCAT(grn_trn_id) as grn_trn_id,grn.rp_id from tbl_grn_sub_trn as grn
                left join tbl_job_work_sub_trn as job on job.job_work_sub_trn_id=grn.job_work_sub_trn_id
                left join tbl_allocate_process as ap on ap.p_id=job.p_id
                 where grn.rp_id =" . $rp_id . " and job.p_id in(".$p_id.") and ap.process_priority=".$priority." group by grn.rp_id";
    $result_g = $dbcon->query($qry_g);
    $res_g = brp_mysqli_fetch_assoc($result_g);
    $g_whr = "";
    $grn_trn_id = "";
    if (brp_mysqli_num_rows($result_g) > 0)
    {
        $g_whr = " and grn_trn_id in(" . $res_g['grn_trn_id'] . ")";
        $grn_trn_id = $res_g['grn_trn_id'];
    }

    $qc_pending_qty = 0;
     if($g_whr){

        
        $query1 = "select IFNULL(sum(batch_qty),0) as batch_qty,batch.cdate from tbl_batch_data as batch
        where batch.qc_status = 0 and reprocess_qc = 0 and batch.process_id = " . $process_id . $g_whr;
        // echo "</br></br>";
        $result1 = $dbcon->query($query1);
       $qc_dt = "";

        while ($rel1 = brp_mysqli_fetch_assoc($result1))
        {
            $qc_pending_qty = $qc_pending_qty + $rel1['batch_qty'];
            $qc_dt = $rel1['cdate'];
        }
      }
    $str = "";
    $process_name = get_process_name($dbcon, $process_id);
    $qc_paramter_info = check_product_qc_paramter($dbcon, $product_id, $process_id);

    if ($batch_qty > 0)
    {
        $str .= '<tr style="color:red">
        <td> ' . $process_name . ' Batch Pending </td>
         <td>' . $batch_qty .  ' ' . $base_unit_name . '</br>' . $batch_conv_qty . ' '. $conv_unit_name . '</td>
        <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'batch_pending\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\')"><i class="fa fa-eye"></i></button> </td>  
        </tr>';
    }

    $qry = "SELECT GROUP_CONCAT(ap.p_id) as allocate_id,sum(p_qty) as totalqty,ap.cdate FROM tbl_allocate_process as ap where  ap.pr_process_type='2' and ap.p_status in(0,1) and ap.company_id=1 and  process_id = " . $process_id . " and ap.p_product_id = " . $product_id . " and ap.p_id in(".$p_id.")  Group by ap.p_product_id, ap.process_id,ap.branch_id";
    $result1 = $dbcon->query($qry);
    $rel1 = brp_mysqli_fetch_assoc($result1);
    $allocate_id = $rel1['allocate_id'];

    $min_working_qty = production_start_count_using_p_id($dbcon, $allocate_id);

    $q = "SELECT IFNULL(sum(trn.product_base_qty),0) as used_qty,trn.cdate FROM `tbl_job_work_sub_trn` as trn  
    left join tbl_job_work_trn as job_work_trn on job_work_trn.job_work_trn_id =  trn.job_work_trn_id
    where job_work_sub_trn_status = 0 and p_id IN (" . $allocate_id . ")  and job_work_trn.job_work_trn_status in (0,1)";
        $job_trn = $dbcon->query($q);
        $job_trn_result = brp_mysqli_fetch_assoc($job_trn);
        $jobwork_working_qty = $job_trn_result['used_qty'];
    // echo "</br></br>";
        if ($min_working_qty > 0)
        {
            $jobwork_pen = $rel1['totalqty'] - $jobwork_working_qty;
        }

        if ($jobwork_pen > 0)
        {
            $jobwork_pen_conv_qty = convert_stock($dbcon,$jobwork_pen,$product_id,'conv_unit');
            $str .= '<tr style="color:red">
            <td> ' . $process_name . ' Jobwork Pending </td>
            <td>' . $jobwork_pen .  ' ' . $base_unit_name . '</br>' . $jobwork_pen_conv_qty . ' '. $conv_unit_name . '</td>
            <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'jobwork_pending\','.$product_id.','.$process_id.','.$priority.',\''.$allocate_id.'\')"><i class="fa fa-eye"></i></button> </td>  
            </tr>';
        }

        $q1 = "SELECT job.job_work_id,sum(strn.product_base_qty) as product_base_qty,job.job_work_date FROM tbl_job_work as job
        left join tbl_job_work_trn as trn on trn.job_work_id = job.job_work_id
        left join tbl_job_work_sub_trn as strn on strn.job_work_trn_id = trn.job_work_trn_id
        where ( 1 AND job.job_work_type = 2 and job.grn_complete_status = 0 and job.job_work_status = 0 and job.request_status = 0 and job.company_id= " . $_SESSION['company_id'] . " and strn.p_id in (" . $p_id . ") and rp_id = " . $rp_id . ") group by rp_id";

            $jobwork_req_pen = $dbcon->query($q1);
            $jobwork_req_result = brp_mysqli_fetch_assoc($jobwork_req_pen);
            $jobwork_req_pen_qty = $jobwork_req_result['product_base_qty'];
    // echo "</br></br>";
            if ($jobwork_req_pen_qty > 0)
            {
                $jobwork_req_pen_conv_qty = convert_stock($dbcon,$jobwork_req_pen_qty,$product_id,'conv_unit');

                $str .= '<tr style="color:red">
                <td> ' . $process_name . ' Jobwork Request Pending </td>
                <td>' . $jobwork_req_pen_qty .  ' ' . $base_unit_name . '</br>' . $jobwork_req_pen_conv_qty . ' '. $conv_unit_name . '</td>
             <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'jobwork_request_pending\','.$product_id.','.$process_id.','.$priority.',\''.$allocate_id.'\')"><i class="fa fa-eye"></i></button> </td>  
                </tr>';
            }

            if ($store_req_pen > 0)
            {
                $store_req_pen_conv_qty = convert_stock($dbcon,$store_req_pen,$product_id,'conv_unit');
                $str .= '<tr style="color:red">
                <td> ' . $process_name . ' Store Request Pending </td>
                 <td>' . $store_req_pen .  ' ' . $base_unit_name . '</br>' . $store_req_pen_conv_qty . ' '. $conv_unit_name . '</td>
                
                <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'store_request_pending\','.$product_id.','.$process_id.','.$priority.',\''.$store_p_id.'\')"><i class="fa fa-eye"></i></button> </td>  
                </tr>';

            }

            if ($store_rel_pen > 0)
            {
                $store_rel_pen_conv_qty = convert_stock($dbcon,$store_rel_pen,$product_id,'conv_unit');
                $str .= '<tr style="color:red">
                <td> ' . $process_name . ' Store Release Pending </td>
                
                <td>' . $store_rel_pen .  ' ' . $base_unit_name . '</br>' . $store_rel_pen_conv_qty . ' '. $conv_unit_name . '</td>
                <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'store_release_pending\','.$product_id.','.$process_id.','.$priority.',\''.$store_p_id.'\')"><i class="fa fa-eye"></i></button> </td>  
                </tr>';
            }

            if ($store_rel_qty > 0)
            {
                $store_rel_qty_conv_qty  = convert_stock($dbcon,$store_rel_qty,$product_id,'conv_unit');
                $str .= '<tr style="color:green">
                <td> ' . $process_name . ' Store Released </td>
                 <td>' . $store_rel_qty .  ' ' . $base_unit_name . '</br>' . $store_rel_qty_conv_qty . ' '. $conv_unit_name . '</td>
               <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'store_release\','.$product_id.','.$process_id.','.$priority.',\''.$store_p_id.'\')"><i class="fa fa-eye"></i></button> </td>  
                </tr>';
            }

            $q2 = "SELECT job.job_work_id,strn.product_base_qty,job.job_work_date FROM tbl_job_work as job
            left join tbl_job_work_trn as trn on trn.job_work_id = job.job_work_id
            left join tbl_job_work_sub_trn as strn on strn.job_work_trn_id = trn.job_work_trn_id
            where ( 1 AND job.job_work_type = 2 and job.grn_complete_status = 0 and job.job_work_status = 0 and strn.request_status = 1 and strn.release_status = 1 and job.chalan_status = 0 and job.company_id= " . $_SESSION['company_id'] . " and p_id in(" . $p_id . ") and rp_id = " . $rp_id . ")";

                $jobwork_chalan_pen = $dbcon->query($q2);
                $jobwork_chalan_result = brp_mysqli_fetch_assoc($jobwork_chalan_pen);
                $jobwork_chalan_pen_qty = $jobwork_chalan_result['product_base_qty'];

                if ($jobwork_chalan_pen_qty > 0)
                {
                    $jobwork_chalan_pen_conv_qty  = convert_stock($dbcon,$jobwork_chalan_pen_qty,$product_id,'conv_unit');

                    $str .= '<tr style="color:red">
                    <td> ' . $process_name . ' Jobwork Chalan Pending </td>
                     <td>' . $jobwork_chalan_pen_qty .  ' ' . $base_unit_name . '</br>' . $jobwork_chalan_pen_conv_qty . ' '. $conv_unit_name . '</td>
                   <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'jobwork_chalan_pending\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\')"><i class="fa fa-eye"></i></button> </td>  
                    </tr>';
                }

                $s_ql = "select sum(s_trn.product_base_qty) as total_qty,job.job_work_date  from tbl_job_work as job
                left join tbl_job_work_trn as job_trn on job_trn.job_work_id=job.job_work_id
                left join tbl_job_work_sub_trn as s_trn on job_trn.job_work_trn_id=s_trn.job_work_trn_id
                where job.grn_complete_status=0 and job_trn.grn_complete_status=0 and job.job_work_type=2
                and job.job_work_status!=2 and job_trn.job_work_trn_status in(0,1) and job.company_id=1 and job.release_status = 1 and job.chalan_status = 1 and
                p_id in (" . $p_id . ") and rp_id = " . $rp_id . "  group by job_trn.product_base_unit,job_trn.process_id,job_trn.product_id";
    // echo "</br></br>";
                $jobwork_grn_pen = $dbcon->query($s_ql);
                $jobwork_grn_result = brp_mysqli_fetch_assoc($jobwork_grn_pen);
                $jobwork_grn_pen_qty = $jobwork_grn_result['total_qty'];


             

                $s_ql = "select sum(grn.product_qty) as grn_qty,job.job_work_date  from tbl_job_work as job
                left join tbl_job_work_trn as job_trn on job_trn.job_work_id=job.job_work_id
                left join tbl_job_work_sub_trn as s_trn on job_trn.job_work_trn_id=s_trn.job_work_trn_id
                left join tbl_grn_sub_trn as grn on grn.job_work_sub_trn_id=s_trn.job_work_sub_trn_id
                where job.grn_complete_status in(1,0) and job_trn.grn_complete_status in(1,0) and job.job_work_type=2
                and job.job_work_status=0 and job_trn.job_work_trn_status in(0,1) and job.company_id=1 and
                s_trn.p_id in (" . $p_id . ") and grn.rp_id = " . $rp_id . "  group by job_trn.product_base_unit,job_trn.process_id,job_trn.product_id";
    // echo "</br></br>";
                $jobwork_grn = $dbcon->query($s_ql);
                $jobwork_grn_res = brp_mysqli_fetch_assoc($jobwork_grn);
                $jobwork_grn_qty = $jobwork_grn_res['grn_qty'];

                $jobwork_grn_pen_qty = $jobwork_grn_pen_qty -  $jobwork_grn_qty;

                if ($jobwork_grn_pen_qty > 0)
                {
                    $jobwork_grn_pen_conv_qty  = convert_stock($dbcon,$jobwork_grn_pen_qty,$product_id,'conv_unit');
                    $str .= '<tr style="color:red">
                    <td> ' . $process_name . ' Jobwork GRN Pending </td>
                      <td>' . $jobwork_grn_pen_qty .  ' ' . $base_unit_name . '</br>' . $jobwork_grn_pen_conv_qty . ' '. $conv_unit_name . '</td>
                   <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'jobwork_grn_pending\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\')"><i class="fa fa-eye"></i></button> </td>  
                    </tr>';
                }
                if ($jobwork_grn_qty > 0)
                {

                     $jobwork_grn_conv_qty  = convert_stock($dbcon,$jobwork_grn_qty,$product_id,'conv_unit');
                    $str .= '<tr style="color:green">
                    <td> ' . $process_name . ' Jobwork GRN Inwarded </td>
                      <td>' . $jobwork_grn_qty .  ' ' . $base_unit_name . '</br>' . $jobwork_grn_conv_qty . ' '. $conv_unit_name . '</td>
                  <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'jobwork_grn\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\')"><i class="fa fa-eye"></i></button> </td>  
                    </tr>';
                }

    //  PRODUCTION START - STOP
                $where = "";
                if ($company_config['batch_wise_stock'] == 1 && $company_config['batch_process'] == 0)
                {
                    $where = " and ((ap.batch_no ='' and ap.batch_process_start_time = 0) OR (ap.batch_process_start_time = 1 AND ap.batch_no != '')) ";
                }
                $s_ql = "select GROUP_CONCAT(p_id) as allocate_id,ap.cdate from tbl_allocate_process as ap
                where ap.pr_process_type = 1 and ap.process_id=" . $process_id . " and p_product_id = " . $product_id . " and  p_ref_id = " . $rp_id . " and ap.company_id=" . $_SESSION['company_id'] . $where . " and ap.p_status IN(0,1) and p_id in(".$p_id.")  group by ap.p_product_id,ap.branch_id,ap.product_version";

                $result = $dbcon->query($s_ql);
                $pending_start_res = brp_mysqli_fetch_assoc($result);
                $allo_p_id = $pending_start_res['allocate_id'];
                $pending_start_qty = production_store_wise_start_count_using_p_id($dbcon, $allo_p_id, $is_store_approval);
    // echo "</br></br>";
                $pending_end_qty = production_end_count_using_p_id($dbcon, $allo_p_id);

                if ($pending_start_qty > 0)
                {
                     $pending_start_conv_qty  = convert_stock($dbcon,$pending_start_qty,$product_id,'conv_unit');
                    $str .= '<tr style="color:red">
                    <td> ' . $process_name . ' Production Start Pending </td>
                     <td>' . $pending_start_qty .  ' ' . $base_unit_name . '</br>' . $pending_start_conv_qty . ' '. $conv_unit_name . '</td>
                   <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'pending_start\','.$product_id.','.$process_id.','.$priority.',\''.$allo_p_id.'\')"><i class="fa fa-eye"></i></button> </td>  
                    </tr>';
                }

                if ($pending_end_qty > 0)
                {
                      $pending_end_conv_qty  = convert_stock($dbcon,$pending_end_qty,$product_id,'conv_unit');

                    $str .= '<tr style="color:red">
                    <td> ' . $process_name . ' Production End Pending</td>
                    <td>' . $pending_end_qty .  ' ' . $base_unit_name . '</br>' . $pending_end_conv_qty . ' '. $conv_unit_name . '</td>
                   <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'pending_end\','.$product_id.','.$process_id.','.$priority.',\''.$allo_p_id.'\')"><i class="fa fa-eye"></i></button> </td>  
                    </tr>';
                }
    // END PRODUCTION START - STOP


                if ($qc_paramter_info == '1')
                {
                    if ($qc_pending_qty > 0)
                    {
                        $qc_pending_conv_qty =  convert_stock($dbcon,$qc_pending_qty,$product_id,'conv_unit');
                        $str .= '<tr style="color:red">
                        <td> ' . $process_name . ' QC Pending </td>
                        <td>' . $qc_pending_qty . '</td>
                        <td>' . $qc_pending_qty .  ' ' . $base_unit_name . '</br>' . $qc_pending_conv_qty . ' '. $conv_unit_name . '</td>
                       <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'qc_pending\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\','.$qc_paramter_info.',\''.$grn_trn_id.'\')"><i class="fa fa-eye"></i></button> </td>  
                        </tr>';
                    }

        // $query2 = "select IFNULL(sum(accept_qty),0) as accept_qty,IFNULL(sum(reject_qty),0) as reject_qty,IFNULL(sum(reprocess_qty),0) as reprocess_qty from tbl_batch_data as batch
        // left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id and trn.grn_trn_status = 0
        // left join tbl_grn_sub_trn as strn on trn.grn_trn_id = strn.grn_trn_id and strn.status = 0
        // where  batch.qc_status = 1 and strn.rp_id=" . $rp_id . " and batch.process_id = " . $process_id;
                     $accept_qty = 0;
                    $reject_qty = 0;
                    $reprocess_qty = 0;
                    if($g_whr){

                    $query2 = "select IFNULL(sum(accept_qty),0) as accept_qty,IFNULL(sum(reject_qty),0) as reject_qty,IFNULL(sum(reprocess_qty),0) as reprocess_qty,batch.cdate
                    from tbl_batch_data as batch where batch.qc_status = 1 and batch.product_id = " . $product_id . " and  batch.process_id = " . $process_id . $g_whr;

                    $result2 = $dbcon->query($query2);
                    $rel2 = brp_mysqli_fetch_assoc($result2);
        // echo "<br><br>";
                    $accept_qty = $rel2['accept_qty'];
                    $reject_qty = $rel2['reject_qty'];
                    $reprocess_qty = $rel2['reprocess_qty'];
                }

                    if ($accept_qty > 0)
                    {
                         $accept_conv_qty =  convert_stock($dbcon,$accept_qty,$product_id,'conv_unit');
                        $str .= '<tr style="color:green">
                        <td> ' . $process_name . ' QC Accept </td>
                          <td>' . $accept_qty .  ' ' . $base_unit_name . '</br>' . $accept_conv_qty . ' '. $conv_unit_name . '</td>
                       <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'qc_accept\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\','.$qc_paramter_info.',\''.$grn_trn_id.'\')"><i class="fa fa-eye"></i></button> </td>  
                        </tr>';
                    }

                    if ($reject_qty > 0)
                    {
                         $reject_conv_qty =  convert_stock($dbcon,$reject_qty,$product_id,'conv_unit');
                        $str .= '<tr style="color:green">
                        <td> ' . $process_name . ' QC Reject </td>
                         <td>' . $reject_qty .  ' ' . $base_unit_name . '</br>' . $reject_conv_qty . ' '. $conv_unit_name . '</td>
                       <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'qc_reject\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\','.$qc_paramter_info.',\''.$grn_trn_id.'\')"><i class="fa fa-eye"></i></button> </td>  
                        </tr>';
                    }

                    if ($reprocess_qty > 0)
                    {
                        $reprocess_conv_qty =  convert_stock($dbcon,$reprocess_qty,$product_id,'conv_unit');
                        $str .= '<tr style="color:green">
                        <td> ' . $process_name . ' QC Reprocess </td>
                        <td>' . $reprocess_qty .  ' ' . $base_unit_name . '</br>' . $reprocess_conv_qty . ' '. $conv_unit_name . '</td>
                       <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'qc_reprocess\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\','.$qc_paramter_info.',\''.$grn_trn_id.'\')"><i class="fa fa-eye"></i></button> </td>  
                        </tr>';
                    }

                    $query = "select COALESCE(sum(pen_qty),0) as sqty,COALESCE(sum(start_qty),0) as start_qty, cdate  from tbl_allocate_re_process where process_id='$process_id' and p_product_id = " . $product_id . " and p_ref_id = " . $rp_id . " and company_id=" . $_SESSION['company_id'] . " and pr_process_type=1 and pt_alloc_id in (" . $p_id . ")";

                    $rs_cust = $dbcon->query($query);
                    $rel = brp_mysqli_fetch_array($rs_cust);

        //$total=$rel['sqty']-$rel['stqty'];
                    $reprocess_start_pen = $rel['sqty'] - $rel['start_qty'];
                    $reprocess_end_pen = $rel['start_qty'];

                    if ($reprocess_start_pen > 0)
                    {
                        $reprocess_start_pen_conv_qty =  convert_stock($dbcon,$reprocess_start_pen,$product_id,'conv_unit');
                        $str .= '<tr style="color:red">
                        <td> ' . $process_name . ' Reprocess Start Pending </td>
                      <td>' . $reprocess_start_pen .  ' ' . $base_unit_name . '</br>' . $reprocess_start_pen_conv_qty . ' '. $conv_unit_name . '</td>
                       <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'reprocess_start_pending\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\','.$qc_paramter_info.',\''.$grn_trn_id.'\')"><i class="fa fa-eye"></i></button> </td>
                        </tr>';
                    }

                    if ($reprocess_end_pen > 0)
                    {
                        $reprocess_end_pen_conv_qty =  convert_stock($dbcon,$reprocess_end_pen,$product_id,'conv_unit');
                        $str .= '<tr style="color:red">
                        <td> ' . $process_name . ' Reprocess End Pending </td>
                         <td>' . $reprocess_end_pen .  ' ' . $base_unit_name . '</br>' . $reprocess_end_pen_conv_qty . ' '. $conv_unit_name . '</td>
                       <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'reprocess_end_pending\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\','.$qc_paramter_info.',\''.$grn_trn_id.'\')"><i class="fa fa-eye"></i></button> </td>
                        </tr>';
                    }
                     $reqc_pending_qty = 0;
                if($g_whr){
                    $query1 = "select IFNULL(sum(batch_qty),0) as batch_qty,batch.cdate from tbl_batch_data as batch where batch.qc_status = 0 and reprocess_qc = 1 and  batch.product_id = " . $product_id . "  and batch.process_id = " . $process_id . $g_whr;

                    $result1 = $dbcon->query($query1);
        // echo "<br><br>";
                   
                    while ($rel1 = brp_mysqli_fetch_assoc($result1))
                    {
                        $reqc_pending_qty = $reqc_pending_qty + $rel1['batch_qty'];
                        $reqc_dt = date('d/m/Y', strtotime($rel1['cdate']));
                    }
                }

                    if ($reqc_pending_qty > 0)
                    {
                         $reqc_pending_conv_qty =  convert_stock($dbcon,$reqc_pending_qty,$product_id,'conv_unit');
                        $str .= '<tr style="color:red">
                        <td> ' . $process_name . ' Reprocess QC Pending </td>
                        <td>' . $reqc_pending_qty .  ' ' . $base_unit_name . '</br>' . $reqc_pending_conv_qty . ' '. $conv_unit_name . '</td>
                       <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'reprocess_qc_pending\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\','.$qc_paramter_info.',\''.$grn_trn_id.'\')"><i class="fa fa-eye"></i></button> </td>
                        </tr>';
                    }


                    if($g_whr){
                    $query11 = "select IFNULL(sum(accept_qty),0) as accept_qty,IFNULL(sum(reject_qty),0) as reject_qty,IFNULL(sum(reprocess_qty),0) as reprocess_qty,batch.cdate from tbl_batch_data as batch where batch.qc_status = 1 and reprocess_qc = 1 and  batch.product_id = " . $product_id . "  and batch.process_id = " . $process_id . $g_whr;

                    $result11 = $dbcon->query($query11);
                    $rel11 = brp_mysqli_fetch_assoc($result11);
                    
                    $re_qc_accept = $rel11['accept_qty'];
                    $re_qc_reject = $rel11['reject_qty'];
                    $re_qc_reprocess = $rel11['reprocess_qty'];

                    if ($re_qc_accept > 0)
                    {
                        $re_qc_accept_conv_qty =  convert_stock($dbcon,$re_qc_accept,$product_id,'conv_unit');
                        $str .= '<tr style="color:green">
                        <td> ' . $process_name . ' Reprocess QC Accept </td>
                        <td>' . $re_qc_accept .  ' ' . $base_unit_name . '</br>' . $re_qc_accept_conv_qty . ' '. $conv_unit_name . '</td>
                       <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'reprocess_qc_accept\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\','.$qc_paramter_info.',\''.$grn_trn_id.'\')"><i class="fa fa-eye"></i></button> </td>
                        </tr>';
                    }
                    if ($re_qc_reject > 0)
                    {
                        $re_qc_reject_conv_qty =  convert_stock($dbcon,$re_qc_reject,$product_id,'conv_unit');
                        $str .= '<tr style="color:green">
                        <td> ' . $process_name . ' Reprocess QC Reject </td>
                         <td>' . $re_qc_reject .  ' ' . $base_unit_name . '</br>' . $re_qc_reject_conv_qty . ' '. $conv_unit_name . '</td>
                       <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'reprocess_qc_reject\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\','.$qc_paramter_info.',\''.$grn_trn_id.'\')"><i class="fa fa-eye"></i></button> </td>
                        </tr>';
                    }
                    if ($re_qc_reprocess > 0)
                    {
                         $re_qc_reprocess_conv_qty =  convert_stock($dbcon,$re_qc_reprocess,$product_id,'conv_unit');
                        $str .= '<tr style="color:green">
                        <td> ' . $process_name . ' Reprocess QC Reprocess </td>
                        <td>' . $re_qc_reprocess .  ' ' . $base_unit_name . '</br>' . $re_qc_reprocess_conv_qty . ' '. $conv_unit_name . '</td>
                       <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'reprocess_qc_reprocess\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\','.$qc_paramter_info.',\''.$grn_trn_id.'\')"><i class="fa fa-eye"></i></button> </td>
                        </tr>';
                    }
                   
                }

                }

    // $query3 = "select IFNULL(sum(accept_qty),0) as store_pen_qty from tbl_batch_data as batch
    // left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id and trn.grn_trn_status = 0
    // left join tbl_grn_sub_trn as strn on trn.grn_trn_id = strn.grn_trn_id and strn.status = 0
    // where  batch.qc_status = 1 and stock_approval_status = 0 and strn.rp_id=" . $rp_id . " and batch.process_id = " . $process_id;
                $store_pending = 0;
                if($g_whr){

                   
                    $query3 = "select IFNULL(sum(accept_qty),0) as store_pen_qty,batch.cdate from tbl_batch_data as batch 
                    where batch.qc_status = 1 and  reprocess_qc = 0 and  stock_approval_status = 0  and batch.status = 0 and batch.process_id = " . $process_id . $g_whr;

                    $result3 = $dbcon->query($query3);
                    $rel3 = brp_mysqli_fetch_assoc($result3);

                    $store_pending = $rel3['store_pen_qty'];
                }
                if ($store_pending > 0)
                {
                     $store_pending_conv_qty =  convert_stock($dbcon,$store_pending,$product_id,'conv_unit');
                    $str .= '<tr style="color:red">
                    <td> ' . $process_name . ' Store approval Pending </td>
                    <td>' . $store_pending .  ' ' . $base_unit_name . '</br>' . $store_pending_conv_qty . ' '. $conv_unit_name . '</td>
                   <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'store_approval_pending\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\','.$qc_paramter_info.',\''.$grn_trn_id.'\')"><i class="fa fa-eye"></i></button> </td>
                    </tr>';
                }

    // $query3 = "select IFNULL(sum(accept_qty),0) as store_acp_qty from tbl_batch_data as batch
    // left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id and trn.grn_trn_status = 0
    // left join tbl_grn_sub_trn as strn on trn.grn_trn_id = strn.grn_trn_id and strn.status = 0
    // where  batch.qc_status = 1 and stock_approval_status = 1 and strn.rp_id=" . $rp_id . " and batch.process_id = " . $process_id;
                 $store_accept = 0;
 if($g_whr){


                $query3 = "select IFNULL(sum(accept_qty),0) as store_acp_qty,batch.cdate from tbl_batch_data as batch 
                where batch.qc_status = 1 and stock_approval_status = 1 and batch.product_id = " . $product_id . " and batch.process_id = " . $process_id . $g_whr;

                $result3 = $dbcon->query($query3);
                $rel3 = brp_mysqli_fetch_assoc($result3);
                $store_accept = $rel3['store_acp_qty'];
            }


                if ($store_accept > 0)
                {
                      $store_accept_conv_qty =  convert_stock($dbcon,$store_accept,$product_id,'conv_unit');
                    $str .= '<tr style="color:green">
                    <td> ' . $process_name . ' Store Accepted </td>
                   <td>' . $store_accept .  ' ' . $base_unit_name . '</br>' . $store_accept_conv_qty . ' '. $conv_unit_name . '</td>
                   <td> <button class="btn btn-info" style="padding:2px 5px !important;margin: 5px;" onclick="show_wo_production_tracking_date('.$rp_id.',\'store_accepted\','.$product_id.','.$process_id.','.$priority.',\''.$p_id.'\','.$qc_paramter_info.',\''.$grn_trn_id.'\')"><i class="fa fa-eye"></i></button> </td>

                    </tr>';
                }

                return $str;

            }

            function get_total_in_stock($dbcon, $pro_id, $unit_id, $start_date, $end_date = "")
            {
                $whr = "";

                if ($end_date != "")
                {
                    $whr = ' and date(stock_date) <= "' . date('Y-m-d', strtotime($end_date)) . '"';
                }
                $query = 'SELECT pro.product_id,base_stock_add FROM `product_mst` as pro 

                left join (select sum(qc.base_stock) as base_stock_add,qc.product_id from tbl_stock_trn as qc 
                 where qc.stock_status != 2 and stock_flage=1 and qc.base_unit=' . $unit_id . ' and date(stock_date) >= "' . date('Y-m-d', strtotime($start_date)) . '" and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
                 group by qc.product_id) as qc on qc.product_id=pro.product_id

                 where pro.product_id=' . $pro_id;
                 $rows = mysqli_fetch_assoc($dbcon->query($query));
    // echo "<br><br>";
                 $stock = $rows['base_stock_add'] ;
                 return floatval($stock);
    //return $query;

             }

             function get_total_out_stock($dbcon, $pro_id, $unit_id, $start_date, $end_date = "")
             {
                $whr = "";

                if ($end_date != "")
                {
                    $whr = ' and date(stock_date) <= "' . date('Y-m-d', strtotime($end_date)) . '"';
                }
                $query = 'SELECT pro.product_id,base_stock_minus FROM `product_mst` as pro 

                left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id from tbl_stock_trn as qc 
                  where qc.stock_status != 2 and stock_flage=2 and qc.base_unit=' . $unit_id . ' and date(stock_date) >= "' . date('Y-m-d', strtotime($start_date)) . '"  and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
                  group by qc.product_id) as qc5 on qc5.product_id=pro.product_id
                
                  where pro.product_id=' . $pro_id;
                  $rows = mysqli_fetch_assoc($dbcon->query($query));

    // echo "<br><br>";
    // var_dump($rows['base_stock_minus']);
    // var_dump($rows['con_stock_minus']);
    // var_dump($rows['opening_stock_mi']);
                  $stock = $rows['base_stock_minus'];

                  return floatval($stock);
    //return $query;

              }
              function load_series_nos($dbcon, $invoicetype_id)
              {
                $row = array();
    // $quer = "select invoicetype_id from  tbl_invoicetype where status=0 and type_id=" . $type_id . " and company_id=" . $_SESSION['company_id'];
    // $ro = mysqli_fetch_assoc($dbcon->query($quer));
                $query1 = "select * from  tbl_invoicetype where invoicetype_id=" . $invoicetype_id;
                $rows = mysqli_fetch_assoc($dbcon->query($query1));
                $id = $rows['taxinvoice_start'];
                $id = $id + 1;

    //$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
    //$end = $start+1;
                if ($rows['invoice_format'] == '2')
                {
                    $invoiceno = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
                }
                else if ($rows['invoice_format'] == '1')
                {
                    $invoiceno = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
                }
                else if ($rows['invoice_format'] == '3')
                {
                    $invoiceno = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
                }
                else
                {
                    $invoiceno = str_pad($id, 3, "0", STR_PAD_LEFT);
                }
    //$row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
    //echo json_encode($row);
                return $invoiceno;
            }

            function get_current_opening_stock_below_start_date($dbcon, $pro_id, $unit_id, $start_date)
            {

                $query = 'SELECT pro.product_id,opening_stock_pl,opening_stock_mi FROM `product_mst` as pro 

                left join (select sum(qc.base_stock) as opening_stock_pl,qc.product_id from tbl_stock_trn as qc 
                   where qc.stock_status != 2 and stock_flage=1 and qc.base_unit=' . $unit_id . ' and date(stock_date) < "' . date('Y-m-d', strtotime($start_date)) . '"  and qc.company_id=' . $_SESSION['company_id'] . ' 
                   group by qc.product_id) as qc4 on qc4.product_id=pro.product_id

                   left join (select sum(qc.base_stock) as opening_stock_mi,qc.product_id from tbl_stock_trn as qc 
                   where qc.stock_status != 2 and stock_flage=2 and qc.base_unit=' . $unit_id . ' and date(stock_date) < "' . date('Y-m-d', strtotime($start_date)) . '"  and qc.company_id=' . $_SESSION['company_id'] . ' 
                   group by qc.product_id) as qc5 on qc5.product_id=pro.product_id

                   where pro.product_id=' . $pro_id;

    // echo "</br></br>";
                   $rows = mysqli_fetch_assoc($dbcon->query($query));
                   $stock = $rows['opening_stock_pl'] - $rows['opening_stock_mi'];

    //$stock=($row['base_stock_add']+$row['con_stock_add'])-($row['base_stock_minus']+$row['con_stock_minus']);


                   return floatval($stock);
    //return $query;

               }

               function get_current_reserve_stock($dbcon, $pro_id, $unit_id, $start_date, $end_date = "")
               {
                $whr = "";

                if ($end_date != "")
                {
                    $whr = ' and stock_date <= "' . date('Y-m-d', strtotime($end_date)) . '"';
                }
                $query = 'SELECT pro.product_id,base_stock_add,base_stock_minus,con_stock_minus,con_stock_add,opening_stock_pl,opening_stock_mi FROM `product_mst` as pro 

                left join (select sum(qc.base_stock) as opening_stock_pl,qc.product_id from tbl_stock_trn as qc 
                    where qc.stock_status != 2 and stock_flage=1 and qc.base_unit=' . $unit_id . ' and stock_date >= "' . date('Y-m-d', strtotime($start_date)) . '" and ref_name="opening_stock" and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
                    group by qc.product_id) as qc4 on qc4.product_id=pro.product_id

                    left join (select sum(qc.base_stock) as opening_stock_mi,qc.product_id from tbl_stock_trn as qc 
                    where qc.stock_status != 2 and stock_flage=2 and qc.base_unit=' . $unit_id . ' and stock_date >= "' . date('Y-m-d', strtotime($start_date)) . '" and ref_name="opening_stock" and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
                    group by qc.product_id) as qc5 on qc5.product_id=pro.product_id

                    left join (select sum(qc.base_stock) as base_stock_add,qc.product_id from tbl_stock_trn as qc 
                    where qc.stock_status != 2 and stock_flage=1 and qc.base_unit=' . $unit_id . ' and stock_date =< "' . date('Y-m-d', strtotime($start_date)) . '" and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
                    group by qc.product_id) as qc on qc.product_id=pro.product_id

                    left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id from tbl_stock_trn as qc 
                    where qc.stock_status != 2 and stock_flage=2 and qc.base_unit=' . $unit_id . ' and stock_date =< "' . date('Y-m-d', strtotime($start_date)) . '" and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
                    group by qc.product_id) as qc1 on qc1.product_id=pro.product_id

                    left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id from tbl_stock_trn as qc 
                    where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . ' and stock_date =< "' . date('Y-m-d', strtotime($start_date)) . '" and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
                    group by qc.product_id) as qc2 on qc2.product_id=pro.product_id

                    left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id from tbl_stock_trn as qc 
                    where qc.stock_status != 2 and stock_flage=2 and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . ' and stock_date =< "' . date('Y-m-d', strtotime($start_date)) . '" and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
                    group by qc.product_id) as qc3 on qc3.product_id=pro.product_id

                    where pro.product_id=' . $pro_id;
                    $rows = mysqli_fetch_assoc($dbcon->query($query));
                    $stock = ($rows['base_stock_add'] + $rows['con_stock_add'] + $rows['opening_stock_pl']) - ($rows['base_stock_minus'] + $rows['con_stock_minus'] - $rows['opening_stock_mi']);

    //$stock=($row['base_stock_add']+$row['con_stock_add'])-($row['base_stock_minus']+$row['con_stock_minus']);


                    return floatval($stock);
                }

                function get_current_reserve_stock_below_start_date($dbcon, $pro_id, $unit_id, $start_date)
                {

                    $query = 'SELECT pro.product_id,base_stock_add,base_stock_minus,con_stock_minus,con_stock_add,opening_stock_pl,opening_stock_mi FROM `product_mst` as pro 

                    left join (select sum(qc.base_stock) as opening_stock_pl,qc.product_id from tbl_reserve_stock as qc 
                     where qc.stock_status != 2 and stock_flage=1 and qc.base_unit=' . $unit_id . ' and reserve_date < "' . date('Y-m-d', strtotime($start_date)) . '" and ref_name="opening_stock" and qc.company_id=' . $_SESSION['company_id'] . ' 
                     group by qc.product_id) as qc4 on qc4.product_id=pro.product_id

                     left join (select sum(qc.base_stock) as opening_stock_mi,qc.product_id from tbl_reserve_stock as qc 
                     where qc.stock_status != 2 and stock_flage=2 and qc.base_unit=' . $unit_id . ' and reserve_date < "' . date('Y-m-d', strtotime($start_date)) . '" and ref_name="opening_stock" and qc.company_id=' . $_SESSION['company_id'] . ' 
                     group by qc.product_id) as qc5 on qc5.product_id=pro.product_id

                     left join (select sum(qc.base_stock) as base_stock_add,qc.product_id from tbl_reserve_stock as qc 
                     where qc.stock_status != 2 and stock_flage=1 and qc.base_unit=' . $unit_id . ' and reserve_date < "' . date('Y-m-d', strtotime($start_date)) . '" and ref_name !="opening_stock" and qc.company_id=' . $_SESSION['company_id'] . ' 
                     group by qc.product_id) as qc on qc.product_id=pro.product_id

                     left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id from tbl_reserve_stock as qc 
                     where qc.stock_status != 2 and stock_flage=2 and qc.base_unit=' . $unit_id . ' and reserve_date < "' . date('Y-m-d', strtotime($start_date)) . '"  and ref_name !="opening_stock" and qc.company_id=' . $_SESSION['company_id'] . ' 
                     group by qc.product_id) as qc1 on qc1.product_id=pro.product_id

                     left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id from tbl_reserve_stock as qc 
                     where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . '  and ref_name !="opening_stock" and reserve_date < "' . date('Y-m-d', strtotime($start_date)) . '" and qc.company_id=' . $_SESSION['company_id'] . ' 
                     group by qc.product_id) as qc2 on qc2.product_id=pro.product_id

                     left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id from tbl_reserve_stock as qc 
                     where qc.stock_status != 2 and stock_flage=2 and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . '  and ref_name !="opening_stock" and reserve_date < "' . date('Y-m-d', strtotime($start_date)) . '" and qc.company_id=' . $_SESSION['company_id'] . ' 
                     group by qc.product_id) as qc3 on qc3.product_id=pro.product_id

                     where pro.product_id=' . $pro_id;

    // echo "</br></br>";
                     $rows = mysqli_fetch_assoc($dbcon->query($query));
                     $stock = ($rows['base_stock_add'] + $rows['con_stock_add'] + $rows['opening_stock_pl']) - ($rows['base_stock_minus'] + $rows['con_stock_minus'] - $rows['opening_stock_mi']);

    //$stock=($row['base_stock_add']+$row['con_stock_add'])-($row['base_stock_minus']+$row['con_stock_minus']);


                     return floatval($stock);
    //return $query;

                 }

function get_reserve_stock_ledger($dbcon, $ref_name, $ref_id)
{
    if ($ref_name == "wo_allocate" && $ref_id != "") {
        $q = "select grn.grn_no,led.l_name,gtrn.grn_id from tbl_grn_trn as gtrn
            left join tbl_grn as grn on grn.grn_id=gtrn.grn_id
            left join tbl_ledger as led on led.l_id=grn.vender_id
            where gtrn.grn_trn_id=" . $ref_id;
            $res = $dbcon->query($q);
        $rows = brp_mysqli_fetch_assoc($res);
        if(brp_mysqli_num_rows($res) > 0){
            $auto_no = "Grn No. : " . $rows['grn_no'];
            $vender_name = "(" . $rows['l_name'] . ")";
            $desc = "<a href='" . ROOT . PRODUCTION_ROOT . "grn_print/" . $rows['grn_id'] . "' target='_blank' title='" . $auto_no . "'>" . $auto_no . " " . $vender_name . "</a>";
        }
        
    } else if ($ref_name == "invoice_trn" && $ref_id != "") {
        $q = "select inv.invoice_no,led.l_name,itrn.invoice_id from tbl_invoicetrn as itrn
            left join tbl_invoice as inv on inv.invoice_id = itrn.invoice_id
            left join tbl_ledger as led on led.l_id = inv.cust_id
            where itrn.trancation_id =" . $ref_id;
        $rows = brp_mysqli_fetch_assoc($dbcon->query($q));
        $auto_no = "Invoice No. : " . $rows['invoice_no'];
        $vender_name = "(" . $rows['l_name'] . ")";
        $desc = "<a href='" . ROOT . PRINT_ROOT . "invoicereceipt/" . $rows['invoice_id'] . "' target='_blank' title='" . $auto_no . "'>" . $auto_no . " " . $vender_name . "</a>";
    }else if ($ref_name == "tbl_grn_trn" && $ref_id != "") {
        $q = "select grn.grn_no,grn.grn_id,led.l_name,grn.vender_id from tbl_grn_trn as trn
            left join tbl_grn as grn on grn.grn_id = trn.grn_id
            left join tbl_ledger as led on led.l_id = grn.vender_id
            where trn.grn_trn_id=" . $ref_id;
        $rows = brp_mysqli_fetch_assoc($dbcon->query($q));
        $auto_no = "Grn No. : " . $rows['grn_no'];
        if ($rows['vender_id'] != '-1') {
            $vender_name = "(" . $rows['l_name'] . ")";
        } else {
            $vender_name = "(INHOUSE)";
        }
        $desc = "<a href='" . ROOT . PRODUCTION_ROOT . "grn_print/" . $rows['grn_id'] . "' target='_blank' title='" . $auto_no . "'>" . $auto_no . " " . $vender_name . "</a>";
    } else if ($ref_name == "grn" && $ref_id != "") {
        $q = "select grn.grn_no,led.l_name,grn.vender_id from tbl_grn as grn
            left join tbl_ledger as led on led.l_id = grn.vender_id
            where grn.grn_id=" . $ref_id;
        $rows = brp_mysqli_fetch_assoc($dbcon->query($q));
        $auto_no = "Grn No. : " . $rows['grn_no'];
        if ($rows['vender_id'] != '-1') {
            $vender_name = "(" . $rows['l_name'] . ")";
        } else {
            $vender_name = "(INHOUSE)";
        }
        $desc = "<a href='" . ROOT . PRODUCTION_ROOT . "grn_print/" . $ref_id . "' target='_blank' title='" . $auto_no . "'>" . $auto_no . " " . $vender_name . "</a>";
    } else if ($ref_name == "store_release" && $ref_id != "")
    {
        $q = "select release_id,issue_no,p_id from tbl_store_release
        where release_id=" . $ref_id;

        $rows = mysqli_fetch_assoc($dbcon->query($q));
        $desc = "ON FLOOR STOCK ADDED :  ". $rows['issue_no'] ;
    }else if ($ref_name == "returning_receipt" && $ref_id != "")
    {
        $q = "select chn.id,chn.channal_id,chn.returnable_type from tbl_returnable_channal_item as c
        left join tbl_returnable_channal as chn on chn.id = c.returnable_id
        where c.id =" . $ref_id;
        $rows = brp_mysqli_fetch_assoc($dbcon->query($q));
        if ($rows['returnable_type'] == "non-returnable")
        {
            $auto_no = "Non Returnable Chalan. : " . $rows['channal_id'];
        }
        else
        {
            $auto_no = "Returnable Chalan. : " . $rows['channal_id'];
        }

        $desc = "<a href='" . ROOT . PRINT_ROOT . "challan_print/" . $rows['id'] . "' target='_blank' title='" . $auto_no . "'>" . $auto_no . "</a>";
    }else {
        $desc = $ref_name . " " . $ref_id;
    }
    return $desc;
}

function category_recusive($dbcon, $catid, $frmdate, $todate, $stock1, $k = "")
{
    $stock = array();
    $k = 1;
    if ($catid != '')
    {
        $cat_stock = category_wise_stock($dbcon, $catid, $frmdate, $todate);
        $stock['base_opn_stock'] = $stock1['base_opn_stock'] + $cat_stock['base_opn_stock'];
        $stock['conv_opn_stock'] = $stock1['conv_opn_stock'] + $cat_stock['conv_opn_stock'];
        $stock['base_closing_stock'] = $stock1['base_closing_stock'] + $cat_stock['base_closing_stock'];
        $stock['conv_closing_stock'] = $stock1['conv_closing_stock'] + $cat_stock['conv_closing_stock'];
        $stock['base_opn_rate'] = $stock1['base_opn_rate'] + $cat_rate['base_opn_rate'];
        $stock['conv_opn_rate'] = $stock1['conv_opn_rate'] + $cat_rate['conv_opn_rate'];
        $stock['base_closing_rate'] = $stock1['base_closing_rate'] + $cat_rate['base_closing_rate'];
        $stock['conv_closing_rate'] = $stock1['conv_closing_rate'] + $cat_rate['conv_closing_rate'];
        $query = "select * from tbl_category where cat_status=0 and cat_pid=" . $catid;

        $result = $dbcon->query($query);
        $i = 1;
        while ($row = brp_mysqli_fetch_array($result))
        {
            //var_dump($k);
            $catid = $row['cat_id'];
            $k++;

            $cat_s = category_recusive($dbcon, $catid, $frmdate, $todate, $stock, $k);
            $stock['base_opn_stock'] = $cat_s['base_opn_stock'];
            $stock['conv_opn_stock'] = $cat_s['conv_opn_stock'];
            $stock['base_closing_stock'] = $cat_s['base_closing_stock'];
            $stock['conv_closing_stock'] = $cat_s['conv_closing_stock'];
            $stock['base_opn_rate'] = $stock1['base_opn_rate'] + $cat_rate['base_opn_rate'];
            $stock['conv_opn_rate'] = $stock1['conv_opn_rate'] + $cat_rate['conv_opn_rate'];
            $stock['base_closing_rate'] = $stock1['base_closing_rate'] + $cat_rate['base_closing_rate'];
            $stock['conv_closing_rate'] = $stock1['conv_closing_rate'] + $cat_rate['conv_closing_rate'];
            // echo "<pre>";print_r($cat_s);echo "</pre>";
            $i++;
        }
        /*$stock['base_opn_stock']  = $stock['base_opn_stock'] + $cat_s1['base_opn_stock'];
        $stock['conv_opn_stock']  = $stock['conv_opn_stock'] + $cat_s1['conv_opn_stock'];
        $stock['base_closing_stock']  = $stock['base_closing_stock'] + $cat_s1['base_closing_stock'];
        $stock['conv_closing_stock']  = $stock['conv_closing_stock'] + $cat_s1['conv_closing_stock'];*/
    }
    //echo "<pre>";print_r($stock);echo "</pre>";
    //print_r($stock);
    return $stock;
}
function category_wise_stock($dbcon, $catid, $frmdate, $todate)
{
    //var_dump($catid);
    $query = "SELECT
    IFNULL(pr.plus_opening_stock, 0) AS plus_opening_stock,
    IFNULL(pr1.plus_conv_opening_stock, 0) AS plus_conv_opening_stock,
    IFNULL(pr2.minus_opening_stock, 0) AS minus_opening_stock,
    IFNULL(pr3.minus_conv_opening_stock, 0) AS minus_conv_opening_stock,
    IFNULL(pr4.opening_stock, 0) AS opening_stock,
    IFNULL(pr5.conv_opening_stock, 0) AS conv_opening_stock,
    IFNULL(pr6.closing_stock_plus, 0) AS closing_stock_plus,
    IFNULL(pr7.conv_closing_stock_plus, 0) AS conv_closing_stock_plus,
    IFNULL(pr8.closing_stock_minus, 0) AS closing_stock_minus,
    IFNULL(pr9.conv_closing_stock_minus, 0) AS conv_closing_stock_minus,
    IFNULL(pr.plus_opening_rate, 0) AS plus_opening_rate,
    IFNULL(pr1.plus_conv_opening_rate, 0) AS plus_conv_opening_rate,
    IFNULL(pr2.minus_opening_rate, 0) AS minus_opening_rate,
    IFNULL(pr3.minus_conv_opening_rate, 0) AS minus_conv_opening_rate,
    IFNULL(pr4.opening_rate, 0) AS opening_rate,
    IFNULL(pr5.conv_opening_rate, 0) AS conv_opening_rate,
    IFNULL(pr6.closing_rate_plus, 0) AS closing_rate_plus,
    IFNULL(pr7.conv_closing_rate_plus, 0) AS conv_closing_rate_plus,
    IFNULL(pr8.closing_rate_minus, 0) AS closing_rate_minus,
    IFNULL(pr9.conv_closing_rate_minus, 0) AS conv_closing_rate_minus,
    prd.product_category,
    cat.cat_name
FROM
    tbl_stock_trn AS stock
LEFT JOIN product_mst AS prd ON prd.product_id = stock.product_id
LEFT JOIN tbl_category AS cat ON cat.cat_id = prd.product_category
LEFT JOIN (
    SELECT
        IFNULL(SUM(st.base_stock), 0) AS plus_opening_stock,
        IFNULL(SUM(base_rate) * SUM(base_stock), 0) AS plus_opening_rate,
        pr.product_category AS pcat
    FROM
        tbl_stock_trn AS st
    LEFT JOIN product_mst AS pr ON pr.product_id = st.product_id
    WHERE
        st.stock_status != 2
        AND st.ref_name != 'opening_stock'
        AND st.stock_flage = 1
        AND st.stock_date < '" . $frmdate . "'
    GROUP BY
        pr.product_category
) AS pr ON pr.pcat = prd.product_category
LEFT JOIN (
    SELECT
        IFNULL(SUM(stc.convert_stock), 0) AS plus_conv_opening_stock,
        IFNULL(SUM(conv_rate) * SUM(stc.convert_stock), 0) AS plus_conv_opening_rate,
        pr.product_category AS pcat
    FROM
        tbl_stock_trn AS stc
    LEFT JOIN product_mst AS pr ON pr.product_id = stc.product_id
    WHERE
        stc.stock_status != 2
        AND stc.ref_name != 'opening_stock'
        AND stc.stock_flage = 1
        AND stc.stock_date < '" . $frmdate . "'
    GROUP BY
        pr.product_category
) AS pr1 ON pr1.pcat = prd.product_category
LEFT JOIN (
    SELECT
        IFNULL(SUM(st1.base_stock), 0) AS minus_opening_stock,
        IFNULL(SUM(base_rate) * SUM(st1.base_stock), 0) AS minus_opening_rate,
        pr.product_category AS pcat
    FROM
        tbl_stock_trn AS st1
    LEFT JOIN product_mst AS pr ON pr.product_id = st1.product_id
    WHERE
        st1.stock_status != 2
        AND st1.ref_name != 'opening_stock'
        AND st1.stock_flage = 2
        AND st1.stock_date < '" . $frmdate . "'
    GROUP BY
        pr.product_category
) AS pr2 ON pr2.pcat = prd.product_category
LEFT JOIN (
    SELECT
        IFNULL(SUM(stc1.convert_stock), 0) AS minus_conv_opening_stock,
        IFNULL(SUM(conv_rate) * SUM(stc1.convert_stock), 0) AS minus_conv_opening_rate,
        pr.product_category AS pcat
    FROM
        tbl_stock_trn AS stc1
    LEFT JOIN product_mst AS pr ON pr.product_id = stc1.product_id
    WHERE
        stc1.stock_status != 2
        AND stc1.ref_name = 'opening_stock'
        AND stc1.stock_flage = 2
        AND stc1.stock_date < '" . $frmdate . "'
    GROUP BY
        pr.product_category
) AS pr3 ON pr3.pcat = prd.product_category
LEFT JOIN (
    SELECT
        IFNULL(SUM(st2.base_stock), 0) AS opening_stock,
        IFNULL(SUM(base_rate) * SUM(st2.base_stock), 0) AS opening_rate,
        pr.product_category AS pcat
    FROM
        tbl_stock_trn AS st2
    LEFT JOIN product_mst AS pr ON pr.product_id = st2.product_id
    WHERE
        st2.stock_status != 2
        AND st2.ref_name = 'opening_stock'
    GROUP BY
        pr.product_category
) AS pr4 ON pr4.pcat = prd.product_category
LEFT JOIN (
    SELECT
        IFNULL(SUM(stc2.convert_stock), 0) AS conv_opening_stock,
        IFNULL(SUM(conv_rate) * SUM(stc2.convert_stock), 0) AS conv_opening_rate,
        pr.product_category AS pcat
    FROM
        tbl_stock_trn AS stc2
    LEFT JOIN product_mst AS pr ON pr.product_id = stc2.product_id
    WHERE
        stc2.stock_status != 2
        AND stc2.ref_name = 'opening_stock'
    GROUP BY
        pr.product_category
) AS pr5 ON pr5.pcat = prd.product_category
LEFT JOIN (
    SELECT
        IFNULL(SUM(st3.base_stock), 0) AS closing_stock_plus,
        IFNULL(SUM(base_rate) * SUM(st3.base_stock), 0) AS closing_rate_plus,
        pr.product_category AS pcat
    FROM
        tbl_stock_trn AS st3
    LEFT JOIN product_mst AS pr ON pr.product_id = st3.product_id
    WHERE
        st3.stock_status != 2
        AND st3.stock_date >= '" . $frmdate . "'
        AND st3.stock_date <= '" . $todate . "'
        AND st3.ref_name != 'opening_stock'
        AND st3.stock_flage = 1
    GROUP BY
        pr.product_category
) AS pr6 ON pr6.pcat = prd.product_category
LEFT JOIN (
    SELECT
        IFNULL(SUM(stc3.convert_stock), 0) AS conv_closing_stock_plus,
        IFNULL(SUM(conv_rate) * SUM(stc3.convert_stock), 0) AS conv_closing_rate_plus,
        pr.product_category AS pcat
    FROM
        tbl_stock_trn AS stc3
    LEFT JOIN product_mst AS pr ON pr.product_id = stc3.product_id
    WHERE
        stc3.stock_status != 2
        AND stc3.stock_date >= '" . $frmdate . "'
        AND stc3.stock_date <= '" . $todate . "'
        AND stc3.ref_name != 'opening_stock'
        AND stc3.stock_flage = 1
    GROUP BY
        pr.product_category
) AS pr7 ON pr7.pcat = prd.product_category
LEFT JOIN (
    SELECT
        IFNULL(SUM(st4.base_stock), 0) AS closing_stock_minus,
        IFNULL(SUM(base_rate) * SUM(st4.base_stock), 0) AS closing_rate_minus,
        pr.product_category AS pcat
    FROM
        tbl_stock_trn AS st4
    LEFT JOIN product_mst AS pr ON pr.product_id = st4.product_id
    WHERE
        st4.stock_status != 2
        AND st4.stock_date >= '" . $frmdate . "'
        AND st4.stock_date <= '" . $todate . "'
        AND st4.ref_name != 'opening_stock'
        AND st4.stock_flage = 2
    GROUP BY
        pr.product_category
) AS pr8 ON pr8.pcat = prd.product_category
LEFT JOIN (
    SELECT
        IFNULL(SUM(stc4.convert_stock), 0) AS conv_closing_stock_minus,
        IFNULL(SUM(conv_rate) * SUM(stc4.convert_stock), 0) AS conv_closing_rate_minus,
        pr.product_category AS pcat
    FROM
        tbl_stock_trn AS stc4
    LEFT JOIN product_mst AS pr ON pr.product_id = stc4.product_id
    WHERE
        stc4.stock_status != 2
        AND stc4.stock_date >= '" . $frmdate . "'
        AND stc4.stock_date <= '" . $todate . "'
        AND stc4.ref_name != 'opening_stock'
        AND stc4.stock_flage = 2
    GROUP BY
        pr.product_category
) AS pr9 ON pr9.pcat = prd.product_category
WHERE
    stock.stock_status != 2
    AND stock.company_id = " . $_SESSION['company_id'] . " 
    AND prd.product_category = " . $catid . "
GROUP BY
    cat.cat_id";

    $result = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($result);

    $base_opening_stock = $row['opening_stock'] + $row['plus_opening_stock'] - $row['minus_opening_stock'];
    $conv_opening_stock = $row['conv_opening_stock'] + $row['plus_conv_opening_stock'] - $row['minus_conv_opening_stock'];
    $base_closing_stock = $base_opening_stock + $row['closing_stock_plus'] - $row['closing_stock_minus'];
    $conv_closing_stock = $conv_opening_stock + $row['conv_closing_stock_plus'] - $row['conv_closing_stock_minus'];
    $base_opening_rate = $row['opening_rate'] + $row['plus_opening_rate'] - $row['minus_opening_rate'];
    $conv_opening_rate = $row['conv_opening_rate'] + $row['plus_conv_opening_rate'] - $row['minus_conv_opening_rate'];
    $base_closing_rate = $base_opening_rate + $row['closing_rate_plus'] - $row['closing_rate_minus'];
    $conv_closing_rate = $conv_opening_rate + $row['conv_closing_rate_plus'] - $row['conv_closing_rate_minus'];
    $stock['category_name'] = $row['cat_name'];
    $stock['base_opn_stock'] = $base_opening_stock;
    $stock['conv_opn_stock'] = $conv_opening_stock;
    $stock['base_closing_stock'] = $base_closing_stock;
    $stock['conv_closing_stock'] = $conv_closing_stock;
    $stock['base_opn_rate'] = $base_opening_rate;
    $stock['conv_opn_rate'] = $conv_opening_rate;
    $stock['base_closing_rate'] = $base_closing_rate;
    $stock['conv_closing_rate'] = $conv_closing_rate;

    return $stock;
}
function category_wise_pro_stock($dbcon, $catid, $frmdate, $todate)
{

    $query = "select plus_opening_stock, plus_conv_opening_stock, minus_opening_stock, minus_conv_opening_stock, opening_stock,conv_opening_stock, closing_stock_plus, conv_closing_stock_plus, closing_stock_minus, pro.product_name, bunit.unit_name as baseunit, cunit.unit_name as conv_unit, strn.base_unit, strn.convert_unit, strn.product_id, pro.product_min_stock, pro.product_max_stock,(select IFNULL(st.base_rate,0) from tbl_stock_trn as st where st.stock_status != 2  and st.stock_flage=1 and st.ref_name='tbl_grn_trn' and st.product_id = strn.product_id  order by st.stock_id desc limit 1) as base_rate,(select IFNULL(st.conv_rate,0) from tbl_stock_trn as st where st.stock_status != 2  and st.stock_flage=1 and st.ref_name='tbl_grn_trn' and st.product_id = strn.product_id  order by st.stock_id desc limit 1) as conv_rate  from tbl_stock_trn as strn

     left join (select st.base_rate,st.conv_rate,st.product_id from tbl_stock_trn as st where st.stock_status != 2  and st.stock_flage=1 and st.stock_date<='" . $frmdate . "' and st.ref_name='tbl_grn_trn' order by st.stock_id desc limit 1) as strate on strate.product_id=strn.product_id

    left join (select sum(st.base_stock) as plus_opening_stock,product_id from tbl_stock_trn as st where st.stock_status != 2  and st.stock_flage=1 and st.stock_date<='" . $frmdate . "' and st.ref_name!='opening_stock' group by st.product_id) as st on st.product_id=strn.product_id

    left join (select sum(stc.convert_stock) as plus_conv_opening_stock,stc.product_id from tbl_stock_trn as stc where stc.stock_status != 2 and stc.stock_flage=1 and stc.stock_date<='" . $frmdate . "' and stc.ref_name!='opening_stock' group by stc.product_id ) as stc on stc.product_id=strn.product_id

    left join (select sum(st1.base_stock) as minus_opening_stock,st1.product_id from tbl_stock_trn as st1 where st1.stock_status != 2  and st1.stock_flage=2 and st1.stock_date<='" . $frmdate . "' and st1.ref_name!='opening_stock' group by st1.product_id ) as st1 on st1.product_id=strn.product_id

    left join (select sum(stc1.convert_stock) as minus_conv_opening_stock,product_id from tbl_stock_trn as stc1 where stc1.stock_status != 2 and stc1.stock_flage=2 and stc1.stock_date<='" . $frmdate . "' and stc1.ref_name!='opening_stock' group by stc1.product_id) as stc1 on stc1.product_id=strn.product_id

    left join (select sum(st2.base_stock) as opening_stock,st2.product_id from tbl_stock_trn as st2 where st2.stock_status != 2  and st2.ref_name='opening_stock' group by st2.product_id) as st2 on st2.product_id=strn.product_id

    left join (select sum(stc2.convert_stock) as conv_opening_stock, stc2.product_id from tbl_stock_trn as stc2 where stc2.stock_status != 2 and stc2.ref_name='opening_stock' group by stc2.product_id) as stc2 on stc2.product_id=strn.product_id

    left join (select sum(base_stock) as closing_stock_plus,st3.product_id from tbl_stock_trn as st3 where st3.stock_status != 2  and st3.stock_date>='" . $frmdate . "' and st3.stock_date<='" . $todate . "' and st3.ref_name!='opening_stock' and st3.stock_flage=1 group by st3.product_id) as st3 on st3.product_id=strn.product_id

    left join (select sum(stc3.convert_stock) as conv_closing_stock_plus,stc3.product_id from tbl_stock_trn as stc3 where stc3.stock_status != 2 and stc3.stock_date>='" . $frmdate . "' and stc3.stock_date<='" . $todate . "' and stc3.ref_name!='opening_stock' and stc3.stock_flage=1 group by stc3.product_id) as stc3 on stc3.product_id=strn.product_id

    left join (select sum(base_stock) as closing_stock_minus,st4.product_id from tbl_stock_trn as st4 where st4.stock_status != 2  and st4.stock_date>='" . $frmdate . "' and st4.stock_date<='" . $todate . "' and st4.ref_name!='opening_stock' and st4.stock_flage=2 group by st4.product_id) as st4 on st4.product_id=strn.product_id

    left join (select sum(stc4.convert_stock) as conv_closing_stock_minus,stc4.product_id from tbl_stock_trn as stc4 where stc4.stock_status != 2 and stc4.stock_date>='" . $frmdate . "' and stc4.stock_date<='" . $todate . "' and stc4.ref_name!='opening_stock' and stc4.stock_flage=2 group by stc4.product_id) as stc4 on  stc4.product_id=strn.product_id

    left join product_mst as pro on pro.product_id = strn.product_id
    left join unit_mst as bunit on bunit.unitid = strn.base_unit
    left join unit_mst as cunit on cunit.unitid = strn.convert_unit
    where strn.stock_status != 2 and pro.product_category=" . $catid . " and strn.company_id=" . $_SESSION['company_id'] . " group by strn.product_id";

    $result = $dbcon->query($query);
    return $result;
}

function get_so_taxable_total($dbcon)
{
    $query = $dbcon->query("SELECT SUM(invs.g_total) as g_total, SUM(invtrn.product_amount) as total FROM tbl_sales_ordertrn as invtrn LEFT JOIN tbl_sales_order as invs ON invs.sales_order_id = invtrn.sales_order_id WHERE invs.sales_order_status = 0 AND invtrn.sales_ordertrn_status = 0 AND invs.approve_status = 3 AND invs.order_accept_status = 1 AND invs.revise_status=0 AND invs.company_id = '" . $_SESSION['company_id'] . "'");
    $res = brp_mysqli_fetch_assoc($query);
    return $res['g_total'] . ',' . $res['total'];
}
function get_po_taxable_total($dbcon)
{
    $query = $dbcon->query("SELECT SUM(invs.g_total) as g_total, SUM(invtrn.product_amount) as total FROM tbl_purchaseordertrn as invtrn LEFT JOIN tbl_purchaseorder as invs ON invs.purchaseorder_id = invtrn.purchaseorder_id WHERE invs.status = 0 AND invtrn.purchaseordertrn_status = 0 AND invs.revise_status=0 AND invs.company_id = '" . $_SESSION['company_id'] . "'");
    $res = brp_mysqli_fetch_assoc($query);

    return $res;
}
function get_quot_won_taxable_total($dbcon)
{
    $query = $dbcon->query("SELECT SUM(invs.g_total) as g_total, COUNT(invs.quotation_id) as total FROM tbl_quotation_trn as invtrn LEFT JOIN tbl_quotation as invs ON invs.quotation_id = invtrn.quotation_id left join tbl_inquiry as inq on inq.inquiry_id=invs.inquiry_id WHERE invs.quotation_status = 0 AND invtrn.quot_trn_status = 0 AND invs.revise_status=0 AND inq.stage_prob = '100' AND invs.company_id = '" . $_SESSION['company_id'] . "'");
    $res = brp_mysqli_fetch_assoc($query);
    return $res['g_total'] . ',' . $res['total'];
}
function get_quot_lost_taxable_total($dbcon)
{
    $query = $dbcon->query("SELECT SUM(invs.g_total) as g_total, COUNT(invs.quotation_id) as total FROM tbl_quotation_trn as invtrn LEFT JOIN tbl_quotation as invs ON invs.quotation_id = invtrn.quotation_id left join tbl_inquiry as inq on inq.inquiry_id=invs.inquiry_id WHERE invs.quotation_status = 0 AND invtrn.quot_trn_status = 0 AND invs.revise_status=0 AND inq.stage_prob=0 AND invs.company_id = '" . $_SESSION['company_id'] . "'");
    $res = brp_mysqli_fetch_assoc($query);
    return $res['g_total'] . ',' . $res['total'];
}
function get_purchase_bill_taxable_total($dbcon)
{
    $query = "SELECT SUM(invs.g_total) as g_total, SUM(invtrn.product_amount) as total FROM tbl_potrancation as invtrn LEFT JOIN tbl_pono as invs ON invs.po_id = invtrn.po_id WHERE invs.status = 0 AND invtrn.potrancation_status = 0 AND invs.company_id = '" . $_SESSION['company_id'] . "'";
    $res = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $res['g_total'] . ',' . $res['total'];
}

function get_bom_no($dbcon, $id)
{
    $query = $dbcon->query("SELECT bom_no FROM tbl_bom WHERE bom_id = " . $id);
    $res = brp_mysqli_fetch_assoc($query);
    return $res['bom_no'];
}
function get_reserve_stock_qty($dbcon, $sales_ordertrn_id)
{
    $str = '';
    $query = $dbcon->query("SELECT base_unit, base_stock FROM tbl_reserve_stock WHERE ref_id = 0 AND stock_flage = 1 AND stock_status!=2 AND sales_order_trn_id = " . $sales_ordertrn_id);
    $cnt = brp_mysqli_num_rows($query);
    if ($cnt > 0)
    {
        $str = '<tr>
        <td colspan="2" style="background: lavender;">Sales order wise Planning</td>
        </tr>
        <tr>
        <td>Stock Allocate Wise</td>
        <td>Yes</td>
        </tr>';
        while ($res = brp_mysqli_fetch_assoc($query))
        {
            $unitname = getunitname($dbcon, $res['base_unit']);
            $str .= "<tr>
            <td>Allocate Stock</td>
            <td>" . $res['base_stock'] . " " . $unitname . "</td>
            </tr>";
        }
    }
    return $str;
}
function get_invoice_no_by_so($dbcon, $sales_ordertrn_id)
{
    $str = '';
    $query = $dbcon->query("SELECT inv.invoice_no, inv.invoice_date, invtrn.product_qty, invtrn.unit_id, inv.cdate, user.user_name FROM tbl_invoicetrn as invtrn LEFT JOIN tbl_invoice as inv ON invtrn.invoice_id = inv.invoice_id LEFT JOIN users as user on inv.user_id = user.user_id WHERE invtrn.trancation_status = 0 AND invtrn.sales_ordertrn_id = " . $sales_ordertrn_id);
    $cnt = brp_mysqli_num_rows($query);
    if ($cnt > 0)
    {
        $str = '<tr>
        <td colspan="2" style="background: lavender;">Invoice Details</td>
        </tr>
        <tr>
        <td colspan="2">
        <table class="table table-bordered" width="100%">
        <tr style="background: linen;">
        <td>Invoice No</td>
        <td>Invoice Date</td>
        <td>Invoice Qty</td>
        <td>Username</td>
        <td>Entry Date & Time</td>
        </tr>';
        while ($res = brp_mysqli_fetch_assoc($query))
        {
            $unitname = getunitname($dbcon, $res['unit_id']);
            $str .= "<tr>
            <td>" . $res['invoice_no'] . "</td>
            <td>" . date("d-M-Y", strtotime($res['invoice_date'])) . "</td>
            <td>" . $res['product_qty'] . " " . $unitname . "</td>
            <td>" . $res['user_name'] . "</td>
            <td>" . date("d-M-Y h:i A", strtotime($res['cdate'])) . "</td>
            </tr>";
        }
        $str .= '</table>
        </td>
        </tr>';
    }
    return $str;
}
function get_wo_detail_by_so($dbcon, $sales_ordertrn_id, $unit_id)
{
    $str = '';
    $query = $dbcon->query("SELECT smp.po_req_no, smp.rp_req_qty, rp.indent_no, rp.rp_req_qty as rp_qty, smp.po_req_date, smp.cdate, rp.indent_date FROM tbl_set_main_process as smp LEFT JOIN tbl_request_product as rp ON rp.sp_id = smp.sp_id WHERE smp.sp_status = 0 AND rp.status = 0 AND rp.main_request = 1 AND smp.sales_order_trn_id = " . $sales_ordertrn_id);
    $cnt = brp_mysqli_num_rows($query);
    if ($cnt > 0)
    {
        $str = '<tr>
        <td colspan="2" style="background: lavender;">Sales order wise Planning</td>
        </tr>
        <tr>
        <td colspan="2">
        <table class="table table-bordered" width="100%">
        <tr style="background: lemonchiffon;">
        <td colspan="4">Work Order Details</td>
        </tr>
        <tr style="background: linen;">
        <td>Work Order No</td>
        <td>Work Order Date</td>
        <td>Work Order Qty</td>
        <td>Entry Date & Time</td>
        </tr>';
        while ($res = brp_mysqli_fetch_assoc($query))
        {
            $unitname = getunitname($dbcon, $unit_id);
            $str .= "<tr>
            <td>" . $res['po_req_no'] . "</td>
            <td>" . date("d-M-Y", strtotime($res['po_req_date'])) . "</td>
            <td>" . $res['rp_req_qty'] . " " . $unitname . "</td>
            <td>" . date("d-M-Y", strtotime($res['cdate'])) . "</td>
            </tr>";
            if ($res['indent_no'] != '')
            {
                $str .= "<tr>
                <td>" . $res['indent_no'] . "</td>
                <td>" . date("d-M-Y", strtotime($res['indent_date'])) . "</td>
                <td>" . $res['rp_qty'] . " " . $unitname . "</td>
                <td>" . date("d-M-Y", strtotime($res['cdate'])) . "</td>
                </tr>";
            }
        }
        $str .= '</table>
        </td>
        </tr>';
    }
    return $str;
}
function get_so_approved_log($dbcon, $sales_order_id)
{
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [ORDER_ACCEPTANCE_SLUG_DELETE]);
    $str = '';
    $query = $dbcon->query("SELECT log.*, usr.user_name, sales.approve_status as ap_status, sales.order_accept_status as oa_status FROM tbl_quot_po_aprv_log as log LEFT JOIN users as usr on usr.user_id=log.user_id LEFT JOIN tbl_sales_order AS sales ON sales.sales_order_id = log.sales_order_id WHERE log.quot_aprv_log_status=0 AND log.sales_order_id=" . $sales_order_id . " ORDER BY log.quot_aprv_log_id DESC");
    $cnt = brp_mysqli_num_rows($query);
    if ($cnt > 0)
    {
        $str .= '<tr>
        <td colspan="3" style="background: lavender; font-weight: bold;">Sales Order Approved Log</td>
        </tr>
        <tr>
        <td colspan="3">
        <table class="table table-bordered" width="100%">
        <tr style="background: linen; font-weight: bold;">
        <td>Username</td>
        <td>Status</td>
        <td>Remarks</td>
        <td>Date & Time</td>
        <td>Action</td>
        </tr>';
        $i = 1;
        while ($res = brp_mysqli_fetch_assoc($query))
        {
            if ($res['approve_status'] == '1')
            {
                $row_data = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
            }
            else
            {
                $row_data = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Reject</div>';
            }
            $delete_btn = '';
            if (in_array(ORDER_ACCEPTANCE_SLUG_DELETE, $bulkAccessArray) && $i == 1 && $res['oa_status'] == 0)
            {
                $delete_btn = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_approve_log(' . $res['sales_order_id'] . ',' . $res['quot_aprv_log_id'] . ',' . $res['approve_status'] . ',1)"><i class="fa fa-trash-o"></i></button>';
            }
            $str .= '<tr>
            <td>' . $res['user_name'] . '</td>
            <td>' . $row_data . '</td>
            <td>' . nl2br($res['approve_remark']) . '</td>
            <td>' . date("d-M-Y h:i A", strtotime($res['cdate'])) . '</td>
            <td>' . $delete_btn . '</td>
            </tr>';
            $i++;
        }
        $str .= '</table>
        </td>
        </tr>';
    }
    return $str;
}
function get_oa_approved_log($dbcon, $sales_order_id)
{
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [ORDER_ACCEPTANCE_SLUG_DELETE]);
    $str = '';
    $query = $dbcon->query("SELECT log.*, usr.user_name FROM tbl_oa_aprv_log as log LEFT JOIN users as usr on usr.user_id=log.user_id WHERE log.so_aprv_log_status=0 AND log.so_id=" . $sales_order_id . " ORDER BY log.oa_aprv_log_id DESC");
    $cnt = brp_mysqli_num_rows($query);
    if ($cnt > 0)
    {
        $str .= '<tr>
        <td colspan="3" style="background: lavender; font-weight: bold;">Order Acceptance Approved Log</td>
        </tr>
        <tr>
        <td colspan="3">
        <table class="table table-bordered" width="100%">
        <tr style="background: linen; font-weight: bold;">
        <td>Username</td>
        <td>Status</td>
        <td>Remarks</td>
        <td>Date & Time</td>
        <td>Action</td>
        </tr>';
        $i = 1;
        while ($res = brp_mysqli_fetch_assoc($query))
        {
            if ($res['approve_status'] == '1')
            {
                $row_data = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
            }
            else
            {
                $row_data = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Reject</div>';
            }
            $delete_btn = '';
            if (in_array(ORDER_ACCEPTANCE_SLUG_DELETE, $bulkAccessArray) && $i == 1)
            {
                $delete_btn = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_approve_log(' . $sales_order_id . ',' . $res['oa_aprv_log_id'] . ',' . $res['approve_status'] . ',2)"><i class="fa fa-trash-o"></i></button>';
            }
            $str .= '<tr>
            <td>' . $res['user_name'] . '</td>
            <td>' . $row_data . '</td>
            <td>' . nl2br($res['approve_remark']) . '</td>
            <td>' . date("d-M-Y h:i A", strtotime($res['created_at'])) . '</td>
            <td>' . $delete_btn . '</td>
            </tr>';
            $i++;
        }
        $str .= '</table>
        </td>
        </tr>';
    }
    return $str;
}

function count_so_stock_allocation($dbcon, $user_id)
{

    $where = '';
    $branch_id = ($_SESSION['branch_id'] == 0) ? "" : " AND so.branch_id = " . $_SESSION['branch_id'];
    /*if ($_SESSION['user_type'] != '2' && $_SESSION['user_type'] != '9')
    {
        $ser = trim(check_crm_find_in_set($dbcon, $_SESSION['user_id'], 0) , ",");
        $where .= ' and so.user_id IN (' . $ser . ')';
    }
    else
    {
        $ser = trim(check_crm_find_in_set($dbcon, $user_id, 0) , ",");
        $where .= ' and so.user_id IN (' . $ser . ')';
    }*/
    $query = "SELECT so.sales_order_no, so.sales_order_date, led.l_name, so_trn.product_qty, so_trn.sales_ordertrn_id, mst.product_name, tc.cat_name, so.delivery_date, bran.branch_name, so_trn.product_id, so_trn.work_order_qty, so_trn.unit_id, (IFNULL(product_qty,0)-IFNULL(stock_add,0)) as pending_qty,so.jobwork_type FROM tbl_sales_ordertrn as so_trn 
    left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id 
    left join tbl_ledger as led on led.l_id=so.cust_id 
    left join product_mst as mst on mst.product_id=so_trn.product_id 
    left join tbl_category as tc on mst.product_category=tc.cat_id 
    left join (select IFNULL(sum(qc.product_qty),0) as stock_add,qc.sales_ordertrn_id from tbl_sales_order_production_trn as qc where qc.sales_order_production_status=0 group by qc.sales_ordertrn_id) as qc on qc.sales_ordertrn_id=so_trn.sales_ordertrn_id 
    left join branch_mst as bran on bran.branch_id=so_trn.branch_id 
    where ( 1 AND so_trn.sales_ordertrn_status=0 and so_trn.bom_id=0 and so_trn.bom_status=0 and so_trn.production_status=0 and mst.product_type!=8 and so_trn.short_close_status=0 and so_trn.invoice_status=0  and so.order_accept_status = 1 and so.approve_status=3 " . $where . " and so.company_id=" . $_SESSION['company_id'] . ") having pending_qty > 0 ORDER BY so.delivery_date desc";

        $rs = $dbcon->query($query);
        $cnt = brp_mysqli_num_rows($rs);
        $row = brp_mysqli_fetch_array($rs);
        if (empty($row['qty']))
        {
            $row['qty'] = 0;
        }
    //return $row['qty'];
        return $cnt;
    //return $query;
    }
    function count_store_material_request($dbcon, $type)
    {

        $query = "SELECT SQL_CALC_FOUND_ROWS pro.product_icode, dr.drawing_number, pro.product_id, pro.product_base_unit, pro.product_name, tc.cat_name, pro.product_status, pro.product_min_stock, reqqty, req_qty, base_stock_add, base_stock_minus, con_stock_add, con_stock_minus, (((IFNULL(base_stock_add,0)+IFNULL(con_stock_add,0))-(IFNULL(base_stock_minus,0)+IFNULL(con_stock_minus,0)))+IFNULL(reqqty,0) + IFNULL(req_qty,0)) as stock, pro.product_category FROM product_mst as pro left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id left join (select sum(ord.base_request_qty) as req_qty,ord.product_id from tbl_store_order_min_max as ord where ord.status=0 group by ord.product_id) as re_req on re_req.product_id=pro.product_id left join (select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0 group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id left join (select sum(qc.base_stock) as base_stock_add,qc.product_id,qc.base_unit from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.company_id=" . $_SESSION['company_id'] . " group by qc.product_id) as qc4 on qc4.product_id=pro.product_id and qc4.base_unit=pro.product_base_unit left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id,qc.base_unit from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=2 and qc.company_id=" . $_SESSION['company_id'] . " group by qc.product_id) as qc1 on qc1.product_id=pro.product_id and qc1.base_unit=pro.product_base_unit left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id,qc.convert_unit from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.company_id=" . $_SESSION['company_id'] . " group by qc.product_id) as qc2 on qc2.product_id=pro.product_id and qc2.convert_unit=pro.product_base_unit left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id,qc.convert_unit from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=2 and qc.base_unit!=qc.convert_unit and qc.company_id=" . $_SESSION['company_id'] . " group by qc.product_id) as qc3 on qc3.product_id=pro.product_id and qc3.convert_unit=pro.product_base_unit where ( 1 AND pro.product_status=0 and pro.product_min_stock!=0 and pro.company_id=" . $_SESSION['company_id'] . " ) having stock < pro.product_min_stock";

          $rs = $dbcon->query($query);

          $count = brp_mysqli_num_rows($rs);

          return $count;
    //return $query;

      }

      function get_indent_approve_qty($dbcon, $rp_id)
      {
        $query = "select sum(approve_qty) as aprv_qty,u.unit_name from approve_indent as a
        left join unit_mst as u on u.unitid = a.approve_unit 
        where approve_indent_status=0 and rp_id=" . $rp_id;

        $res = brp_mysqli_fetch_assoc($dbcon->query($query));
        return $res['aprv_qty'] . ' ' . $res['unit_name'];
    }

    function get_purchase_order_quotation_detail($dbcon,$approve_indent_id){
        $query = "select mst.*,cat.l_name,ai.rp_id from po_quotation as mst
        left join tbl_ledger as cat on cat.l_id=mst.vender_id 
        left join approve_indent as ai on mst.approve_indent_id=ai.approve_indent_id 
        where mst.po_quotation_status!=2 and mst.approve_indent_id=".$approve_indent_id;
        $result = $dbcon->query($query);
        $cnt = brp_mysqli_num_rows($result);
        $str = '';
        $str .= '<table class="table table-bordered" style="width:100%">
        <tr>
            <td>Sr No.</td>
            <td>Vendor Name</td>
            <td>Quotation No</td>
            <td>Quotation Date</td>
            <td>Delivery Date</td>
            <td>Payment Days</td>
            <td>Rate</td>
        </tr>';
        if ($cnt > 0)
        {
            $i=1;
            while ($row = brp_mysqli_fetch_array($result))
            {
                $str .= '<tr>
                    <td>' . $i . '</td>
                    <td>' . $row['l_name'] . '</td>
                    <td>' . $row['quotation_no'] . '</td>
                    <td>' . date('d M, Y',strtotime($row['quotation_date'])) . '</td>
                    <td>' . date('d M, Y',strtotime($row['delivery_date'])) . '</td>
                    <td>' . $row['payment_days'] . '</td>
                    <td>' . $row['product_rate'] . '</td>
                </tr>';
                $i++;
            }
        }
        else
        {
            $str .= '<tr>
            <td colspan="7" style="text-align:center">No Data Yet...!!</td>
            </tr>';
        }

        $str .= '</table>';
        return $str;
    }

    function get_approved_detail($dbcon, $rp_id)
    {
        $query = "select a.*,u.unit_name,us.user_name,temp.purchaseordertrn_id as temptrn_ref_id from approve_indent as a 
        left join unit_mst as u on u.unitid = a.approve_unit
        left join users as us on us.user_id = a.user_id
        left join tbl_purchasetrntemp as temp on temp.approve_indent_id = a.approve_indent_id
        where approve_indent_status=0 and rp_id=" . $rp_id;
        $result = $dbcon->query($query);
        $cnt = brp_mysqli_num_rows($result);
        $str = '';
        $str .= '<table class="table table-bordered" style="width:100%">
        <tr>
        <td>Approve No.</td>
        <td>Approve Date</td>
        <td>Approve Qty</td>
        <td>Entry Date Time</td>
        <td>User Name</td>
        </tr>';
        if ($cnt > 0)
        {
            while ($row = brp_mysqli_fetch_array($result))
            {
                $str .= '<tr>
                <td>' . $row['approve_no'] . '</td>
                <td>' . date('d-m-Y', strtotime($row['approve_date'])) . '</td>
                <td>' . $row['approve_qty'] . ' ' . $row['unit_name'] . '</td>
                <td>' . $row['cdate'] . '</td>
                <td>' . $row['user_name'] . '</td>
                </tr>';

                $str .= '<tr>
                <td colspan="5" style="height:25px"></td>
                </tr>';

                $str .='<tr>
                    <th colspan="5" style="text-align: center;background-color: lavender;">Purchase Order Qutation Detail</th>
                </tr>

                <tr>
                    <th colspan="5" style="height: 40px;">
                        '.get_purchase_order_quotation_detail($dbcon,$row['approve_indent_id']).'
                    </th>
                </tr>';

                $str .= '<tr>
                <th colspan="3">Po Qty</th>
                <th colspan="2">' . get_asper_indent_poqty($dbcon, $row['temptrn_ref_id']) . '</th>
                </tr>
                <tr>
                <th colspan="5" style="text-align: center;background-color: lavender;">PO Detail</th>
                </tr>
                <tr>
                <th colspan="5" style="height: 40px;">' . get_asper_indent_podetail($dbcon, $row['temptrn_ref_id']) . '</th>
                </tr>';
            }
        }
        else
        {
            $str .= '<tr>
            <td colspan="5" style="text-align:center">No Data Yet...!!</td>
            </tr>';
        }

        $str .= '</table>';
        return $str;
    }
  function get_asper_indent_poqty($dbcon, $temptrn_ref_id) {
    // Check if $temptrn_ref_id is empty or not a valid integer
    if (empty($temptrn_ref_id) || !is_numeric($temptrn_ref_id)) {
        return "0 "; // Return a default value if $temptrn_ref_id is invalid
    }

    // Proceed with the query only if $temptrn_ref_id is valid
    $query = "SELECT sum(used_qty) as po_qty, un.unit_name
              FROM tbl_purchaseorder_req_trn as req
              LEFT JOIN tbl_request_product as re ON re.rp_id = req.rp_id
              LEFT JOIN unit_mst as un ON un.unitid = re.purchase_unit
              WHERE purchaseordertrn_req_status=0 AND req.req_id=" . $temptrn_ref_id;

    $result = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($result);

    // Check if the result is valid
    if (!$row) {
        return "0 "; // Return a default value if no rows are found
    }

    return $row['po_qty'] . " " . $row['unit_name'];
}

   function get_asper_indent_podetail($dbcon, $temptrn_ref_id) {
    // Check if $temptrn_ref_id is empty or not a valid integer
    if (empty($temptrn_ref_id) || !is_numeric($temptrn_ref_id)) {
        return '<table class="table table-bordered" style="width:100%">
            <tr>
                <td colspan="6" style="text-align:center">No Data Yet...!!</td>
            </tr>
        </table>';
    }

    // Proceed with the query only if $temptrn_ref_id is valid
    $query = "SELECT re.*, po.purchaseorder_id, po.purchaseorder_no, po.purchaseorder_date, led.l_name, un.unit_name, us.user_name, po.cdate, req.purchase_unit
              FROM tbl_purchaseorder_req_trn as re
              LEFT JOIN tbl_purchaseordertrn as ptr ON ptr.purchaseordertrn_id = re.purchaseordertrn_id
              LEFT JOIN tbl_purchaseorder as po ON po.purchaseorder_id = ptr.purchaseorder_id
              LEFT JOIN tbl_ledger as led ON led.l_id = po.vender_id
              LEFT JOIN tbl_request_product as req ON re.rp_id = req.rp_id
              LEFT JOIN unit_mst as un ON un.unitid = req.purchase_unit
              LEFT JOIN users as us ON us.user_id = po.userid
              WHERE purchaseordertrn_req_status=0 AND re.req_id=" . $temptrn_ref_id;

    $result = $dbcon->query($query);
    $cnt = brp_mysqli_num_rows($result);
    $str = '';
    $str .= '<table class="table table-bordered" style="width:100%">
    <tr>
        <td>PO No.</td>
        <td>PO Date</td>
        <td>Vendor Name</td>
        <td>Qty</td>
        <td>Entry Date Time</td>
        <td>User Name</td>
    </tr>';

    if ($cnt > 0) {
        while ($row = brp_mysqli_fetch_array($result)) {
            $str .= '<tr>
            <td>' . $row['purchaseorder_no'] . '</td>
            <td>' . date('d-m-Y', strtotime($row['purchaseorder_date'])) . '</td>
            <td>' . $row['l_name'] . '</td>
            <td>' . $row['used_qty'] . ' ' . $row['unit_name'] . '</td>
            <td>' . $row['cdate'] . '</td>
            <td>' . $row['user_name'] . '</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:center;background-color: lavender;">Po Approve Detail</td>
            </tr>
            <tr>
                <td colspan="6" style="height: 40px;">' . get_po_approved_detail($dbcon, $row['purchaseorder_id']) . '</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:center;background-color: lavender;">Po Finance Approve Detail</td>
            </tr>
            <tr>
                <td colspan="6" style="height: 40px;">' . get_pofinance_approved_detail($dbcon, $row['purchaseorder_id']) . '</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:center;height:25px"></td>
            </tr>
            <tr>
                <td colspan="3">Grn Qty</td>
                <td colspan="3">' . get_poagainst_grn($dbcon, $row['purchaseordertrn_id'], $row['rp_id']) . '</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align: center;background-color: lavender;">Grn Detail</td>
            </tr>
            <tr>
                <td colspan="6" style="height: 40px;">' . get_poagainst_grn_detail($dbcon, $row['purchaseordertrn_id'], $row['rp_id']) . '</td>
            </tr>';
        }
    } else {
        $str .= '<tr>
        <td colspan="6" style="text-align:center">No Data Yet...!!</td>
        </tr>';
    }
    $str .= '</table>';
    return $str;
}


    function get_po_approved_detail($dbcon, $purchaseordderid)
    {
        $query = "select ap.*,us.user_name from tbl_purchaseorder_aprv_log as ap
        left join users as us on us.user_id = ap.user_id
        where is_delete=0 and purchaseorder_id=" . $purchaseordderid;

        $result = $dbcon->query($query);
        $cnt = brp_mysqli_num_rows($result);
        $str = '';
        $str .= '<table class="table table-bordered" style="width:100%">
        <tr>
        <td>Sr.no</td>
        <td>User</td>
        <td>Status</td>
        <td>Remark</td>
        <td>Entry Date Time</td>
        </tr>';
        if ($cnt > 0)
        {
            $i = 1;
            while ($row = brp_mysqli_fetch_array($result))
            {
                if ($row['approve_status'] == 3)
                {
                    $status = '<strong style="color:green">Approved</strong>';
                }
                else
                {
                    $status = '<strong style="color:orange">Disapproved</strong>';
                }
                $str .= '<tr>
                <td>' . $i . '</td>
                <td>' . $row['user_name'] . '</td>
                <td>' . $status . '</td>
                <td>' . $row['approve_remark'] . '</td>
                <td>' . $row['cdate'] . '</td>
                </tr>';
                $i++;
            }
        }
        else
        {
            $str .= '<tr>
            <td colspan="5" style="text-align:center">No Data Yet...!!</td>
            </tr>';
        }
        $str .= '</table>';
        return $str;
    }

    function get_pofinance_approved_detail($dbcon, $purchaseordderid)
    {
        $query = "select ap.*,us.user_name from tbl_purchaseorder_finance_aprv_log as ap
        left join users as us on us.user_id = ap.user_id
        where is_delete=0 and purchaseorder_id=" . $purchaseordderid;

        $result = $dbcon->query($query);
        $cnt = brp_mysqli_num_rows($result);
        $str = '';
        $str .= '<table class="table table-bordered" style="width:100%">
        <tr>
        <td>Sr.no</td>
        <td>User</td>
        <td>Status</td>
        <td>Remark</td>
        <td>Entry Date Time</td>
        </tr>';
        if ($cnt > 0)
        {
            $i = 1;
            while ($row = brp_mysqli_fetch_array($result))
            {
                if ($row['approve_status'] == 1)
                {
                    $status = '<strong style="color:green">Approved</strong>';
                }
                else
                {
                    $status = '<strong style="color:orange">Disapproved</strong>';
                }
                $str .= '<tr>
                <td>' . $i . '</td>
                <td>' . $row['user_name'] . '</td>
                <td>' . $status . '</td>
                <td>' . $row['approve_remark'] . '</td>
                <td>' . $row['cdate'] . '</td>
                </tr>';
                $i++;
            }
        }
        else
        {
            $str .= '<tr>
            <td colspan="5" style="text-align:center">No Data Yet...!!</td>
            </tr>';
        }
        $str .= '</table>';
        return $str;
    }

    function get_po_shorclose_detail($dbcon,$purchaseordertrn_id){
        $query = "select shr.*,unit.unit_name,us.user_name from tbl_log_po_short_close as shr 
        left join unit_mst as unit on unit.unitid = shr.unit_id
        left join users as us on us.user_id = shr.user_id
        where shr.aproove_status=1 and shr.short_close_status=0 and shr.po_trn_id=".$purchaseordertrn_id;

        $result = $dbcon->query($query);
        $cnt = brp_mysqli_num_rows($result);

        $str = '';

        $str .='<table class="table table-bordered" style="width:100%">
        <tr>
        <td>Sr.No</td>
        <td>Short Close Date</td>
        <td>Short Close Qty</td>
        <td>Short Close Reason</td>
        <td>User</td>
        </tr>';

        if ($cnt > 0)
        {
            $i=1;
            while ($row = brp_mysqli_fetch_array($result))
            {
                $str .='<tr>
                    <td>'.$i.'</td>
                    <td>'.date('d-m-Y',strtotime($row['date'])).'</td>
                    <td>'.$row['short_close_qty'].' '.$row['unit_name'].'</td>
                    <td>'.$row['short_close_reason'].'</td>
                    <td>'.$row['user_name'].'</td>
                </tr>';
                $i++;
            }
        }else{
            $str .= '<tr>
            <td colspan="5" style="text-align:center">No Data Yet...!!!</td>
            </tr>';
        }

        $str .='</table>';
        return $str;
    }


    function get_poagainst_grn($dbcon, $purchaseordertrn_id, $rp_id)
    {

        if ($rp_id)
        {
            $where = " and gstr.rp_id=" . $rp_id;
            $left_join = "left join unit_mst as bun on bun.unitid = req.purchase_unit";
            $field = '';
        }
        else
        {
            $where = '';
            $left_join = "left join unit_mst as bun on bun.unitid = gstr.product_base_unit
            left join unit_mst as cun on cun.unitid = gstr.product_conv_unit";
            $field = ',cun.unit_name as conv_unit';
        }
        $query = "select sum(gstr.product_qty) as base_qty, sum(gstr.product_conv_qty) as conv_qty,gstr.product_base_unit,gtr.rate_unit,req.purchase_unit,bun.unit_name " . $field . " from tbl_grn_sub_trn as gstr
        left join tbl_grn_trn as gtr on gtr.grn_trn_id = gstr.grn_trn_id
        left join tbl_grn as grn on grn.grn_id = gtr.grn_id
        left join tbl_request_product as req on req.rp_id=gstr.rp_id
        " . $left_join . "
        where gstr.status=0 and gstr.purchaseordertrn_id=" . $purchaseordertrn_id . $where;

        $result = $dbcon->query($query);
        $row = brp_mysqli_fetch_array($result);

        if ($rp_id)
        {
            if ($row['product_base_unit'] == $row['purchase_unit'])
            {
                $product_qty = $row['base_qty'];
            }
            else
            {
                $product_qty = $row['conv_qty'];
            }
        }
        else
        {
            if ($row['product_base_unit'] == $row['rate_unit'])
            {
                $product_qty = $row['base_qty'];
            }
            else
            {
                $product_qty = $row['conv_qty'];
                $row['unit_name'] = $row['conv_unit'];
            }
        }
    //return $query;
        return $product_qty . " " . $row['unit_name'];
    }
    function get_poagainst_grn_detail($dbcon, $purchaseordertrn_id, $rp_id)
    {

        if ($rp_id)
        {
            $where = " and gstr.rp_id=" . $rp_id;
            $left_join = "left join unit_mst as bun on bun.unitid = req.purchase_unit";
            $field = '';
            $type = 'indent';
        }
        else
        {
            $where = '';
            $left_join = "left join unit_mst as bun on bun.unitid = gstr.product_base_unit
            left join unit_mst as cun on cun.unitid = gstr.product_conv_unit";
            $field = ',cun.unit_name as conv_unit';
            $type = 'po';
        }

        $query = "select grn.grn_no,grn.grn_date,us.user_name,gtr.product_qty,gtr.product_conv_qty,bun.unit_name,gstr.product_base_unit,gtr.rate_unit,req.purchase_unit,grn.cdate,gstr.grn_trn_sub_id,gtr.grn_trn_id " . $field . " from tbl_grn_sub_trn as gstr
        left join tbl_grn_trn as gtr on gtr.grn_trn_id = gstr.grn_trn_id
        left join tbl_grn as grn on grn.grn_id = gtr.grn_id
        left join tbl_request_product as req on req.rp_id=gstr.rp_id
        " . $left_join . "
        left join users as us on us.user_id = grn.user_id
        where gstr.status=0 and gstr.purchaseordertrn_id=" . $purchaseordertrn_id . $where;

        $result = $dbcon->query($query);
        $cnt = brp_mysqli_num_rows($result);
        $str = '';
        $str .= '<table class="table table-bordered" style="width:100%">
        <tr>
        <td>GRN.No</td>
        <td>GRN Date</td>
        <td>Qty</td>
        <td>Entry Date Time</td>
        <td>User</td>
        </tr>';
        if ($cnt > 0)
        {
            while ($row = brp_mysqli_fetch_array($result))
            {
                if ($rp_id)
                {
                    if ($row['purchase_unit'] == $row['product_base_unit'])
                    {
                        $product_qty = $row['product_qty'];
                    }
                    else
                    {
                        $product_qty = $row['product_conv_qty'];
                    }
                }
                else
                {
                    if ($row['rate_unit'] == $row['product_base_unit'])
                    {
                        $product_qty = $row['product_qty'];
                    }
                    else
                    {
                        $product_qty = $row['product_conv_qty'];
                        $row['unit_name'] = $row['conv_unit'];
                    }
                }
                $str .= '<tr>
                <td>' . $row['grn_no'] . '</td>
                <td>' . date('d-m-Y', strtotime($row['grn_date'])) . '</td>
                <td>' . $product_qty . ' ' . $row['unit_name'] . '</td>
                <td>' . $row['cdate'] . '</td>
                <td>' . $row['user_name'] . '</td>
                </tr>

                <tr>
                <td colspan="5" style="text-align:center;height:25px"></td>
                </tr>

                <tr>
                <th colspan="3">Purchase BIll Qty</th>
                <th colspan="2">' . get_inward_against_purchasebill_qty($dbcon, $row['grn_trn_sub_id'], $type) . '</th>
                </tr>
                <tr>
                <th colspan="5" style="text-align: center;background-color: lavender;">Purchase Bill Detail</th>
                </tr>
                <tr>
                <th colspan="5" style="height: 40px;">
                ' . get_inward_against_purchasebill_detail($dbcon, $row['grn_trn_sub_id'], $type) . ' 
                </th>
                </tr>

                <tr>
                <td colspan="5" style="text-align:center;height:25px"></td>
                </tr>

                <tr>
                <th colspan="3">Purchase Qc Qty</th>
                <th colspan="2">' . get_purchase_qcqty($dbcon, $row['grn_trn_id']) . '</th>
                </tr>
                <tr>
                <th colspan="5" style="text-align: center;background-color: lavender;">Purchase Qc Detail</th>
                </tr>
                <tr>
                <th colspan="5" style="height: 40px;">' . get_purchase_qcqty_detail($dbcon, $row['grn_trn_id']) . '</th>
                </tr>

                <tr>
                <td colspan="5" style="text-align:center;height:25px"></td>
                </tr>

                <tr>
                <th colspan="3">Store Receive Qty</th>
                <th colspan="2">' . get_storerecieved_qty($dbcon, $row['grn_trn_id']) . '</th>
                </tr>
                <tr>
                <th colspan="5" style="text-align: center;background-color: lavender;">Store Receive Detail</th>
                </tr>
                <tr>
                <th colspan="5" style="height: 40px;">' . get_storerecieved_qty_detail($dbcon, $row['grn_trn_id']) . '</th>
                </tr>';
            }
        }
        else
        {
            $str .= '<tr>
            <td colspan="5" style="text-align:center">No Data Yet...!!!</td>
            </tr>';
        }
        $str .= '</table>';
        return $str;
    }
    function get_inward_against_purchasebill_qty($dbcon, $grn_sub_trn_id, $type)
    {
        if ($type == 'indent')
        {
            $left_join = 'left join unit_mst as un on un.unitid = req.purchase_unit';
        }
        else
        {
            $left_join = 'left join unit_mst as un on un.unitid = ptr.rate_unit';
        }
        $query = "select sum(ptr.product_qty) as base_qty, sum(ptr.product_conv_qty) as conv_qty, un.unit_name, req.purchase_unit, ptr.unit_id,ptr.rate_unit from tbl_potrancation as ptr 
        left join tbl_grn_sub_trn as gstr on gstr.grn_trn_sub_id = ptr.grn_sub_trn_id
        left join tbl_request_product as req on req.rp_id = gstr.rp_id
        " . $left_join . "
        where ptr.potrancation_status=0 and ptr.grn_sub_trn_id=" . $grn_sub_trn_id;

        $result = $dbcon->query($query);
        $row = brp_mysqli_fetch_array($result);

        if ($type == 'indent')
        {
            if ($row['purchase_unit'] == $row['unit_id'])
            {
                $product_qty = $row['base_qty'];
            }
            else
            {
                $product_qty = $row['conv_qty'];
            }
        }
        else
        {
            if ($row['rate_unit'] == $row['unit_id'])
            {
                $product_qty = $row['base_qty'];
            }
            else
            {
                $product_qty = $row['conv_qty'];
            }
        }
        return $product_qty . " " . $row['unit_name'];
    }

    function get_inward_against_purchasebill_detail($dbcon, $grn_sub_trn_id, $type)
    {
        if ($type == 'indent')
        {
            $left_join = 'left join unit_mst as un on un.unitid = req.purchase_unit';
        }
        else
        {
            $left_join = 'left join unit_mst as un on un.unitid = ptr.rate_unit';
        }
        $query = "select po.po_no,po.po_date,us.user_name, un.unit_name, req.purchase_unit, ptr.unit_id,led.l_name,ptr.product_qty,ptr.product_conv_qty,po.cdate,ptr.rate_unit from tbl_potrancation as ptr 
        left join tbl_pono as po on po.po_id = ptr.po_id
        left join tbl_grn_sub_trn as gstr on gstr.grn_trn_sub_id = ptr.grn_sub_trn_id
        left join tbl_request_product as req on req.rp_id = gstr.rp_id
        " . $left_join . "
        left join tbl_ledger as led on led.l_id = po.vender_id
        left join users as us on us.user_id = po.userid
        where ptr.potrancation_status=0 and ptr.grn_sub_trn_id=" . $grn_sub_trn_id;

        $result = $dbcon->query($query);
        $cnt = brp_mysqli_num_rows($result);
        $str = '';
        $str .= '<table class="table table-bordered" style="width:100%">
        <tr>
        <td>Purchase Bill No</td>
        <td>Purchase Bill Date</td>
        <td>Vendor Name</td>
        <td>Qty</td>
        <td>Entry Date Time</td>
        <td>User</td>
        </tr>';
        if ($cnt > 0)
        {
            while ($row = brp_mysqli_fetch_array($result))
            {
                if ($type == 'indent')
                {
                    if ($row['purchase_unit'] == $row['unit_id'])
                    {
                        $purchase_qty = $row['product_qty'];
                    }
                    else
                    {
                        $purchase_qty = $row['product_conv_qty'];
                    }
                }
                else
                {
                    if ($row['purchase_unit'] == $row['unit_id'])
                    {
                        $purchase_qty = $row['product_qty'];
                    }
                    else
                    {
                        $purchase_qty = $row['product_conv_qty'];
                    }
                }

                $str .= '<tr>
                <td>' . $row['po_no'] . '</td>
                <td>' . date('d-m-Y', strtotime($row['po_date'])) . '</td>
                <td>' . $row['l_name'] . '</td>
                <td>' . $purchase_qty . ' ' . $row['unit_name'] . '</td>
                <td>' . $row['cdate'] . '</td>
                <td>' . $row['user_name'] . '</td>
                </tr>';
            }
        }
        else
        {
            $str .= '<tr>
            <td colspan="6" style="text-align:center">No Data Yet....!!!</td>
            </tr>';
        }
        $str .= '</table>';
        return $str;
    }

    function get_purchase_qcqty($dbcon, $grn_trn_id)
    {
        $query = "select sum(qtrn.qc_product_qty) as qc_qty,un.unit_name from tbl_qc as qc 
        left join tbl_qc_trn as qtrn on qtrn.qc_id = qc.qc_id
        left join tbl_grn_trn as gtr on gtr.grn_trn_id = qc.grn_trn_id
        left join unit_mst as un on un.unitid = gtr.rate_unit
        where qc.qc_status=0 and qtrn.qc_status=0 and qc.grn_trn_id=" . $grn_trn_id;

        $result = $dbcon->query($query);
        $row = brp_mysqli_fetch_array($result);

        return $row['qc_qty'] . " " . $row['unit_name'];
    }

    function get_purchase_qcqty_detail($dbcon, $grn_trn_id)
    {
        $query = "select qc.*,un.unit_name,gtr.rate_unit,us.user_name from tbl_qc as qc 
        left join tbl_grn_trn as gtr on gtr.grn_trn_id=qc.grn_trn_id
        left join unit_mst as un on un.unitid = gtr.rate_unit
        left join users as us on us.user_id = qc.user_id
        where qc.qc_status=0 and qc.grn_trn_id=" . $grn_trn_id;

        $result = $dbcon->query($query);
        $cnt = brp_mysqli_num_rows($result);
        $str = '';
        $str .= '<table class="table table-bordered" style="width:100%">
        <tr>
        <td>QC No</td>
        <td>QC Date</td>
        <td>Qc Accept Qty</td>
        <td>Qc Reject Qty</td>
        <td>Entry Date Time</td>
        <td>User</td>
        </tr>';
        if ($cnt > 0)
        {
            while ($row = brp_mysqli_fetch_array($result))
            {
                if ($row['rate_unit'] == $row['accepted_base_unit'])
                {
                    $product_accept_qty = $row['accepted_base_qty'];
                    $product_reject_qty = $row['rejected_base_unit'];
                }
                else
                {
                    $product_accept_qty = $row['accepted_conv_qty'];
                    $product_reject_qty = $row['rejected_conv_unit'];
                }
                $str .= '<tr>
                <td>' . $row['qc_no'] . '</td>
                <td>' . date('d-m-Y', strtotime($row['qc_date'])) . '</td>
                <td>' . $product_accept_qty . ' ' . $row['unit_name'] . '</td>
                <td>' . $product_reject_qty . ' ' . $row['unit_name'] . '</td>
                <td>' . $row['cdate'] . '</td>
                <td>' . $row['user_name'] . '</td>
                </tr>';
            }
        }
        else
        {
            $str .= '<tr>
            <td colspan="6" style="text-align:center">No Data Yet...!!!</td>
            </tr>';
        }
        $str .= '</table>';
        return $str;
    }

    function get_storerecieved_qty($dbcon, $grn_trn_id)
    {
        $query = "select sum(strn.qty) as approval_qty,un.unit_name from tbl_store_accept_trn as strn 
        left join tbl_store_accept as sac on sac.store_accept_id=strn.store_accept_id
        left join unit_mst as un on un.unitid = strn.unit_id
        where strn.store_accept_trn_status=0 and strn.grn_trn_id=" . $grn_trn_id;
        $result = $dbcon->query($query);
        $row = brp_mysqli_fetch_array($result);

        return $row['approval_qty'] . " " . $row['unit_name'];
    }

    function get_storerecieved_qty_detail($dbcon, $grn_trn_id)
    {
        $query = "select strn.*,sac.store_accept_no,sac.cdate,us.user_name,un.unit_name from tbl_store_accept_trn as strn 
        left join tbl_store_accept as sac on sac.store_accept_id=strn.store_accept_id
        left join tbl_grn_trn as gtrn on gtrn.grn_trn_id=strn.grn_trn_id
        left join unit_mst as un on un.unitid = strn.unit_id
        left join users as us on us.user_id = sac.user_id
        where strn.store_accept_trn_status=0 and strn.grn_trn_id=" . $grn_trn_id;

        $result = $dbcon->query($query);
        $cnt = brp_mysqli_num_rows($result);
        $str = '';
        $str .= '<table class="table table-bordered" style="width:100%">
        <tr>
        <td>Store Accept No</td>
        <td>Approval Qty</td>
        <td>Remark</td>
        <td>Entry Date Time</td>
        <td>User</td>
        </tr>';
        if ($cnt > 0)
        {
            while ($row = brp_mysqli_fetch_array($result))
            {
                $str .= '<tr>
                <td>' . $row['store_accept_no'] . '</td>
                <td>' . $row['qty'] . ' ' . $row['unit_name'] . '</td>
                <td>' . $row['remark'] . '</td>
                <td>' . $row['cdate'] . '</td>
                <td>' . $row['user_name'] . '</td>
                </tr>';
            }
        }
        else
        {
            $str .= '<tr>
            <td colspan="6" style="text-align:center">No Data Yet...!!!</td>
            </tr>';
        }
        $str .= '</table>';

        return $str;
    }
    function user_wiseapproval_permission($dbcon, $module_type)
    {
        $str = '';
        $str .= '<div class="row">
        <div class="col-sm-4">
        <section class="panel">
        <div class="panel-body">
        <div class="form-group">
        <label><strong>Choose User</strong></label>
        <select class="select2" id="permission_user_id' . $module_type . '" name="permission_user_id" >
        ' . getalluser($dbcon, '') . '
        </select>
        </div>';
        
        if ($module_type == 1) {
            $str .= '<div class="form-group">
            <label><strong>Select Amount Type*</strong></label>
            <select class="select2" id="amount_type' . $module_type . '" name="amount_type" >
            <option value="1">Amount</option>
            <option value="2">Percentage</option>
            </select>
            </div>';

            $str .= '<div class="form-group percentage-approval hidden">
            <label><strong>Percentage</strong></label>
            <input class="form-control numbersOnly" type="text" name="percentage" id="percentage' . $module_type . '" placeholder="percentage" value="" />
            </div>';

            $str .= '<div class="form-group amount-approval">
                        <label><strong>Amount</strong></label>
                        <input class="form-control numbersOnly" type="text" name="amount" id="amount' . $module_type . '" placeholder="Amount" value="" />
                        </div>';
        } else {
            $str .= '<div class="form-group">
        <label><strong>Amount</strong></label>
        <input class="form-control numbersOnly" type="text" name="amount" id="amount' . $module_type . '" placeholder="Amount" value="" />
        </div>
        <input class="form-control hidden" type="text" id="amount_type' . $module_type . '" name="amount_type" value="1" />';
        }
        
        $str .= '<div class="form-group">
        <label><strong>Auto Approval</strong></label>
        <select class="select2" id="auto_approval' . $module_type . '" name="auto_approval" >
        <option value="0">No</option>
        <option value="1">Yes</option>
        </select>
        </div>
        <input type="hidden" name="module_type" id="module_type' . $module_type . '" value="' . $module_type . '">
        <button type="button" onclick="add_userwise_approval(' . $module_type . ')" class="btn btn-success">Submit</button>
        </div>
        </section>
        </div>

        <div class="col-sm-8">
        <section class="panel">
        <div class="panel-body">
        <div class="adv-table">
        <table class="display table table-bordered table-striped" id="user_wise_approval_' . $module_type . '">
        <thead>
        <tr>
        <th>Sr. NO.</th>
        <th>User Name</th>
        <th>Amount</th>';

        if ($module_type == 1) {
            $str .= '<th>Percentage</th>';
        }
        
        $str .= '<th>Auto Approval</th>
        <th class="hidden-phone">Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        </table>
        </div>
        </div>
        </section>
        </div>
        </div>';
        return $str;
    }
    function get_product_unit($dbcon,$product_id){
     $query  = "select product_base_unit,product_conv_unit from product_mst where product_id=".$product_id;
     $result = $dbcon->query($query);
     $row = brp_mysqli_fetch_array($result);

     return $row;
 }
 function count_store_order_request($dbcon, $type)
{
   /* $query = "SELECT SQL_CALC_FOUND_ROWS mst.order_id, pro.product_id, mst.base_unit, reqqty, pro.product_name, tc.cat_name, mst.base_qty, mst.wo_base_qty, mst.base_request_qty, (((IFNULL(base_stock_add,0)+IFNULL(con_stock_add,0))-(IFNULL(base_stock_minus,0)+IFNULL(con_stock_minus,0)))+IFNULL(reqqty,0)) as stock FROM tbl_store_order_min_max as mst left join product_mst as pro on pro.product_id=mst.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join (select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0 group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id left join (select sum(qc.base_stock) as base_stock_add,qc.product_id,qc.base_unit from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.company_id=1 group by qc.product_id) as qc4 on qc4.product_id=pro.product_id and qc4.base_unit=pro.product_base_unit left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id,qc.base_unit from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=2 and qc.company_id=1 group by qc.product_id) as qc1 on qc1.product_id=pro.product_id and qc1.base_unit=pro.product_base_unit left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id,qc.convert_unit from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.company_id=1 group by qc.product_id) as qc2 on qc2.product_id=pro.product_id and qc2.convert_unit=pro.product_base_unit left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id,qc.convert_unit from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=2 and qc.base_unit!=qc.convert_unit and qc.company_id=1 group by qc.product_id) as qc3 on qc3.product_id=pro.product_id and qc3.convert_unit=pro.product_base_unit where ( 1 AND mst.status=0 and mst.wo_complete_status=0 and mst.company_id=1 ) having stock < mst.base_qty ORDER BY pro.product_name desc";*/

   $query = "SELECT SQL_CALC_FOUND_ROWS mst.doc_no, mst.order_id, pro.product_id, mst.base_unit, reqqty, pro.product_name, tc.cat_name, mst.base_qty, mst.wo_base_qty, mst.base_request_qty FROM tbl_store_order_min_max as mst left join product_mst as pro on pro.product_id=mst.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join (select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0 group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id where ( 1 AND mst.status=0 and mst.wo_complete_status=0 and mst.company_id=".$_SESSION['company_id']." and mst.bom_status = 1)";

        $rs = $dbcon->query($query);

        $count = brp_mysqli_num_rows($rs);
        return $count;
    }

function get_check_batch_no($dbcon,$batch_no,$batch_stock_id=''){
    $where='';
    if($batch_stock_id){
        $where = " and batch_stock_id!=".$batch_stock_id;
    }
    $query1 = "select batch_stock_no from tbl_batch_stock_trn_in where status=0 and batch_stock_no='$batch_no'".$where;
    $rs1    = $dbcon->query($query1);
    $cnt1   = brp_mysqli_num_rows($rs1);

    $query2 = "select batch_no from tbl_batch_data status=0 and batch_no='$batch_no'";
    $rs2    = $dbcon->query($query2);
    $cnt2   = brp_mysqli_num_rows($rs2);

    $query3 = "select batch_no from tbl_stock_trn where stock_status != 2 and batch_no='$batch_no'";
    $rs3    = $dbcon->query($query3); 
    $cnt3   = brp_mysqli_num_rows($rs3);

    $cnt = $cnt1+$cnt2+$cnt3;
    return $cnt;
}

function get_temp_batch_no($dbcon,$count,$product_id){

    $company_config = getCompanyConfiguration($dbcon);

    if ($company_config['batch_wise_stock'] == '1')
    { // batch_wise_stock  0 : no , 1 : yes
        // batch wise stock permission - yes
        if ($company_config['batch_no_stock'] == '0')
        { // batch_no_stock  0 : general wise, 1 : product wise
            // GENERAL WISE STOCK BATCH NO
            $query = "select * from tbl_invoicetype where status=0 and type_id=30 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
            $result = $dbcon->query($query);
            $row = brp_mysqli_fetch_assoc($result);
            $rows = array();
            $query1 = "select * from  tbl_invoicetype where invoicetype_id=" . $row['invoicetype_id'];
            $rows = brp_mysqli_fetch_assoc($dbcon->query($query1));
            $id = $rows['taxinvoice_start'];
            $id = $id + $count;

            if ($rows['invoice_format'] == '2')
            {
                return $row['invoiceno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
            }
            else if ($rows['invoice_format'] == '1')
            {
                return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
            }
            else if ($rows['invoice_format'] == '3')
            {
                return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
            }
            else
            {
                return $row['invoiceno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
            }
        }
        else
        {
            // PRODUCT WISE STOCK BATCH NO
            $query = "select * from  product_mst where product_id = " . $product_id;
            $result = $dbcon->query($query);
            $rows = brp_mysqli_fetch_assoc($result);

            $id = $rows['taxinvoice_start'];
            $id = $id + $count;

            return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
        }
    }
    else
    {
        // batch wise stock permission - no
        $query = "select * from tbl_invoicetype where status=0 and type_id=30 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
        $result = $dbcon->query($query);
        $row = brp_mysqli_fetch_assoc($result);
        $rows = array();
        $query1 = "select * from  tbl_invoicetype where invoicetype_id=" . $row['invoicetype_id'];
        $rows = brp_mysqli_fetch_assoc($dbcon->query($query1));
        $id = $rows['taxinvoice_start'];
        $id = $id + $count;

        if ($rows['invoice_format'] == '2')
        {
            return $row['invoiceno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
        }
        else if ($rows['invoice_format'] == '1')
        {
            return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
        }
        else if ($rows['invoice_format'] == '3')
        {
            return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
        }
        else
        {
            return $row['invoiceno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
        }
    }
}

function get_invoice_type_list($dbcon,$type_id,$invoicetype_id){
    $query = "select invoice_type,invoicetype_id from tbl_invoicetype where type_id=".$type_id." and status=0 and company_id=".$_SESSION['company_id']." and financial_year_id = ".$_SESSION['financial_year_id'];
    $result = $dbcon->query($query);
    $str = '';
    while($row = brp_mysqli_fetch_array($result)){
        $sel='';
        if($row['invoicetype_id']==$invoicetype_id){
            $sel = "selected='selected'";
        }

        $str .='<option '.$sel.' value="'.$row['invoicetype_id'].'">'.$row['invoice_type'].'</option>';
    }

    return $str;
}
function getcurrencydetail($dbcon,$id)
{
    $query = "SELECT * from tbl_currency where currency_status=0 and currency_id=".$id;
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel;
}

function hsn_wise_tax_category($dbcon,$hsn_id){
    $query = "select hsn.sale_gst,tca.tax_cat_name from mst_hsn_code as hsn
    left join tbl_tax_category as tca on tca.tax_cat_id=hsn.sale_gst
    where hsn_status=0 and hsn_id=".$hsn_id;
    $result = $dbcon->query($query);
    $str = '';
    $str .='<option  value="">--Choose Tax Category--</option>';
    while($row = brp_mysqli_fetch_array($result)){
        
        $str .='<option value="'.$row['sale_gst'].'">'.$row['tax_cat_name'].'</option>';
    }

    return $str;
}
function wip_stock_po_stock_add($dbcon,$poid){
    $query = "select purchaseordertrn_id,product_conv_qty,product_qty from tbl_purchaseordertrn as hsn
        where purchaseordertrn_status=0 and purchaseorder_id=".$poid;
    $result = $dbcon->query($query);
    while($row = brp_mysqli_fetch_array($result)){
        
        $query1 = "select purchaseordertrn_req__id, IFNULL(used_qty,0) as usedqty
        from tbl_purchaseorder_req_trn as hsn
        left join tbl_request_product as req on req.rp_id=hsn.rp_id
        where purchaseordertrn_req_status=0 and req.pre_trn_id=0 and hsn.purchaseordertrn_id=".$row['purchaseordertrn_id']
        ;
         $result1 = $dbcon->query($query1);
        $row1 = brp_mysqli_fetch_array($result1);
        $conv_stock = $row['product_conv_qty'] - $row1['usedqty'];
        $base_stock=$row['product_qty']*$conv_stock/$row['product_conv_qty'];
        $info_followup_status['wip_po_stock'] = $base_stock;
        $info_followup_status['wip_po_stock_conv'] = $conv_stock;
        update_record('tbl_purchaseordertrn', $info_followup_status,"purchaseordertrn_id=".$row['purchaseordertrn_id'] , $dbcon);

    }
}

function get_quotation_complete_sales_order($dbcon,$sales_order_id){
    $query = "select * from tbl_sales_ordertrn where sales_ordertrn_status=0 and sales_order_id=".$sales_order_id;
    $result = $dbcon->query($query);
    while($row = brp_mysqli_fetch_array($result)){
        $query_quot = "select qtrn.*,(select sum(strn.product_qty) from tbl_sales_ordertrn as strn where strn.sales_ordertrn_status=0 and strn.quot_trn_id = qtrn.quot_trn_id) as base_qty, (select sum(strn.product_conv_qty) from tbl_sales_ordertrn as strn where strn.sales_ordertrn_status=0 and strn.quot_trn_id = qtrn.quot_trn_id) as conv_qty from tbl_quotation_trn as qtrn
        where quot_trn_id =".$row['quot_trn_id'];
        $result_quot = $dbcon->query($query_quot);
        $quot_row = brp_mysqli_fetch_array($result_quot);
        if($quot_row['unitid']==$row['unit_id']){
            if($quot_row['base_qty'] >= $quot_row['product_qty']){
                $info_quot['sales_order_status'] = 1;
            }else{
                $info_quot['sales_order_status'] = 0;
            }
            update_record('tbl_quotation_trn', $info_quot,"quot_trn_id=".$row['quot_trn_id'] , $dbcon);
        }else{
            if($quot_row['conv_qty'] >= $quot_row['product_conv_qty']){
                $info_quot['sales_order_status'] = 1;
            }else{
                $info_quot['sales_order_status'] = 0;
            }
            update_record('tbl_quotation_trn', $info_quot,"quot_trn_id=".$row['quot_trn_id'] , $dbcon);
        }

        $quot = "select (select count(*) from tbl_quotation_trn as qtt where qtt.quot_trn_status=0 and qtt.quotation_id=quot.quotation_id) as total_qt , (select count(*) from tbl_quotation_trn as qtt where qtt.sales_order_status=1 and qtt.quot_trn_status=0 and qtt.quotation_id=quot.quotation_id) as done_qt from tbl_quotation as quot
        where quot.quotation_id = ".$row['quotation_id'];

        $quot_r = $dbcon -> query($quot);

        $quot_row1 = brp_mysqli_fetch_array($quot_r);
        if($quot_row1['total_qt'] == $quot_row1['done_qt']){

            $query="select * from tbl_ledger where l_id=".$POST['cust_id'];
            $rel=mysqli_fetch_assoc($dbcon->query($query));

            $info_quot_row['sales_order_status'] = 1;
            $cust_email = $rel['cust_email'];
                        //po_approve_status
            $info_quot_row['qt_company_name']       = $rel['qt_company_name'];
            $info_quot_row['qt_com_mno']            = $rel['qt_com_mno'];
            $info_quot_row['qt_com_gstno']          = $rel['qt_com_gstno'];
            $info_quot_row['qt_com_addr']           = $rel['qt_com_addr'];
            $info_quot_row['qt_add_country']        = $rel['qt_add_country'];
            $info_quot_row['qt_add_state']          = $rel['qt_add_state'];
            $info_quot_row['qt_add_city']           = $rel['qt_add_city'];
            $info_quot_row['qt_po_no']              = $POST['po_no'];
            $info_quot_row['qt_po_date']            = date('Y-m-d',strtotime($POST['sales_order_date']));
            $info_quot_row['qt_delivery_date']      = date('Y-m-d',strtotime($POST['delivery_date']));
            $info_quot_row['qt_po_amount']          = $POST['g_total'];
            $info_quot_row['qt_po_attch']           = "demo.png";
            $info_quot_row['po_approve_status']     = 1;
            $info_quot_row['sales_order_id']        = $sales_order_id;
        
        }else{
            $info_quot_row['po_approve_status']  = 0;
            $info_quot_row['sales_order_status'] = 0;
        }
        update_record('tbl_quotation', $info_quot_row,"quotation_id=".$row['quotation_id'] , $dbcon);
    }
}
function load_balty($dbcon, $where,$edit_id)
    {
        $query = "select * from solid_balty_mst where balty_status='0'" . $where;
        $rs_product = $dbcon->query($query);

        while ($rsp = brp_mysqli_fetch_assoc($rs_product))
        {
            $sel = "";
            if($rsp['balty_id']==$edit_id){
                $sel = "selected='selected'";
            }
            echo '<option ' . $sel . ' value="' . $rsp['balty_id'] . '">' . $rsp['balty_name'] . '</option>';
        }
    }
function load_size($dbcon, $where,$edit_id)
    {
        $query = "select * from solid_size_mst where size_status='0'" . $where;
        $rs_product = $dbcon->query($query);

        while ($rsp = brp_mysqli_fetch_assoc($rs_product))
        {   
            $sel ="";
            if($rsp['size_id']==$edit_id){
                $sel = "selected='selected'";
            }
            echo '<option ' . $sel . ' value="' . $rsp['size_id'] . '">' . $rsp['size_name'] . '</option>';
        }
    }
function load_batch_size($dbcon, $where,$edit_id)
    {
        $query = "select * from solid_batch_size_mst where batch_size_status='0'" . $where;
        $rs_product = $dbcon->query($query);
        
        while ($rsp = brp_mysqli_fetch_assoc($rs_product))
        {
            $sel="";
            if($rsp['batch_size_id']==$edit_id){
                $sel = "selected='selected'";
            }
    
            echo '<option ' . $sel . ' value="' . $rsp['batch_size_id'] . '">' . $rsp['batch_size_name'] . '</option>';
        }
    }
    function count_mixing_end($dbcon)
    {
        $query = "SELECT IFNULL(sum(req_batch-complate_batch),0) as pending_qty FROM solid_production_planning as so_trn 
        where ( 1 AND so_trn.status=0) ";
        $rs_product = $dbcon->query($query);
        $rsp = brp_mysqli_fetch_assoc($rs_product);
        echo $rsp['pending_qty']; 
    }
    function count_exe_inorder($dbcon)
    {
        $query = "SELECT IFNULL(sum(planning_qty-excluding_allocate_qty),0) as pending_qty FROM solid_production_planning as so_trn left join product_mst as pro on pro.product_id=so_trn.printing_material left join solid_size_mst as bsm on bsm.size_id=so_trn.extrusion_size where ( 1 AND so_trn.status=0) ";
        $rs_product = $dbcon->query($query);
        $rsp = brp_mysqli_fetch_assoc($rs_product);
        echo $rsp['pending_qty']; 
    }
    function count_exe_instock($dbcon)
    {
        $query = "SELECT IFNULL(sum(excluding_allocate_qty-excluding_allocate_approve_qty),0) as pending_allo_qty FROM solid_production_planning as so_trn left join product_mst as pro on pro.product_id=so_trn.printing_material left join solid_size_mst as bsm on bsm.size_id=so_trn.extrusion_size where ( 1 AND so_trn.status=0) ";
        $rs_product = $dbcon->query($query);
        $rsp = brp_mysqli_fetch_assoc($rs_product);
        echo $rsp['pending_allo_qty']; 
    }
    function count_exe_end($dbcon)
    {
        $query = "SELECT IFNULL(sum(excluding_allocate_approve_qty-excluding_complate_qty),0) as pending_end_qty FROM solid_production_planning as so_trn left join product_mst as pro on pro.product_id=so_trn.printing_material left join solid_size_mst as bsm on bsm.size_id=so_trn.extrusion_size where ( 1 AND so_trn.status=0) ";
        $rs_product = $dbcon->query($query);
        $rsp = brp_mysqli_fetch_assoc($rs_product);
        echo $rsp['pending_end_qty']; 
    }
    function count_printing_inorder($dbcon)
    {
        $query = "SELECT IFNULL(sum(planning_qty-printing_allocate_qty),0) as pending_qty FROM solid_production_planning as so_trn left join product_mst as pro on pro.product_id=so_trn.printing_material left join solid_size_mst as bsm on bsm.size_id=so_trn.extrusion_size where ( 1 AND so_trn.status=0) ";
        $rs_product = $dbcon->query($query);
        $rsp = brp_mysqli_fetch_assoc($rs_product);
        echo $rsp['pending_qty']; 
    }
    function count_printing_instock($dbcon)
    {
        $query = "SELECT IFNULL(sum(printing_allocate_qty-printing_allocate_approve_qty),0) as pending_allo_qty FROM solid_production_planning as so_trn left join product_mst as pro on pro.product_id=so_trn.printing_material left join solid_size_mst as bsm on bsm.size_id=so_trn.extrusion_size where ( 1 AND so_trn.status=0)";
        $rs_product = $dbcon->query($query);
        $rsp = brp_mysqli_fetch_assoc($rs_product);
        echo $rsp['pending_allo_qty']; 
    }
    function count_printing_end($dbcon)
    {
        $query = "SELECT IFNULL(sum(printing_allocate_approve_qty-printing_complate_qty),0) as pending_end_qty FROM solid_production_planning as so_trn left join product_mst as pro on pro.product_id=so_trn.printing_material left join solid_size_mst as bsm on bsm.size_id=so_trn.extrusion_size where ( 1 AND so_trn.status=0) ";
        $rs_product = $dbcon->query($query);
        $rsp = brp_mysqli_fetch_assoc($rs_product);
        echo $rsp['pending_end_qty']; 
    }
    function get_batch_printing_allocate($dbcon, $product_id,$edit_id)
    {
         $query = "select i.stock_id,i.batch_no,(base_stock-used_base_stock) as pending_base_stock from tbl_stock_trn as i
        where stock_status!=2 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and product_id = ".$product_id." and batch_no != ''";
        $rs_product = $dbcon->query($query);
        $bno="";
        $bno.='<option>Selecet Batch No</option>';
        while ($rsp = brp_mysqli_fetch_assoc($rs_product))
        {
            $sel="";
            if($rsp['stock_id']==$edit_id){
                $sel = "selected='selected'";
            }
    
            $bno.='<option ' . $sel . ' value="' . $rsp['stock_id'] . '">' . $rsp['batch_no'] . ' ('.$rsp['pending_base_stock'].')</option>';
        }
        return $bno;
    }


function product_type_category_wise_stock($dbcon, $product_type_id, $frmdate, $todate)
{
    // var_dump($product_type_id);
   $query = "SELECT
    IFNULL(pr.plus_opening_stock, 0) AS plus_opening_stock,
    IFNULL(pr1.plus_conv_opening_stock, 0) AS plus_conv_opening_stock,
    IFNULL(pr2.minus_opening_stock, 0) AS minus_opening_stock,
    IFNULL(pr3.minus_conv_opening_stock, 0) AS minus_conv_opening_stock,
    IFNULL(pr4.opening_stock, 0) AS opening_stock,
    IFNULL(pr5.conv_opening_stock, 0) AS conv_opening_stock,
    IFNULL(pr6.closing_stock_plus, 0) AS closing_stock_plus,
    IFNULL(pr7.conv_closing_stock_plus, 0) AS conv_closing_stock_plus,
    IFNULL(pr8.closing_stock_minus, 0) AS closing_stock_minus,
    IFNULL(pr9.conv_closing_stock_minus, 0) AS conv_closing_stock_minus,
    IFNULL(pr.plus_opening_rate, 0) AS plus_opening_rate,
    IFNULL(pr1.plus_conv_opening_rate, 0) AS plus_conv_opening_rate,
    IFNULL(pr2.minus_opening_rate, 0) AS minus_opening_rate,
    IFNULL(pr3.minus_conv_opening_rate, 0) AS minus_conv_opening_rate,
    IFNULL(pr4.opening_rate, 0) AS opening_rate,
    IFNULL(pr5.conv_opening_rate, 0) AS conv_opening_rate,
    IFNULL(pr6.closing_rate_plus, 0) AS closing_rate_plus,
    IFNULL(pr7.conv_closing_rate_plus, 0) AS conv_closing_rate_plus,
    IFNULL(pr8.closing_rate_minus, 0) AS closing_rate_minus,
    IFNULL(pr9.conv_closing_rate_minus, 0) AS conv_closing_rate_minus,
    prd.product_type,
    pt.product_type_name
FROM
    tbl_stock_trn AS stock
LEFT JOIN product_mst AS prd ON prd.product_id = stock.product_id
LEFT JOIN pro_ms_product_type AS pt ON pt.product_type_id = prd.product_type
LEFT JOIN (
    SELECT
        IFNULL(SUM(st.base_stock), 0) AS plus_opening_stock,
        IFNULL(SUM(base_rate), 0) AS plus_opening_rate,
        pr.product_type AS pcat
    FROM
        tbl_stock_trn AS st
    LEFT JOIN product_mst AS pr ON pr.product_id = st.product_id
    WHERE
        st.stock_status != 2
        AND st.ref_name != 'opening_stock'
        AND st.stock_flage = 1
        AND st.stock_date < '" . $frmdate . "'
    GROUP BY
        pr.product_type
) AS pr ON pr.pcat = prd.product_type
LEFT JOIN (
    SELECT
        IFNULL(SUM(stc.convert_stock), 0) AS plus_conv_opening_stock,
        IFNULL(SUM(conv_rate), 0) AS plus_conv_opening_rate,
        pr.product_type AS pcat
    FROM
        tbl_stock_trn AS stc
    LEFT JOIN product_mst AS pr ON pr.product_id = stc.product_id
    WHERE
        stc.stock_status != 2
        AND stc.ref_name != 'opening_stock'
        AND stc.stock_flage = 1
        AND stc.stock_date < '" . $frmdate . "'
    GROUP BY
        pr.product_type
) AS pr1 ON pr1.pcat = prd.product_type
LEFT JOIN (
    SELECT
        IFNULL(SUM(st1.base_stock), 0) AS minus_opening_stock,
        IFNULL(SUM(base_rate), 0) AS minus_opening_rate,
        pr.product_type AS pcat
    FROM
        tbl_stock_trn AS st1
    LEFT JOIN product_mst AS pr ON pr.product_id = st1.product_id
    WHERE
        st1.stock_status != 2
        AND st1.ref_name != 'opening_stock'
        AND st1.stock_flage = 2
        AND st1.stock_date < '" . $frmdate . "'
    GROUP BY
        pr.product_type
) AS pr2 ON pr2.pcat = prd.product_type
LEFT JOIN (
    SELECT
        IFNULL(SUM(stc1.convert_stock), 0) AS minus_conv_opening_stock,
        IFNULL(SUM(conv_rate), 0) AS minus_conv_opening_rate,
        pr.product_type AS pcat
    FROM
        tbl_stock_trn AS stc1
    LEFT JOIN product_mst AS pr ON pr.product_id = stc1.product_id
    WHERE
        stc1.stock_status != 2
        AND stc1.ref_name = 'opening_stock'
        AND stc1.stock_flage = 2
        AND stc1.stock_date < '" . $frmdate . "'
    GROUP BY
        pr.product_type
) AS pr3 ON pr3.pcat = prd.product_type
LEFT JOIN (
    SELECT
        IFNULL(SUM(st2.base_stock), 0) AS opening_stock,
        IFNULL(SUM(base_rate), 0) AS opening_rate,
        pr.product_type AS pcat
    FROM
        tbl_stock_trn AS st2
    LEFT JOIN product_mst AS pr ON pr.product_id = st2.product_id
    WHERE
        st2.stock_status != 2
        AND st2.ref_name = 'opening_stock'
    GROUP BY
        pr.product_type
) AS pr4 ON pr4.pcat = prd.product_type
LEFT JOIN (
    SELECT
        IFNULL(SUM(stc2.convert_stock), 0) AS conv_opening_stock,
        IFNULL(SUM(conv_rate), 0) AS conv_opening_rate,
        pr.product_type AS pcat
    FROM
        tbl_stock_trn AS stc2
    LEFT JOIN product_mst AS pr ON pr.product_id = stc2.product_id
    WHERE
        stc2.stock_status != 2
        AND stc2.ref_name = 'opening_stock'
    GROUP BY
        pr.product_type
) AS pr5 ON pr5.pcat = prd.product_type
LEFT JOIN (
    SELECT
        IFNULL(SUM(st3.base_stock), 0) AS closing_stock_plus,
        IFNULL(SUM(base_rate), 0) AS closing_rate_plus,
        pr.product_type AS pcat
    FROM
        tbl_stock_trn AS st3
    LEFT JOIN product_mst AS pr ON pr.product_id = st3.product_id
    WHERE
        st3.stock_status != 2
        AND st3.stock_date >= '" . $frmdate . "'
        AND st3.stock_date <= '" . $todate . "'
        AND st3.ref_name != 'opening_stock'
        AND st3.stock_flage = 1
    GROUP BY
        pr.product_type
) AS pr6 ON pr6.pcat = prd.product_type
LEFT JOIN (
    SELECT
        IFNULL(SUM(stc3.convert_stock), 0) AS conv_closing_stock_plus,
        IFNULL(SUM(conv_rate), 0) AS conv_closing_rate_plus,
        pr.product_type AS pcat
    FROM
        tbl_stock_trn AS stc3
    LEFT JOIN product_mst AS pr ON pr.product_id = stc3.product_id
    WHERE
        stc3.stock_status != 2
        AND stc3.stock_date >= '" . $frmdate . "'
        AND stc3.stock_date <= '" . $todate . "'
        AND stc3.ref_name != 'opening_stock'
        AND stc3.stock_flage = 1
    GROUP BY
        pr.product_type
) AS pr7 ON pr7.pcat = prd.product_type
LEFT JOIN (
    SELECT
        IFNULL(SUM(st4.base_stock), 0) AS closing_stock_minus,
        IFNULL(SUM(base_rate), 0) AS closing_rate_minus,
        pr.product_type AS pcat
    FROM
        tbl_stock_trn AS st4
    LEFT JOIN product_mst AS pr ON pr.product_id = st4.product_id
    WHERE
        st4.stock_status != 2
        AND st4.stock_date >= '" . $frmdate . "'
        AND st4.stock_date <= '" . $todate . "'
        AND st4.ref_name != 'opening_stock'
        AND st4.stock_flage = 2
    GROUP BY
        pr.product_type
) AS pr8 ON pr8.pcat = prd.product_type
LEFT JOIN (
    SELECT
        IFNULL(SUM(stc4.convert_stock), 0) AS conv_closing_stock_minus,
        IFNULL(SUM(conv_rate), 0) AS conv_closing_rate_minus,
        pr.product_type AS pcat
    FROM
        tbl_stock_trn AS stc4
    LEFT JOIN product_mst AS pr ON pr.product_id = stc4.product_id
    WHERE
        stc4.stock_status != 2
        AND stc4.stock_date >= '" . $frmdate . "'
        AND stc4.stock_date <= '" . $todate . "'
        AND stc4.ref_name != 'opening_stock'
        AND stc4.stock_flage = 2
    GROUP BY
        pr.product_type
) AS pr9 ON pr9.pcat = prd.product_type
WHERE
    stock.stock_status != 2
    AND stock.company_id = " . $_SESSION['company_id'] . " 
    AND prd.product_type = " . $product_type_id . "
GROUP BY
    pt.product_type_id";

    $result = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($result);

    $base_opening_stock = $row['opening_stock'] + $row['plus_opening_stock'] - $row['minus_opening_stock'];
    $conv_opening_stock = $row['conv_opening_stock'] + $row['plus_conv_opening_stock'] - $row['minus_conv_opening_stock'];
    $base_closing_stock = $base_opening_stock + $row['closing_stock_plus'] - $row['closing_stock_minus'];
    $conv_closing_stock = $conv_opening_stock + $row['conv_closing_stock_plus'] - $row['conv_closing_stock_minus'];
    $base_opening_rate = $row['opening_rate'] + $row['plus_opening_rate'] - $row['minus_opening_rate'];
    $conv_opening_rate = $row['conv_opening_rate'] + $row['plus_conv_opening_rate'] - $row['minus_conv_opening_rate'];
    $base_closing_rate = $base_opening_rate + $row['closing_rate_plus'] - $row['closing_rate_minus'];
    $conv_closing_rate = $conv_opening_rate + $row['conv_closing_rate_plus'] - $row['conv_closing_rate_minus'];
    $stock['product_type_name'] = $row['product_type_name'];
    $stock['base_opn_stock'] = $base_opening_stock;
    $stock['conv_opn_stock'] = $conv_opening_stock;
    $stock['base_closing_stock'] = $base_closing_stock;
    $stock['conv_closing_stock'] = $conv_closing_stock;
    $stock['base_opn_rate'] = $base_opening_rate;
    $stock['conv_opn_rate'] = $conv_opening_rate;
    $stock['base_closing_rate'] = $base_closing_rate;
    $stock['conv_closing_rate'] = $conv_closing_rate;

    return $stock;
}


function product_type_wise_pro_stock($dbcon, $product_type, $frmdate, $todate)
{

    $query = "select plus_opening_stock, plus_conv_opening_stock, minus_opening_stock, minus_conv_opening_stock, opening_stock,conv_opening_stock, closing_stock_plus, conv_closing_stock_plus, closing_stock_minus, pro.product_name, bunit.unit_name as baseunit, cunit.unit_name as conv_unit, strn.base_unit, strn.convert_unit, strn.product_id, pro.product_min_stock, pro.product_max_stock,(select IFNULL(st.base_rate,0) from tbl_stock_trn as st where st.stock_status != 2  and st.stock_flage=1 and st.ref_name='tbl_grn_trn' and st.product_id = strn.product_id  order by st.stock_id desc limit 1) as base_rate,(select IFNULL(st.conv_rate,0) from tbl_stock_trn as st where st.stock_status != 2  and st.stock_flage=1 and st.ref_name='tbl_grn_trn' and st.product_id = strn.product_id  order by st.stock_id desc limit 1) as conv_rate  from tbl_stock_trn as strn

     left join (select st.base_rate,st.conv_rate,st.product_id from tbl_stock_trn as st where st.stock_status != 2  and st.stock_flage=1 and st.stock_date<='" . $frmdate . "' and st.ref_name='tbl_grn_trn' order by st.stock_id desc limit 1) as strate on strate.product_id=strn.product_id

    left join (select sum(st.base_stock) as plus_opening_stock,product_id from tbl_stock_trn as st where st.stock_status != 2  and st.stock_flage=1 and st.stock_date<='" . $frmdate . "' and st.ref_name!='opening_stock' group by st.product_id) as st on st.product_id=strn.product_id

    left join (select sum(stc.convert_stock) as plus_conv_opening_stock,stc.product_id from tbl_stock_trn as stc where stc.stock_status != 2 and stc.stock_flage=1 and stc.stock_date<='" . $frmdate . "' and stc.ref_name!='opening_stock' group by stc.product_id ) as stc on stc.product_id=strn.product_id

    left join (select sum(st1.base_stock) as minus_opening_stock,st1.product_id from tbl_stock_trn as st1 where st1.stock_status != 2  and st1.stock_flage=2 and st1.stock_date<='" . $frmdate . "' and st1.ref_name!='opening_stock' group by st1.product_id ) as st1 on st1.product_id=strn.product_id

    left join (select sum(stc1.convert_stock) as minus_conv_opening_stock,product_id from tbl_stock_trn as stc1 where stc1.stock_status != 2 and stc1.stock_flage=2 and stc1.stock_date<='" . $frmdate . "' and stc1.ref_name!='opening_stock' group by stc1.product_id) as stc1 on stc1.product_id=strn.product_id

    left join (select sum(st2.base_stock) as opening_stock,st2.product_id from tbl_stock_trn as st2 where st2.stock_status != 2  and st2.ref_name='opening_stock' group by st2.product_id) as st2 on st2.product_id=strn.product_id

    left join (select sum(stc2.convert_stock) as conv_opening_stock, stc2.product_id from tbl_stock_trn as stc2 where stc2.stock_status != 2 and stc2.ref_name='opening_stock' group by stc2.product_id) as stc2 on stc2.product_id=strn.product_id

    left join (select sum(base_stock) as closing_stock_plus,st3.product_id from tbl_stock_trn as st3 where st3.stock_status != 2  and st3.stock_date>='" . $frmdate . "' and st3.stock_date<='" . $todate . "' and st3.ref_name!='opening_stock' and st3.stock_flage=1 group by st3.product_id) as st3 on st3.product_id=strn.product_id

    left join (select sum(stc3.convert_stock) as conv_closing_stock_plus,stc3.product_id from tbl_stock_trn as stc3 where stc3.stock_status != 2 and stc3.stock_date>='" . $frmdate . "' and stc3.stock_date<='" . $todate . "' and stc3.ref_name!='opening_stock' and stc3.stock_flage=1 group by stc3.product_id) as stc3 on stc3.product_id=strn.product_id

    left join (select sum(base_stock) as closing_stock_minus,st4.product_id from tbl_stock_trn as st4 where st4.stock_status != 2  and st4.stock_date>='" . $frmdate . "' and st4.stock_date<='" . $todate . "' and st4.ref_name!='opening_stock' and st4.stock_flage=2 group by st4.product_id) as st4 on st4.product_id=strn.product_id

    left join (select sum(stc4.convert_stock) as conv_closing_stock_minus,stc4.product_id from tbl_stock_trn as stc4 where stc4.stock_status != 2 and stc4.stock_date>='" . $frmdate . "' and stc4.stock_date<='" . $todate . "' and stc4.ref_name!='opening_stock' and stc4.stock_flage=2 group by stc4.product_id) as stc4 on  stc4.product_id=strn.product_id

    left join product_mst as pro on pro.product_id = strn.product_id
    left join unit_mst as bunit on bunit.unitid = strn.base_unit
    left join unit_mst as cunit on cunit.unitid = strn.convert_unit
    where strn.stock_status != 2 and pro.product_type=" . $product_type . " and strn.company_id=" . $_SESSION['company_id'] . " group by strn.product_id";

    $result = $dbcon->query($query);
    return $result;
}

?>