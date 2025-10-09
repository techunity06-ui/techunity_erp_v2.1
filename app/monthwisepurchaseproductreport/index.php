<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
		
$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
		
if(strtolower($POST['mode']) == "generate_report") {
        $date=get_sdate($POST['date']);
        $set="select * from tbl_company where company_id=".$_SESSION['company_id'];
        $set_head = brp_mysqli_fetch_assoc($dbcon->query($set));		
				
        $str .= '<table  class="display table table-bordered table-striped" id="data_list">
                    <tr id="logo" class="logo" style="display:none">
                                <td colspan="13" style="text-align:center;">
                                        <strong>'.$set_head['company_name'].'</strong>
                                </td>
                        </tr>
                        <tr>
                                <td colspan="7"><strong>Month Wise Purchase Product Report</strong></td>
                                <td colspan="6" style="text-align:right">Date
                                <label>  : <strong>'.date('d/m/Y',strtotime($date['start_date'])).'</strong> To <strong>'.date('d/m/Y',strtotime($date['end_date'])).'</strong></label></td>
                        </tr>
					
                        <tr>
                                <th style="text-align:center">Product Name</th>
                                <th style="text-align:center">April</th>
                                <th style="text-align:center">May</th>
                                <th style="text-align:center">June</th>
                                <th style="text-align:center">July</th>
                                <th style="text-align:center">Aug</th>
                                <th style="text-align:center">Sep</th>
                                <th style="text-align:center">Oct</th>
                                <th style="text-align:center">Nov</th>
                                <th style="text-align:center">Dec</th>
                                <th style="text-align:center">Jan</th>
                                <th style="text-align:center">Feb</th>
                                <th style="text-align:center">Mar</th>
                        </tr>
                    <tbody>';
        $qry = "SELECT product_name,
                sum(case when 4 = MONTH(po_date) then product_qty else 0 end) '4',
                sum(case when 5 = MONTH(po_date) then product_qty else 0 end) '5',
                sum(case when 6 = MONTH(po_date) then product_qty else 0 end) '6',
                sum(case when 7 = MONTH(po_date) then product_qty else 0 end) '7',
                sum(case when 8 = MONTH(po_date) then product_qty else 0 end) '8',
                sum(case when 9 = MONTH(po_date) then product_qty else 0 end) '9',
                sum(case when 10 = MONTH(po_date) then product_qty else 0 end) '10',
                sum(case when 11 = MONTH(po_date) then product_qty else 0 end) '11',
                sum(case when 12 = MONTH(po_date) then product_qty else 0 end) '12',
                sum(case when 1 = MONTH(po_date) then product_qty else 0 end) '1',
                sum(case when 2 = MONTH(po_date) then product_qty else 0 end) '2',
                sum(case when 3 = MONTH(po_date) then product_qty else 0 end) '3',
                trn.product_id 
            FROM `tbl_potrancation` as trn 
            left join product_mst as product on product.product_id=trn.product_id 
            left join tbl_pono as po on po.po_id=trn.po_id 
            where trn.potrancation_status=0 and product_status=0 
                and po.po_date between '".date('Y-m-d',strtotime($date['start_date']))."' and '".date('Y-m-d',strtotime($date['end_date']))."' 
                and po.company_id=".$_SESSION['company_id']." and product.company_id in(".$_SESSION['company_id'].") 
            group by product.product_id";
			  
            $result1 = $dbcon->query($qry);
            $i=1;
            if(mysqli_num_rows($result1)>0){
                $total=0;
                while($re=mysqli_fetch_assoc($result1)){	
                    $str.='<tr>
                            <td data-label="Product Name" style="text-align:center">'.$re['product_name'].'</td>
                            <td data-label="April" style="text-align:center">'.$re['4'].'</td>
                            <td data-label="May" style="text-align:center">'.$re['5'].'</td>
                            <td data-label="June" style="text-align:center">'.$re['6'].'</td>
                            <td data-label="July" style="text-align:center">'.$re['7'].'</td>
                            <td data-label="Aug" style="text-align:center">'.$re['8'].'</td>
                            <td data-label="Sep" style="text-align:center">'.$re['9'].'</td>
                            <td data-label="Oct" style="text-align:center">'.$re['10'].'</td>
                            <td data-label="Nov" style="text-align:center">'.$re['11'].'</td>
                            <td data-label="Dec" style="text-align:center">'.$re['12'].'</td>
                            <td data-label="Jan" style="text-align:center">'.$re['1'].'</td>
                            <td data-label="Feb" style="text-align:center">'.$re['2'].'</td>
                            <td data-label="Mar" style="text-align:center">'.$re['3'].'</td>
                        </tr>';				
                        $i++;
                }
            }
            else {
                $str .='<tr>
                            <td colspan="13" style="text-align:center">NO DATA FOUND  </td>
                        </tr>';
							
            }
            $str .='</tbody>				 
		</table>';
    echo $str;
}
function get_sdate($date)
{
	$sdate['start_date']=date('01-04-'.$date);
	$sdate['end_date']=date('31-03-'.($date+1));
	return $sdate;	
}
?>