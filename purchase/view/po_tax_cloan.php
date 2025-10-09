<?php 
session_start();

include('../include/urlfile.php');	

$form="Purchase Order";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];


$query="select sta.gst_state_code,trn.total,hsn.hsn_code,po.vender_id,led.stateid,trn.purchaseordertrn_id,trn.branch_id,trn.product_id from tbl_purchaseordertrn as trn
left tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
left join tbl_ledger as led on led.l_id=po.vender_id
left join state_mst as sta on sta.stateid=led.stateid
left join product_mst as pmst on pmst.product_id=trn.product_id
left join mst_hsn_code as hsn on hsn.hsn_id=pmst.product_hsn
where trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0";
$result=$dbcon->query($query);
while($rel=brp_mysqli_fetch_assoc($result))
{
        $company_state = get_company_data($dbcon,$_SESSION['company_id']);
                                //$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
        $sale_gst = get_tax_cat_by_hsn($dbcon,$rel['hsn_code']);

        $custLedgerDetails = get_cust_data_arr($dbcon,$rel['vender_id']);

        $cgst_tax_rate=0;
        $sgst_tax_rate=0;
        $igst_tax_rate=0;
        if(($company_state['stateid'] == $rel['stateid']) && ($custLedgerDetails['enable_sez'] == 0)){
                $gst = $sale_gst['tax_gst']/2;
                $cgst_tax_per = $gst;
                $cgst_tax_rate = ($gst*$rel['total'])/100;
                $sgst_tax_per = $gst;
                $sgst_tax_rate = ($gst*$rel['total'])/100;
        }else{
                $igst_tax_per = $sale_gst['tax_gst'];
                $igst_tax_rate = ($sale_gst['tax_gst']*$rel['total'])/100;
        }

        if(isset($POST['currency_enable']) && $POST['currency_enable']==1){
                $curncy_trn['currency_id'] = $POST['currency_id'];
                $curncy_trn['currency_rate'] = $POST['currency_rate'];
        }else{
                $basecurrency = getbasecurrency($dbcon);
                $curncy_trn['currency_id'] = $basecurrency['currencyid'];
                $curncy_trn['currency_rate'] = 1;
        }



        $info1['cgst_tax_per'] = isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
        $info1['cgst_tax_rate'] = isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
        $info1['sgst_tax_per'] = isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
        $info1['sgst_tax_rate'] = isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
        $info1['igst_tax_per'] = isset($igst_tax_per) ? $igst_tax_per : 0 ;
        $info1['igst_tax_rate'] = isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
        $info1['product_tax_cat'] = $sale_gst['tax_cat_id'];

$updateid=update_record("tbl_purchaseordertrn", array_merge($info1,$curncy_trn),"purchaseordertrn_id=".$rel['purchaseordertrn_id'] , $dbcon, $rel['branch_id']);

        if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
                $cl_id = get_ledger_by_name($dbcon,'CGST');
                $insert_tax = add_tax_transaction_record_clo($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$rel['purchaseordertrn_id'],"tbl_purchaseordertrn",$rel['product_id'],3,'',$rel['branch_id']);
        }
        if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
                $cl_id = get_ledger_by_name($dbcon,'SGST');
                $insert_tax = add_tax_transaction_record_clo($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$rel['purchaseordertrn_id'],"tbl_purchaseordertrn",$rel['product_id'],3,'',$rel['branch_id']);
        }
        if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
                $cl_id = get_ledger_by_name($dbcon,'IGST');
                $insert_tax = add_tax_transaction_record_clo($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$rel['purchaseordertrn_id'],"tbl_purchaseordertrn",$rel['product_id'],3,'',$rel['branch_id']);
        }

                                // check for the addiotional tax on product Start -- Maulik

        $count_add_tax=get_check_addition_tax_clo($dbcon,$sale_gst['tax_cat_id'],$rel['total'],$rel['purchaseordertrn_id'],$rel['product_id'],'',$rel['branch_id'],'tbl_purchaseordertrn');

}



function add_tax_transaction_record_clo($dbcon,$ledger_id,$tx_tax_value_per,$tx_taxable_value,$transaction_id,$table_name,$product_id,$tx_status,$edit_id,$branch_id)
{       

        $info1['tx_tax_id'] = $ledger_id;
        $info1['tx_tax_value'] = $tx_tax_value_per;
        $info1['tx_taxable_value'] = $tx_taxable_value;
        $info1['tx_transaction_id'] = $transaction_id;
        $info1['tx_transaction_type'] = $table_name;
        $info1['tx_product_id'] = $product_id;
        $info1['tx_status'] = $tx_status;
        $info1['cdate']  = date("Y-m-d H:i:s");
        $info1['user_id'] = $_SESSION['user_id'];
        $info1['company_id'] = $_SESSION['company_id'];
        $info1['branch_id'] = $branch_id;

         $inserid=add_record("tbl_tax_trn",$info1, $dbcon,$branch_id);
        
}
function get_check_addition_tax_clo($dbcon,$tax_id,$product_amount,$inserid,$product_id,$edit_id,$branch_id,$trn_table)
{
        $qry=$dbcon->query("SELECT * from tbl_tax_category_details where tax_additional='1' and isdelete=0 and tax_cat='$tax_id'");
        if(brp_mysqli_num_rows($qry)>0)
        {
                while($row = brp_mysqli_fetch_assoc($qry)) {
           // $rows[] = $row;
                   $tax_amt = ($product_amount*$row['tax_per'])/100;
                        $insert_tax = add_tax_transaction_record_clo($dbcon,$row['tax_id'],$row['tax_per'],$tax_amt,$inserid,$trn_table,$product_id,3,$edit_id,$branch_id);
        }
        }
        else
        {
                $rows = 0;
        }
        //return $rows;
}
?>
