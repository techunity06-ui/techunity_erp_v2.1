<?php

function quotation_bom_show_print($dbcon,$bom_id,$qty,$num,$call,$space){
	$rr="";
	$query_m="select * from tbl_bom as bom where bom_status=0 and bom_id=".$bom_id;
	$result_m=$dbcon->query($query_m);
	$rel_m=mysqli_fetch_assoc($result_m);
	
	$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
		left join product_mst as pro on pro.product_id=bom_trn.product_id
		left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
		where bom_trn_status=0 and bom_id=".$bom_id;
	$result1=$dbcon->query($query1);
	$k=1;$new_call=$call+1;
	for ($x = 1; $x <= $call; $x++) {
			$space=$space."&nbsp;&nbsp;";
		}
	while($rel1=mysqli_fetch_assoc($result1)){ 
		$new_num=$num.".".$k; 
		
			$base_one_qty=$rel1['product_base_qty']/$rel_m['product_base_qty'];
			$base_qty=$base_one_qty*$qty;
			$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");
		$typ=get_product_type_by_id($dbcon,$rel1['product_type']);
		//$lst=get_last_purchase($dbcon,$rel1['product_id']);
		
		$rr.='<tr>
			<td style="border-right:1px solid; border-bottom:1px solid;">'.$new_num.'</td>
			<td style="border-right:1px solid; border-bottom:1px solid;">'.$rel1["product_name"].'</td>
			<td style="border-right:1px solid; border-bottom:1px solid;" >'.$typ.'</td>
			
			<td style="border-right:1px solid; border-bottom:1px solid;" >
				'.$base_qty.'  '.$rel1["base_unit_name"].'
			</td>
			<td style="border-right:1px solid; border-bottom:1px solid;">'.$rel1["base_unit_name"].'</td>
			<!--<td style="border:1px #444 solid;">'.$lst.'</td>-->
			<td style="border-right:1px solid; border-bottom:1px solid;" >';
				$query="select mst.*,p.process_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id where mst.status = 0 AND mst.product_id=".$rel1['product_id']." order by process_priority";
				$result=$dbcon->query($query);
				$cnt=mysqli_num_rows($result);
				if($cnt>0){ 
					$rr.='<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
						<tr>
							<th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
						</tr>';
					 while($rel=mysqli_fetch_assoc($result)){ 
						if($rel['process_type']==1){
							$process_type="Inhouse";
						}else{
							$process_type="Outside";
						}
					
					
					$rr.='<tr>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel["process_priority"].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$process_type.'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel["process_name"].'</td>
						</tr>';
					 } 
					$rr.='</table>';
				 }
			$rr.='</td>
		</tr>';
		 $rr.=quotation_bom_show_print($dbcon,$rel1['p_bom_id'],$base_qty,$new_num,$new_call,$space);
		$k++;
	}
	return $rr; 
}
//nikunj function start
function getqcrejectedqty($dbcon,$product_id,$purchase_order_trn_id){
	$query="SELECT sum(tqr.qc_rejected) as rejected
	FROM tbl_grn_trn as tgt
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id and tgt.product_id=tpt.product_id
	left JOIN tbl_grn as tg ON tgt.grn_id=tg.grn_id 
	left JOIN tbl_qc as tq ON tq.grn_trn_id=tgt.grn_trn_id 
	left JOIN tbl_qc_trn as tqr ON tqr.qc_id=tq.qc_id 
	where tgt.product_id=".$product_id. " and tgt.purchaseordertrn_id=".$purchase_order_trn_id;
	$result1=mysqli_fetch_assoc($dbcon->query($query));
	if(isset($result1['rejected'])){
		return $result1['rejected'];
	}else{
		return '-';
	}
}

function getqcaccpetedqty($dbcon,$product_id,$purchase_order_trn_id){
	$query="SELECT sum(tqr.qc_accepted) as accepted
	FROM tbl_grn_trn as tgt
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id and tgt.product_id=tpt.product_id
	left JOIN tbl_grn as tg ON tgt.grn_id=tg.grn_id 
	left JOIN tbl_qc as tq ON tq.grn_trn_id=tgt.grn_trn_id 
	left JOIN tbl_qc_trn as tqr ON tqr.qc_id=tq.qc_id 
	where tgt.product_id=".$product_id. " and tgt.purchaseordertrn_id=".$purchase_order_trn_id;
	$result1=mysqli_fetch_assoc($dbcon->query($query));
	if(isset($result1['accepted'])){
		return $result1['accepted'];
	}else{
		return '-';
	}
}
function getgrnno($dbcon,$product_id,$purchase_order_trn_id){
	$query="SELECT tg.grn_no
	FROM tbl_grn_trn as tgt
	left JOIN tbl_grn as tg ON tg.grn_id=tgt.grn_id 
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id and tgt.product_id=tpt.product_id
     where tpt.purchaseordertrn_status!=2 and tgt.product_id=".$product_id. " and tgt.purchaseordertrn_id=".$purchase_order_trn_id;
	$result1=mysqli_fetch_assoc($dbcon->query($query));
	if(isset($result1['grn_no'])){
		return $result1['grn_no'];
	}else{
		return '-';
	}
}
function dateDiffInDays($date1, $date2)  
{ 
	if($date1=='1970/01/01' || $date1=='-'){
		$date1=date("Y/m/d");
	}
	if($date2=='1970/01/01' || $date2=='-'){
		$date2=date("Y/m/d");
	}
	$date1=date_create($date1);
    $date2=date_create($date2);
    $diff=date_diff($date1,$date2);
    $delaydats=$diff->format("%R%a");
	if($delaydats>=0){
    	return 0;
    }else{
    return	str_replace('-','',  $delaydats);
    }
	
} 

function getreceivedqty($dbcon,$product_id,$purchase_order_trn_id){
	 $query="SELECT sum(tgt.product_qty) as product_qty
	FROM tbl_grn_trn as tgt
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id and tgt.product_id=tpt.product_id
     where tpt.purchaseordertrn_status!=2 and tgt.product_id=".$product_id. " and tgt.purchaseordertrn_id=".$purchase_order_trn_id;
	$result1=mysqli_fetch_assoc($dbcon->query($query));
	if(isset($result1['product_qty'])){
		return $result1['product_qty'];
	}else{
		return '-';
	}
}

function getchallanno($dbcon,$product_id,$purchase_order_trn_id,$field){
	$query="SELECT tg.".$field."
	FROM tbl_grn_trn as tgt
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id and tgt.product_id=tpt.product_id
	left JOIN tbl_grn as tg ON tgt.grn_id=tg.grn_id 
	where tpt.purchaseordertrn_status!=2  and tgt.product_id=".$product_id. " and tgt.purchaseordertrn_id=".$purchase_order_trn_id;
	$result1=mysqli_fetch_assoc($dbcon->query($query));
	if(isset($result1[$field])){
		return $result1[$field];
	}else{
		return '-';
	}
}

function processoutside($dbcon,$prdctcategory,$prdctid){
	$query_date = date('d-m-Y');
	$firstday=date('Y-m-01', strtotime($query_date));
	$lastday=date('Y-m-t', strtotime($query_date));
	$query="SELECT  IFNULL(SUM(tsmp.p_qty), 0) as processcount
	FROM tbl_allocate_process as tsmp
	left JOIN product_mst as p ON p.product_id=tsmp.p_product_id where tsmp.pr_process_type=2";
				//if($prdctcategory){
	$query.=' and p.product_category='.$prdctcategory;
				//}
	if($prdctid>0){
		$query.=' and tsmp.p_product_id='.$prdctid;
	}
	$query.=" and tsmp.p_start_time>='".$firstday."' and tsmp.p_start_time<='".$lastday."'";
	$result1=mysqli_fetch_assoc($dbcon->query($query));
	if(isset($result1['processcount'])){
		return $result1['processcount'];
	}else{
		return 0;
	}
}

function processinside($dbcon,$prdctcategory,$prdctid){
	$query_date = date('d-m-Y');
	$firstday=date('Y-m-01', strtotime($query_date));
	$lastday=date('Y-m-t', strtotime($query_date));
	$query="SELECT  IFNULL(SUM(tsmp.p_qty), 0) as processcount
	FROM tbl_allocate_process as tsmp
	left JOIN product_mst as p ON p.product_id=tsmp.p_product_id where tsmp.pr_process_type=1";
				//if($prdctcategory){
	$query.=' and p.product_category='.$prdctcategory;
				//}
	if($prdctid>0){
		$query.=' and tsmp.p_product_id='.$prdctid;
	}
	$query.=" and tsmp.p_start_time>='".$firstday."' and tsmp.p_start_time<='".$lastday."'";
	$result1=mysqli_fetch_assoc($dbcon->query($query));
	if(isset($result1['processcount'])){
		return $result1['processcount'];
	}else{
		return 0;
	}
}

function getqccount($dbcon,$prdctcategory,$prdctid){
	$query_date = date('d-m-Y');
	$firstday=date('Y-m-01', strtotime($query_date));
	$lastday=date('Y-m-t', strtotime($query_date));
	$query="SELECT  IFNULL(SUM(tsmp.qc_product_qty), 0) as qccount
	FROM tbl_qc_trn as tsmp
	left JOIN product_mst as p ON p.product_id=tsmp.product_id";
				//if($prdctcategory){
	$query.=' where p.product_category='.$prdctcategory;
				//}
	if($prdctid>0){
		$query.=' and tsmp.product_id='.$prdctid;
	}
	$query.=" and tsmp.cdate>='".$firstday."' and tsmp.cdate<='".$lastday."'";
	$result1=mysqli_fetch_assoc($dbcon->query($query));
	if(isset($result1['qccount'])){
		return $result1['qccount'];
	}else{
		return 0;
	}

}
function getgrncount($dbcon,$prdctcategory,$prdctid){
	$query_date = date('d-m-Y');
	$firstday=date('Y-m-01', strtotime($query_date));
	$lastday=date('Y-m-t', strtotime($query_date));
	$query="SELECT  IFNULL(SUM(tsmp.product_qty), 0) as grncount
	FROM tbl_grn_trn as tsmp
	left JOIN product_mst as p ON p.product_id=tsmp.product_id";
				//if($prdctcategory){
	$query.=' where p.product_category='.$prdctcategory;
				//}
	if($prdctid>0){
		$query.=' and tsmp.product_id='.$prdctid;
	}
	$query.=" and tsmp.cdate>='".$firstday."' and tsmp.cdate<='".$lastday."'";
	  //  $query.=" group by tsmp.product_id";
	  //exit;
	$result1=mysqli_fetch_assoc($dbcon->query($query));
	if(isset($result1['grncount'])){
		return $result1['grncount'];
	}else{
		return 0;
	}
}
function getpocount($dbcon,$prdctcategory,$prdctid){
	$query_date = date('d-m-Y');
	$firstday=date('Y-m-01', strtotime($query_date));
	$lastday=date('Y-m-t', strtotime($query_date));
	$query="SELECT  group_concat(tp.purchaseorder_id) as poids
	FROM tbl_purchaseorder as tp
	where tp.status=0";
	$query.=" and tp.cdate>='".$firstday."' and tp.cdate<='".$lastday."'";
	  //  $query.=" group by tp.purchaseorder_id";
	  //exit;
	$result1=mysqli_fetch_assoc($dbcon->query($query));
	$query="SELECT  IFNULL(SUM(tpt.product_qty), 0) as pocount
	FROM tbl_purchaseordertrn as tpt
	left JOIN product_mst as p ON p.product_id=tpt.product_id where tpt.purchaseorder_id in (".$result1['poids'].")";
				//if($prdctcategory){
	$query.=' and p.product_category='.$prdctcategory;
				//}
	if($prdctid>0){
		$query.=' and tpt.product_id='.$prdctid;
	}
				//echo $query;
	
	$result1=mysqli_fetch_assoc($dbcon->query($query));

	if(isset($result1['pocount'])){
		return $result1['pocount'];
	}else{
		return 0;
	}

}
function getmrpcount($dbcon,$prdctcategory,$prdctid){
	$query_date = date('d-m-Y');
	$firstday=date('Y-m-01', strtotime($query_date));
	$lastday=date('Y-m-t', strtotime($query_date));
	$query="SELECT  IFNULL(SUM(tsmp.rp_req_qty), 0) as mrpcount
	FROM tbl_set_main_process as tsmp
	left JOIN product_mst as p ON p.product_id=tsmp.product_id";
				//if($prdctcategory){
	$query.=' where p.product_category='.$prdctcategory;
				//}
	if($prdctid>0){
		$query.=' and tsmp.product_id='.$prdctid;
	}
	$query.=" and (tsmp.cdate>='".$firstday."' and tsmp.cdate<='".$lastday."') or ( tsmp.mdate>='".$firstday."' and  tsmp.mdate<='".$lastday."' )";
	  //  $query.=" group by tsmp.product_id";
	  //exit;
	$result1=mysqli_fetch_assoc($dbcon->query($query));

	if(isset($result1['mrpcount'])){
		return $result1['mrpcount'];
	}else{
		return 0;
	}
}

function getfinishedproducts($dbcon,$prdctid){
	$str='';
	$query="Select * from product_mst where product_type=0";
	$rs_type=$dbcon->query($query);
	$str ='<option value="" >--Choose Product--</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$str .= '<option  value="'.$row['product_id'].'">'.$row['product_name'].'</option>';
	}
	return $str;
}

function getproductsbycategory($dbcon,$category){
	$str='';
	$query="Select * from product_mst where product_category=".$category;
	$rs_type=$dbcon->query($query);
	$str ='<option value="" >--Choose Product--</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$str .= '<option  value="'.$row['product_id'].'">'.$row['product_name'].'</option>';
	}
	return $str;
}

function getwoqtybyprdctid($dbcon,$prdctid){
	$query_date = date('d-m-Y');
	$firstday=date('Y-m-01', strtotime($query_date));
	$lastday=date('Y-m-t', strtotime($query_date));
	$query="SELECT  sum(tsmp.rp_req_qty) as wo
	FROM tbl_set_main_process as tsmp
	left JOIN product_mst as p ON p.product_id=tsmp.product_id where tsmp.product_id=".$prdctid;
	$query.=" and (tsmp.cdate>='".$firstday."' and tsmp.cdate<='".$lastday."') or ( tsmp.mdate>='".$firstday."' and  tsmp.mdate<='".$lastday."' )";
	$query.=" group by tsmp.product_id";
	return  $result1=mysqli_fetch_assoc($dbcon->query($query));
}

function vendorwiseformat3($dbcon,$postarr){
	$str='';
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$str .='
	<table  width="100%"   class="display">
	</table>
	<table  class="" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>Item Wise Order Summary</strong>
	</td>
	</tr>';

	$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
	<th width="10%" style="text-align:center">NO.</th>
	<th></th>
	<th width="20%" style="text-align:center">Vendor Name</th>
	<th width="20%" style="text-align:center">City</th>	
	<th width="20%" style="text-align:center">UOM</th>	
	<th width="25%" style="text-align:center">PO Qty</th>
	<th width="25%" style="text-align:center"> Amount</th>
	</tr>';

	$query="SELECT group_concat(distinct(tp.purchaseorder_id)) as poids,cm.city_name,tl.l_name as vendorname,tp.purchaseorder_id,(select IFNULL(sum(tpt.product_amount),0)) as tot,(select IFNULL(sum(tpt.product_qty),0)) as totqty,um.unit_name
	FROM tbl_purchaseorder as tp 
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
	left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id 
	left JOIN city_mst as cm ON cm.cityid=tl.cityid 
	left JOIN product_mst as p ON p.product_id=tpt.product_id
	left JOIN unit_mst as um ON um.unitid=p.product_base_unit
	where tpt.purchaseordertrn_status=0 and tp.status=0 and tp.po_approval_status=1";

	if($POST['po_date_type']){
		if($POST['po_date_type']=='po'){
			$s_date=explode(' - ',$POST['rep_po_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" and tp.purchaseorder_date>='".date('Y-m-d',strtotime($startdate))."' and tp.purchaseorder_date<='".date('Y-m-d',strtotime($enddate))."'";
		}else{
			$s_date=explode(' - ',$POST['rep_del_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" and tp.purchaseorder_due_date>='".date('Y-m-d',strtotime($startdate))."' and tp.purchaseorder_due_date<='".date('Y-m-d',strtotime($enddate))."'";
		}

	}
	if(isset($POST['specific_vendor'])){
		if($POST['vendor_id']){
			$query.=' and tp.vender_id='.$POST['vendor_id'];
		}
	}

	if(isset($POST['specific_item'])){
				if($POST['item_id']){
					$query.=' and tpt.product_id='.$POST['item_id'];
				}
	}


	$query.=" group by tp.vender_id";
	$result_summary=$dbcon->query($query);

	if(mysqli_num_rows($result_summary)>0)
	{
		$j=1;
		while($result_summary_arr=mysqli_fetch_assoc($result_summary))
		{
			$str.='<tr style="  border: 1px dashed #cccccc;">
			<td width="10%" style="text-align:center">'.$j.'</td>
			<td></td>
			<td width="20%"  style="text-align:center">'.$result_summary_arr["vendorname"].'</td>
			<td width="20%" style="text-align:center">'.$result_summary_arr["city_name"].'</td>
			<td width="20%" style="text-align:center">'.$result_summary_arr["unit_name"].'</td>
			<td width="25%" style="text-align:center">'.$result_summary_arr["totqty"].'</td>
			<td width="25%" style="text-align:center">'.$result_summary_arr["tot"].'</td>
			</tr>';
			$j++;
		}
	}else{
		$str .='<tr>
		<td colspan="8" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}
	return $str;
}

function vendorwiseformat2($dbcon,$postarr){
	$str='';
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$str .='
	<table  width="100%"   class="display">
	</table>
	<table  class="" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>Item Wise Order Summary</strong>
	</td>
	</tr>';

	$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
	<th width="10%" style="text-align:center">NO.</th>
	<th></th>
	<th width="20%" style="text-align:center">Vendor Name</th>
	<th width="20%" style="text-align:center">City</th>	
	<th width="25%" style="text-align:center">Net Amount</th>
	<th width="25%" style="text-align:center">Gross Amount</th>
	</tr>';

	$query="SELECT group_concat(distinct(tp.purchaseorder_id)) as poids,cm.city_name,tl.l_name as vendorname,tp.purchaseorder_id,(select IFNULL(sum(tpt.product_amount),0)) as tot,(select IFNULL(sum(tpt.total),0)) as grossamt
	FROM tbl_purchaseorder as tp 
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
	left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id 
	left JOIN city_mst as cm ON cm.cityid=tl.cityid 
	where tpt.purchaseordertrn_status=0 group by tp.vender_id";
	$result_summary=$dbcon->query($query);

	if(mysqli_num_rows($result_summary)>0)
	{
		$j=1;
		while($result_summary_arr=mysqli_fetch_assoc($result_summary))
		{
			$str.='<tr style="  border: 1px dashed #cccccc;">
			<td width="10%" style="text-align:center">'.$j.'</td>
			<td></td>
			<td width="20%"  style="text-align:center">'.$result_summary_arr["vendorname"].'</td>
			<td width="20%" style="text-align:center">'.$result_summary_arr["city_name"].'</td>
			<td width="25%" style="text-align:center">'.$result_summary_arr["tot"].'</td>
			<td width="25%" style="text-align:center">'.$result_summary_arr["grossamt"].'</td>
			</tr>';
			$j++;
		}
	}else{
		$str .='<tr>
		<td colspan="8" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}
	return $str;

}

function itemwiseformat2($dbcon,$postarr){
	$str='';
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$str .='
	<table  width="100%"   class="display">
	</table>
	<table  class="" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>Item Wise Order Summary</strong>
	</td>
	</tr>';

	$query="SELECT sum(tpt.product_qty) as totalqty,um.unit_name,p.product_name,p.product_icode,sum(tpt.product_amount) as totalamt FROM 
	tbl_purchaseordertrn as tpt 
	left JOIN unit_mst as um ON um.unitid=tpt.unit_id
	left JOIN product_mst as p ON p.product_id=tpt.product_id
	group by tpt.product_id";
	$result_summary=$dbcon->query($query);

	if(mysqli_num_rows($result_summary)>0)
	{
		$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
		<th width="5%" style="text-align:center">NO.</th>
		<th width="15%" style="text-align:center">Item Code</th>
		<th width="15%" style="text-align:center">Item Description</th>
		<th width="20%" style="text-align:center">UOM</th>
		<th width="15%" style="text-align:center">PO Qty</th>
		<th width="15%" style="text-align:center">Amount</th>
		</tr>';
		$total=0;
		$j=1;
		while($result_summary_arr=mysqli_fetch_assoc($result_summary))
		{
			$str.='<tr>
			<td style="text-align:center">'.$j.'</td>
			<td style="text-align:center">'.$result_summary_arr["product_icode"].'</td>
			<td style="text-align:center">'.$result_summary_arr["product_name"].'</td>
			<td style="text-align:center">'.$result_summary_arr["unit_name"].'</td>
			<td style="text-align:center">'.$result_summary_arr["totalqty"].'</td>
			<td style="text-align:center">'.$result_summary_arr["totalamt"].'</td>
			</tr>';
			$j++;
		}
	}else{
		$str .='<tr>
		<td colspan="8" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}
	$str.='</table>';
	return $str;

}

function getbilldata($dbcon,$purchase_order_trn_id,$productid){
	$query="SELECT tpot.total as billtotal,tg.challan_no,tpot.product_qty as billqty,tpo.po_no as billno,tpo.po_date as billdate,tp.purchaseorder_no,tpt.unit_id,tpt.product_id,tpt.product_rate,tpt.product_disc,tpt.product_qty,tpt.used_qty,tp.purchaseorder_due_date,(select sum(tbl_grn_trn.product_qty) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as pending
	FROM tbl_pono as tpo
	left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
	left JOIN tbl_grn as tg ON tpot.grn_id=tg.grn_id
	left JOIN tbl_grn_trn as tgt ON  tgt.grn_id=tg.grn_id 
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id
	left JOIN tbl_purchaseorder as tp ON tpt.purchaseorder_id=tp.purchaseorder_id where tpo.status!='2' and tp.vender_id=".$purchase_order_trn_id. " AND tpot.product_id=".$productid.' group by tpot.potrancation_id';
	return	$result1=$dbcon->query($query);
}

function getgrndata($dbcon,$product_id,$purchase_order_trn_id){
	$query="SELECT tg.grn_no,tg.grn_date,tgt.product_qty,tqr.qc_product_qty,tqr.qc_rejected,tq.qc_date,tg.challan_no
	FROM tbl_grn_trn as tgt
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id and tgt.product_id=tpt.product_id
	left JOIN tbl_grn as tg ON tgt.grn_id=tg.grn_id 
	left JOIN tbl_qc as tq ON tq.grn_trn_id=tgt.grn_trn_id 
	left JOIN tbl_qc_trn as tqr ON tqr.qc_id=tq.qc_id 
	where tgt.product_id=".$product_id. " and tgt.purchaseordertrn_id=".$purchase_order_trn_id;
	return	$result1=$dbcon->query($query);
}

function getbillno($dbcon,$product_id,$purchase_order_id){
	$query="SELECT tpo.po_no as billno,tpo.po_date as billdate
	FROM tbl_purchaseordertrn as tpt
	left JOIN tbl_grn_trn as tgt ON tgt.purchaseordertrn_id=tpt.purchaseordertrn_id and tgt.product_id=tpt.product_id
	left JOIN tbl_grn as tg ON tgt.grn_id=tg.grn_id 
	left JOIN tbl_potrancation as tpot ON tpot.grn_id=tg.grn_id
	left JOIN tbl_pono as tpo ON tpo.po_id=tpot.po_id  where tpt.product_id=".$product_id. " and tpt.purchaseorder_id=".$purchase_order_id. "  and tpo.po_no!='' group by tpt.purchaseordertrn_id" ;
	$result1=$dbcon->query($query);
	//print_r($result1);
}

function getallworkorder($dbcon){
	$str='';
	$query="Select * from tbl_set_main_process";
	$rs_type=$dbcon->query($query);
	$str ='<option value="" >--Choose Work Order--</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$sel='';
		$str .= '<option '.$row['sp_id'].' value="'.$row['sp_id'].'">'.$row['po_req_no'].'</option>';
	}
	return $str;
}

function getallsalesorder($dbcon){
	$str='';
	$query="Select * from tbl_sales_order where sales_order_status!=2";
	$rs_type=$dbcon->query($query);
	$str ='<option value="" >--Choose Sales Order--</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$sel='';
		$str .= '<option '.$row['sales_order_id'].' value="'.$row['sales_order_id'].'">'.$row['sales_order_no'].'</option>';
	}
	return $str;
}

function get_all_category_modify($dbcon,$id,$where='')
{
	$str='';
	$query="Select * from tbl_category where cat_status=0 ".$where;
	$rs_type=$dbcon->query($query);
	if($id=='0'){ $psel='selected="selected"';}
	$str ='<option value="" >--Choose Category--</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$sel='';
		if($row['cat_id']==$id)
			{$sel='selected="selected"';}
		
		$str .= '<option '.$sel.' value="'.$row['cat_id'].'">'.$row['cat_name'].'</option>';
	}
	return $str;
}
function getunitname($dbcon,$uid){
	$query="SELECT  * FROM unit_mst as tp
	where tp.unitid=".$uid;
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_assoc($rs_cust);
	return $rel['unit_name'];
}

function get_alter_unit($dbcon,$pid,$uid){
	$query="SELECT  * FROM product_mst as tp
	where tp.product_id=".$pid;
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_assoc($rs_cust);
		//print_r($rel);
	if($uid==$rel['product_base_unit']){
		$unitname=getunitname($dbcon,$rel['product_conv_unit']);

	}else{
		$unitname=getunitname($dbcon,$rel['product_base_unit']);
	}
	return $unitname;
}

function getproducttrn($dbcon,$id){
	$query="SELECT sum(tp.product_qty) as tot,group_concat(tp.purchaseordertrn_id) as trnids FROM tbl_purchaseordertrn as tp
	where tp.purchaseorder_id=".$id;
	$rs_cust=$dbcon->query($query);	
	return	$rel=mysqli_fetch_assoc($rs_cust);
}

function getpendingqtygrn($dbcon,$ids){
	$query="SELECT sum(tg.product_qty) as pending FROM tbl_grn_trn as tg
	where tg.purchaseordertrn_id in(".$ids.")";
	$rs_cust=$dbcon->query($query);	
	return $rel=mysqli_fetch_assoc($rs_cust);
}
function check_product_qty_completed($dbcon,$purchaseordertrn_id,$pr_id){
	$query="SELECT sum(tp.product_qty) as pendingqty,tp.purchaseordertrn_id,tp.product_id,tp.purchaseorder_id FROM tbl_grn_trn as tp where tp.purchaseordertrn_id=".$purchaseordertrn_id." and tp.product_id=".$pr_id;
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_assoc($rs_cust);
	return $rel['pendingqty'];
}

function getAllitemsbyvendorid($dbcon,$venderid){
	$pending_po=array();
	$query="SELECT p.product_name,tpt.product_id,tp.purchaseorder_no,tpt.product_qty,tpt.used_qty,tp.purchaseorder_id
	FROM tbl_purchaseorder as tp
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
	left JOIN product_mst as p ON p.product_id=tpt.product_id
	where tp.vender_id=".$venderid;
	$rs_cust=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		echo '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name'].'</option>';
	}
}

function getAllitemsbyvendoridjobcard($dbcon,$venderid){
	$pending_po=array();
	$query="SELECT p.product_name,tp.product_id
	FROM tbl_purchasecardtrn as tp
	left JOIN product_mst as p ON p.product_id=tp.product_id
	where tp.vendor_id=".$venderid;
	$rs_cust=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		echo '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name'].'</option>';
	}
}

function getAllPendingitems($dbcon,$purchaseorder_id){
	$pending_po=array();
	$query="SELECT tp.product_qty,tp.purchaseordertrn_id,tp.product_id,tp.purchaseorder_id FROM tbl_purchaseordertrn as tp  where tp.purchaseorder_id=".$purchaseorder_id;
	$rs_cust=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$pn_qty=check_product_qty_completed($dbcon,$rel['purchaseorder_id'],$rel['product_id']);
		if($rel['product_qty']<=$pn_qty){

		}else{

			$pending_po[]=$rel;
		}
	}
	$html='';
	$sel='';
	if(count($pending_po)>0){
		
		$html.='<option value="">--Select Item--</option>';
		foreach ($pending_po as $key => $value) {
		//	$html.='qq';
			$html.='<option '.$sel.' value="'.$value['product_id'].'">'.get_pro_field($dbcon,$value['product_id'],'product_name').'</option>';
		}
	}
	echo $html;
}


function checkpopendorcomp($dbcon,$purchaseordderid){
	$tot_prdct_data=getproducttrn($dbcon,$purchaseordderid);
	$pend_prdct_data=getpendingqtygrn($dbcon,$purchaseordderid);
	if($tot_prdct_data['tot']<=$pend_prdct_data['pending']){
		return 1;
	}else{
		return 0;
			//$pending_po[]=$rel;
	}
}
function getAllPO($dbcon,$id){
	$pending_po=array();
	$query="SELECT tp.purchaseorder_no,tpt.product_qty,tpt.used_qty,tp.purchaseorder_id
	FROM tbl_purchaseorder as tp
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
	left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
	where tp.vender_id=".$id." group by tp.purchaseorder_id ";
	$rs_cust=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$tot_prdct_data=getproducttrn($dbcon,$rel['purchaseorder_id']);
		$pend_prdct_data=getpendingqtygrn($dbcon,$tot_prdct_data['trnids']);
		
		if($tot_prdct_data['tot']<=$pend_prdct_data['pending']){
		}else{
			$pending_po[]=$rel;
		}
	}
	$html='';
	$sel='';
	if(count($pending_po)>0){
		$html.='<option value="">--Select PO--</option>';
		foreach ($pending_po as $key => $value) {
			$html.='<option '.$sel.' value="'.$value['purchaseorder_id'].'">'.$value['purchaseorder_no'].'</option>';
		}
	}
	echo $html;
}

function getAllPOs($dbcon,$id){
	$pending_po=array();
	$query="SELECT tp.purchaseorder_no,tp.purchaseorder_id
	FROM tbl_purchaseorder as tp
	where tp.vender_id=".$id." group by tp.purchaseorder_id ";
	$rs_cust=$dbcon->query($query);	
	$html='';
	$sel='';
	$html.='<option value="">--Select PO--</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$html.='<option '.$sel.' value="'.$rel['purchaseorder_id'].'">'.$rel['purchaseorder_no'].'</option>';
	}
	echo $html;
}

function getAllVendor($dbcon,$id) {
	$query="select l_id,l_name from tbl_ledger where l_status=0 and l_form='customer_form'";
	$rs_cust=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		// $sel='';
		if($rel['l_id']==$id) {
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['l_id'].'">'.$rel['l_name'].'</option>';
	}
}


function getpurchasebilldata($dbcon,$po_id,$vender_id,$postdata){
	$response=[];
	  $query="SELECT tpot.total as billtotal,tg.challan_no,tpot.product_id,tpot.unit_id,tpot.product_qty as billqty,tpo.po_no as billno,tpo.po_date as billdate,tp.purchaseorder_no,tpt.unit_id,tpt.product_id,tpot.product_rate,tpot.product_disc,tpt.product_qty,tpt.used_qty,tp.purchaseorder_due_date,tg.grn_no,pm.product_name,bunit.unit_name,tpot.product_amount
	FROM tbl_pono as tpo
	left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
	inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
	left join unit_mst as bunit on bunit.unitid=tpot.unit_id
	left JOIN tbl_grn as tg ON tpot.grn_id=tg.grn_id
	left JOIN tbl_grn_trn as tgt ON  tgt.grn_id=tg.grn_id 
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id
	left JOIN tbl_purchaseorder as tp ON tpt.purchaseorder_id=tp.purchaseorder_id where tpo.status!='2' and tpo.vender_id=".$vender_id. " AND tpot.po_id=".$po_id;

	if($postdata['po_date_type']){
		if($postdata['po_date_type']=='po'){
			$s_date=explode(' - ',$postdata['rep_po_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" and tpot.cdate>='".date('Y-m-d',strtotime($startdate))."' and tpot.cdate<='".date('Y-m-d',strtotime($enddate))."'";
		}else{
			$s_date=explode(' - ',$postdata['rep_del_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" and tpot.cdate>='".date('Y-m-d',strtotime($startdate))."' and tpot.cdate<='".date('Y-m-d',strtotime($enddate))."'";
		}

	}

	if(isset($postdata['specific_vendor'])){
		if($postdata['vendor_id']){
			$query.=' and tpo.vender_id='.$postdata['vendor_id'];
		}
	}
	if(isset($postdata['specific_item'])){
		if($postdata['item_id']){
					$query.=' and tpot.product_id='.$postdata['item_id'];
		}
	}
	if(isset($postdata['purchase_type_status'])){
		if($postdata['purchase_type_id']){
					$query.=' and tpo.purchase_bill_type='.$postdata['purchase_type_id'];
		}
	}
	 $query.=' group by tpot.potrancation_id';
		$result1=$dbcon->query($query);
	while($rel=mysqli_fetch_assoc($result1))
	{	

		array_push($response, $rel);
	}
	return $response;
}

function groupsummaryreport($dbcon,$postdata){
$response=[];
	 $query="SELECT sum(tpot.total) as billtotal,sum(tpot.product_qty) as billqty,bunit.unit_name,tc.cat_name,tc.cat_id
	FROM tbl_pono as tpo
	left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
	inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
	left JOIN tbl_category as tc ON tc.cat_id=pm.product_category 
	left join unit_mst as bunit on bunit.unitid=tpot.unit_id
	where tpo.status!='2'";

	if($postdata['po_date_type']){
		if($postdata['po_date_type']=='po'){
			
			$query.=" and tpo.po_date>='".date('Y-m-d',strtotime($postdata['fromdate']))."' and tpo.po_date<='".date('Y-m-d',strtotime($postdata['todate']))."'";
		}else{
			
			$query.=" and tpo.po_date>='".date('Y-m-d',strtotime($postdata['fromdate']))."' and tpo.po_date<='".date('Y-m-d',strtotime($postdata['todate']))."'";
		}
	}

	if(isset($postdata['specific_vendor'])){
		if($postdata['vendor_id']){
			$query.=' and tpo.vender_id='.$postdata['vendor_id'];
		}
	}
	if(isset($postdata['specific_item'])){
		if($postdata['item_id']){
					$query.=' and tpot.product_id='.$postdata['item_id'];
		}
	}
	if(isset($postdata['purchase_type_status'])){
		if($postdata['purchase_type_id']){
					$query.=' and tpo.purchase_bill_type='.$postdata['purchase_type_id'];
		}
	}
	$query.=" group by tc.cat_id";
	//echo  $query.=' group by tpot.potrancation_id';
	$result1=$dbcon->query($query);
	$str='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
		<th width="9%" style="text-align:center"> NO </th>
		<th width="9%" style="text-align:center">Item Group </th>
		<th width="12%" style="text-align:center">UOM</th>
		<th width="12%" style="text-align:center">Qty</th>
		<th width="12%" style="text-align:center">Purchase Amount</th>
		</tr>';

					
	while($rel=mysqli_fetch_assoc($result1))
	{	
		array_push($response, $rel);
	}
	$j=1;
			if(count($response)>0)
			{
				$tot_amt=0;
				$tot_qty=0;
				$tot_pend_amt=0;
				foreach ($response as $key => $re) {
					$tot_amt=$tot_amt+$re["billtotal"];
					$str.='<tr style="border: 1px dashed #cccccc;">
					<td style="text-align:center">'.$j.'</td>
					<td style="text-align:center">'.$re["cat_name"].'</td>
					<td style="text-align:center">'.$re["unit_name"].'</td>
					<td style="text-align:center">'.$re["billqty"].'</td>
					<td style="text-align:center">'.$re["billtotal"].'</td>
					</tr>';
					$j++;
				}
				$str .='<tr>
				
				
				<td  colspan="4" style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:right;"><strong>Net Amount :  </strong></td>
				<td style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:center;">'.number_format($tot_amt,2).'</td>
				</tr>';
			}else{
				$str .='<tr>
						<td colspan="8" style="text-align:center">NO DATA FOUND  </td>
						</tr>';
			}
		return $str;
	
}

function getpurchasebilldataitemwiese($dbcon,$productid,$postdata){
	$response=[];
	$query="SELECT tpot.total as billtotal,tg.challan_no,tpot.product_id,tpot.unit_id,tpot.product_qty as billqty,tpo.po_no as billno,tpo.po_date as billdate,tp.purchaseorder_no,tpt.unit_id,tpt.product_id,tpot.product_rate,tpot.product_disc,tpt.product_qty,tpt.used_qty,tp.purchaseorder_due_date,tg.grn_no,pm.product_name,bunit.unit_name,tpot.product_amount,tl.l_name
	FROM tbl_pono as tpo
	left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
	left JOIN tbl_ledger as tl ON tl.l_id=tpo.vender_id
	inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
	left join unit_mst as bunit on bunit.unitid=tpot.unit_id
	left JOIN tbl_grn as tg ON tpot.grn_id=tg.grn_id
	left JOIN tbl_grn_trn as tgt ON  tgt.grn_id=tg.grn_id 
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id
	left JOIN tbl_purchaseorder as tp ON tpt.purchaseorder_id=tp.purchaseorder_id where tpo.status!='2' and tpot.product_id=".$productid. "";

	if($postdata['po_date_type']){
		if($postdata['po_date_type']=='po'){
			$s_date=explode(' - ',$postdata['rep_po_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" and tpot.cdate>='".date('Y-m-d',strtotime($startdate))."' and tpot.cdate<='".date('Y-m-d',strtotime($enddate))."'";
		}else{
			$s_date=explode(' - ',$postdata['rep_del_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" and tpot.cdate>='".date('Y-m-d',strtotime($startdate))."' and tpot.cdate<='".date('Y-m-d',strtotime($enddate))."'";
		}

	}

	if(isset($postdata['specific_vendor'])){
		if($postdata['vendor_id']){
			$query.=' and tpo.vender_id='.$postdata['vendor_id'];
		}
	}
	if(isset($postdata['specific_item'])){
		if($postdata['item_id']){
					$query.=' and tpot.product_id='.$postdata['item_id'];
		}
	}
	if(isset($postdata['purchase_type_status'])){
		if($postdata['purchase_type_id']){
					$query.=' and tpo.purchase_bill_type='.$postdata['purchase_type_id'];
		}
	}
	 $query.=' group by tpot.potrancation_id';
		$result1=$dbcon->query($query);
	while($rel=mysqli_fetch_assoc($result1))
	{	

		array_push($response, $rel);
	}
	return $response;
}


function getpgorupdetailreport($dbcon,$typeid='',$catid='',$postdata){
	$str='';
	$response=[];
	 $query="SELECT tpot.total as billtotal,pm.product_type,pm.product_category,tg.challan_no,tpot.product_id,tpot.unit_id,tpot.product_qty as billqty,tpo.po_no as billno,tpo.po_date as billdate,tp.purchaseorder_no,tpt.unit_id,tpt.product_id,tpot.product_rate,tpot.product_disc,tpt.product_qty,tpt.used_qty,tp.purchaseorder_due_date,tg.grn_no,pm.product_name,bunit.unit_name,tpot.product_amount,tl.l_name
	FROM tbl_pono as tpo
	left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
	left JOIN tbl_ledger as tl ON tl.l_id=tpo.vender_id
	inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
	left join unit_mst as bunit on bunit.unitid=tpot.unit_id
	left JOIN tbl_grn as tg ON tpot.grn_id=tg.grn_id
	left JOIN tbl_grn_trn as tgt ON  tgt.grn_id=tg.grn_id 
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id
	left JOIN tbl_purchaseorder as tp ON tpt.purchaseorder_id=tp.purchaseorder_id where tpo.status!='2'";

	if($typeid!=''){
		$query.=" and  pm.product_type=".$typeid;
	}
	if($catid){
		$query.=" and pm.product_category=".$catid;
	}

	if($postdata['po_date_type']){
		if($postdata['po_date_type']=='po'){
			$s_date=explode(' - ',$postdata['rep_po_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" and tpot.cdate>='".date('Y-m-d',strtotime($postdata['fromdate']))."' and tpot.cdate<='".date('Y-m-d',strtotime($postdata['todate']))."'";
		}else{
			$s_date=explode(' - ',$postdata['rep_del_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" and tpot.cdate>='".date('Y-m-d',strtotime($postdata['fromdate']))."' and tpot.cdate<='".date('Y-m-d',strtotime($postdata['todate']))."'";
		}
	}

	
	//echo $query;
	$query.=' group by tpot.potrancation_id';
	$result1=$dbcon->query($query);
	while($rel=mysqli_fetch_assoc($result1))
	{	

		array_push($response, $rel);
	}
	// echo "<pre>";
	// print_r($response);

	$j=1;
			if(count($response)>0)
			{
				$tot_amt=0;
				$tot_qty=0;
				$tot_pend_amt=0;
				foreach ($response as $key => $re) {
					$unit=get_alter_unit($dbcon,$re["product_id"],$re["unit_id"]);
					$convert_unit_stock=convert_stock($dbcon,$re["product_qty"],$re["product_id"],$unit);
					//$pend_conv=convert_stock($dbcon,$pen,$re["product_id"],$unit);
					$tot_amt=$tot_amt+$re["product_amount"];
					$tot_qty=$tot_qty+$re["billqty"];
					$str.='<tr style="border: 1px dashed #cccccc;">
					<td style="text-align:center">'.$j.'</td>
					<td style="text-align:center">'.$re["billno"].'</td>
					<td style="text-align:center">'.$re["l_name"].'</td>
					<td style="text-align:center">'.$re["product_name"].'</td>';

					$str.='<td style="text-align:center">'.$re["unit_name"].'<br>'.$unit.'</td>
					<td style="text-align:center">'.$re["billqty"].'<br>'.$convert_unit_stock.'</td>
					<td style="text-align:center">'.$re["product_rate"].'</td>
					
					<td style="text-align:center">'.$re["product_amount"].'</td>
					</tr>';
					$j++;
				}
				$str .='<tr>
				<td colspan="5" style="text-align:right"><strong>Subtotal : </strong></td>
				<td style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:center;">'.number_format($tot_qty,2).'</td>
				<td  style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:right;"><strong>Net Amount :  </strong></td>
				<td style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:center;">'.number_format($tot_amt,2).'</td>
				</tr>';
			}else{
				$str .='<tr>
						<td colspan="8" style="text-align:center">NO DATA FOUND  </td>
						</tr>';
			}
		return $str;
}

function getpurchasebilldatabillnowise($dbcon,$po_id,$postdata){
	$response=[];
	  $query="SELECT tpot.total as billtotal,tg.challan_no,tpot.product_id,tpot.unit_id,tpot.product_qty as billqty,tpo.po_no as billno,tpo.po_date as billdate,tp.purchaseorder_no,tpt.unit_id,tpt.product_id,tpot.product_rate,tpot.product_disc,tpt.product_qty,tpt.used_qty,tp.purchaseorder_due_date,tg.grn_no,pm.product_name,bunit.unit_name,tpot.product_amount
	FROM tbl_pono as tpo
	left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
	inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
	left join unit_mst as bunit on bunit.unitid=tpot.unit_id
	left JOIN tbl_grn as tg ON tpot.grn_id=tg.grn_id
	left JOIN tbl_grn_trn as tgt ON  tgt.grn_id=tg.grn_id 
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id
	left JOIN tbl_purchaseorder as tp ON tpt.purchaseorder_id=tp.purchaseorder_id where tpo.status!='2'  AND tpo.po_id=".$po_id;

	if(isset($postdata['specific_vendor'])){
		if($postdata['vendor_id']){
			$query.=' and tpo.vender_id='.$postdata['vendor_id'];
		}
	}
	if(isset($postdata['specific_item'])){
		if($postdata['item_id']){
					$query.=' and tpot.product_id='.$postdata['item_id'];
		}
	}
	if(isset($postdata['purchase_type_status'])){
		if($postdata['purchase_type_id']){
					$query.=' and tpo.purchase_bill_type='.$postdata['purchase_type_id'];
		}
	}

	
	 $query.=' group by tpot.potrancation_id';
	//print_r($postdata);
//	echo $query;
		$result1=$dbcon->query($query);
	while($rel=mysqli_fetch_assoc($result1))
	{	

		array_push($response, $rel);
	}
	return $response;
}
//nikunj function end
function add_request_reserve_stock_qc($dbcon,$rp_id,$accept_qty,$unit_id,$product_id,$reject_status){
	if($reject_status=="0"){
		//var_dump($rp_id);
		//var_dump($accept_qty);
		//var_dump($unit_id);
		add_request_reserve_stock($dbcon,$rp_id,$accept_qty,$unit_id);
	}else{
		$set11="select rp.*,qtrn.reject_qty,qtrn.reject_used_qty,qtrn.qc_process_trn_id from tbl_request_product as rp
		left join tbl_qc_process_trn as qtrn on qtrn.p_ref_id=rp.rp_id
		where rp.reject_status=0 and qtrn.reject_qty>0 and reject_qty>reject_used_qty and rp.rp_pid=".$product_id;
			$ser=$dbcon->query($set11);
			while($set_row=mysqli_fetch_assoc($ser)){
				$pending_qty=$set_row['reject_qty']-$set_row['reject_used_qty'];
				if($pending_qty>=$accept_qty){
					//$accept_qty
					add_request_reserve_stock_qc($dbcon,$set_row['rp_id'],$accept_qty,$unit_id,$product_id,$set_row['reject_status']);
					
					$dbcon->query("update tbl_qc_process_trn set reject_used_qty=reject_used_qty+".$accept_qty." where qc_process_trn_id=".$set_row['qc_process_trn_id']);
					
					$accept_qty=$accept_qty-$accept_qty;
				}else{
					//$pending_qty
					add_request_reserve_stock_qc($dbcon,$set_row['rp_id'],$pending_qty,$unit_id,$product_id,$set_row['reject_status']);
					$dbcon->query("update tbl_qc_process_trn set reject_used_qty=reject_used_qty+".$pending_qty." where qc_process_trn_id=".$set_row['qc_process_trn_id']);
					
					$accept_qty=$accept_qty-$pending_qty;
				}
			}
				
	}
}
/*function getrequiredproducttype($dbcon,$id,$where)
{
 $str='';
 $q='';
 if($where)
 {
   $q = $where;
 }
 $query="select p.product_id,p.product_name,p.product_type from product_mst as p
 where p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) ".$q." order by p.product_name";
 
 $rs_dispatch=$dbcon->query($query);
 $rel=mysqli_fetch_assoc($rs_dispatch);
 
 return $rel['product_type'];
}*/
function get_breadcum($dbcon,$bomid,$id1,$id2){
//	echo $id1."gh".$id2;
   $query="SELECT tb.bom_no,tb.bom_id,tbt.bom_id as pbom,pm.product_name FROM tbl_bom as tb 
          inner JOIN product_mst as pm ON pm.product_id=tb.bom_product 
          left JOIN tbl_bomtrn as tbt ON tbt.p_bom_id=tb.bom_id
          where tb.bom_id in($bomid) order by tb.bom_id asc";
	// echo $query="SELECT tb.bom_no,tb.bom_id,pm.product_name FROM tbl_bom as tb inner JOIN product_mst as pm ON pm.product_id=tb.bom_product where tb.bom_id in($bomid) order by tb.bom_id asc";
$result=$dbcon->query($query);
//$rel1=mysqli_fetch_assoc($result);
$st=array();
$bom_id_array=array();
$key=0;
while($rel1=mysqli_fetch_assoc($result)){
	
		$bom_id_array[]=$rel1['bom_id'];
	if(trim($rel1['bom_id'])==''){
		
	}else{
		array_push($st,$rel1['bom_id']);
	}
	if(count($st)>0){
		$rel1['link']=base64_encode(implode(',', $st));
	}
	
	$r[]=$rel1;
	$key++;
}
// echo "<pre>";
// print_r($r);
// exit;
$i=0;
foreach ($r as $key => $r2){	
			if($key==0){
					$href="href=".ROOT."bom_add/";
			}else{
				$href="href=".ROOT."bom_allocate/".$r2['bom_id']."/".$r2['pbom']."/".$r[$i-1]['link'];
			}
			echo '<li><a '.$href.'>
   '.$r2["product_name"].'</a></li>';
   $i++;
	}
}
function bom_show_print($dbcon,$bom_id,$qty,$num,$call,$space){
	$query_m="select * from tbl_bom as bom where bom_status=0 and bom_id=".$bom_id;
	$result_m=$dbcon->query($query_m);
	$rel_m=mysqli_fetch_assoc($result_m);
	
	$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
		left join product_mst as pro on pro.product_id=bom_trn.product_id
		left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
		where bom_trn_status=0 and bom_id=".$bom_id;
	$result1=$dbcon->query($query1);
	$k=1;$new_call=$call+1;
	for ($x = 1; $x <= $call; $x++) {
			$space=$space."&nbsp;&nbsp;";
		}
	while($rel1=mysqli_fetch_assoc($result1)){ 
		$new_num=$num.".".$k; 
		
			$base_one_qty=$rel1['product_base_qty']/$rel_m['product_base_qty'];
			$base_qty=$base_one_qty*$qty;
			$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");
		
		?>
		<tr>
			<!-- <td style="border:0.5px #444 solid;"><?=$space?><?=$new_num?></td> -->
			<td style="border:0.5px #444 solid;"><?=$new_num?></td>
			<td style="border:0.5px #444 solid;"><?=$rel1['product_name']?></td>
			<td style="border:1px #444 solid;" ><?=get_product_type_by_id($dbcon,$rel1['product_type'])?></td>
			
			<td style="border:1px #444 solid;" >
			<?php	$_SESSION['bom_tot']=$_SESSION['bom_tot']+$base_qty; ?>
				<?phpif($rel1['product_base_unit']!=$rel1['product_conv_unit']){ ?>
				<?=$base_qty?>  <?=$rel1['base_unit_name']?><br/>
				<?=$conv_stock?>  <?=$rel1['conv_unit_name']?>
				<?}else{?>
				<?=$base_qty?>  <?=$rel1['base_unit_name']?>
				<?}?>
			</td>
			<td style="border:1px #444 solid;"><?=$rel1['base_unit_name'] ?></td>
			<td style="border:1px #444 solid;"><?=get_last_purchase($dbcon,$rel1['product_id']) ?></td>
			<td style="border:1px #444 solid;" >
				<?$query="select mst.*,p.process_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id where mst.status = 0 AND  mst.product_id=".$rel1['product_id']." order by process_priority";
				$result=$dbcon->query($query);
				$cnt=mysqli_num_rows($result);
				if($cnt>0){ ?>
					<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
						<tr>
							<th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
						</tr>
					<?phpwhile($rel=mysqli_fetch_assoc($result)){ 
						if($rel['process_type']==1){
							$process_type="Inhouse";
						}else{
							$process_type="Outside";
						}
					?>
					
						<tr>
							<td style="border:0.5px #444 solid;text-align:center;" ><?=$rel['process_priority']?></td>
							<td style="border:0.5px #444 solid;text-align:center;" ><?=$process_type?></td>
							<td style="border:0.5px #444 solid;text-align:center;" ><?=$rel['process_name']?></td>
						</tr>
					<?php} ?>
					</table>
				<?php} ?>
			</td>
		</tr>
		<?phpecho bom_show_print($dbcon,$rel1['p_bom_id'],$base_qty,$new_num,$new_call,$space);
		$k++;
	}
}
function count_so_procuct_req($dbcon)
{
	$query="select count(sales_ordertrn_id) as qty from tbl_sales_ordertrn where sales_ordertrn_status=0";
	
	$rs=$dbcon->query($query);
	
	$row=mysqli_fetch_array($rs);
	
	return $row['qty'];
}
function pending_indent_count($dbcon){
	$query="select count(rp_id) as pending_cou from tbl_request_product where indent_status=1";
	 $result=$dbcon->query($query);
	 $row=mysqli_fetch_assoc($result);
	 return $row['pending_cou'];
}
function check_multiplication($dbcon,$product_id,$bomid){
  $query="select tb_t.tot_standrad_qty from tbl_bomtrn as mst 
		inner join tbl_bom as tb on tb.bom_id=mst.bom_id
		left join tbl_bom as tb_t on tb_t.bom_id=mst.p_bom_id
		left join product_mst as product on product.product_id=mst.product_id 
		left join unit_mst as u on u.unitid=mst.product_base_unit
		left join unit_mst as cunit on cunit.unitid=mst.product_conv_unit
		where mst.bom_trn_status=0 and tb.bom_product=".$product_id." order by mst.bom_trn_id asc limit 1";
//		exit;
		
		
$result=$dbcon->query($query);
$rel=mysqli_fetch_assoc($result);
return $rel['tot_standrad_qty'];
}
function get_proudct_multiple_qty($dbcon,$product_id,$bomid){
 $query="select * from tbl_bomtrn where product_id=".$product_id." and bom_id=".$bomid;
 $result=$dbcon->query($query);
 $row=mysqli_fetch_assoc($result);
 return $row['product_base_qty'];
}
function get_dynamic_bom_no($dbcon){
	$query="select * from tbl_invoicetype where status=0 and type_id=5 and company_id=".$_SESSION['company_id'];
		$result=$dbcon->query($query);
		$row=mysqli_fetch_assoc($result);
		 // echo $row['invoicetype_id'];
		 // exit;
		$rows=array();
		 $query1="select * from  tbl_invoicetype where invoicetype_id=".$row['invoicetype_id'];
		$rows=mysqli_fetch_assoc($dbcon->query($query1));
		$id=$rows['taxinvoice_start'];
		$id=$id+1;
		// echo "<pre>";
		// print_r($rows);
		// exit;
		//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
		//$end = $start+1;
		if($rows['invoice_format']=='2')
		{
			return $row['invoiceno'] = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
		}
		else if($rows['invoice_format']=='1')
		{
			return$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
		}
		else if($rows['invoice_format']=='3'){
			 return $row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
		}
		else{
		return	$row['invoiceno'] = str_pad($id,3,"0",STR_PAD_LEFT);
		}
		//return $row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
		//echo json_encode($row);
}
function generate_sitemap_modify($dbcon,$id,$table,$field,$parent,$prev_param_1,$prev_param_2,$param3,$key) {
   $str=base64_decode($param3);
   $str_arr=explode(',',$str);
   array_pop($str_arr);
   $or_str=implode(',',$str_arr);
   $encoded_string=base64_encode($or_str);
   $query_bom = "SELECT * FROM ".$table." WHERE ".$field." = '".$id."'";
   $rsCategoryId = $dbcon->query($query_bom);
   $row_rsCategoryId = mysqli_fetch_assoc($rsCategoryId);
   
   if($key==0){

    if(isset($_SESSION['bom_edit_id'])){
    echo '<li>';
    echo '<a href="'.ROOT."bom_edit/".$_SESSION['bom_edit_id'].'">
  '.get_pro_field($dbcon,$row_rsCategoryId['bom_product'],'product_name').' </a>
  </li>';
    }else{
    echo '<li>';
    echo '<a href="'.ROOT."bom_edit/".$prev_param_1.'">
  '.get_pro_field($dbcon,$row_rsCategoryId['bom_product'],'product_name').' </a>
  </li>';
  //   echo '<li>';
  //   echo '<a href="'.ROOT.'bom_add">'.get_pro_field($dbcon,$row_rsCategoryId['bom_product'],'product_name').' </a>
  // </li>';
    }
   
   
   }else{
echo '<li>';
    echo '<a href="'.ROOT."bom_allocate/".$prev_param_1.'/'.$prev_param_2.'/'.$encoded_string.'">
  '.get_pro_field($dbcon,$row_rsCategoryId['bom_product'],'product_name').' </a>
  </li>';
   }
 
}
function getrequiredproducttype($dbcon,$id,$where)
{
	 $str='';
	 $q='';
	 if($where)
	 {
	   $q = $where;
	 }
	 $query="select p.product_id,p.product_name,p.product_type from product_mst as p
	 where p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) ".$q." order by p.product_name";
	 
	 $rs_dispatch=$dbcon->query($query);
	 $rel=mysqli_fetch_assoc($rs_dispatch);
	 
	 return $rel['product_type'];
}
function start_qty_avalable($dbcon,$process_id,$process_type,$product_id,$p_id){
			 if(!empty($product_id)){
				$ser=" and ap.p_product_id=".$product_id;
			} 
			if(!empty($p_id)){
				$p_id_val=" and ap.p_id=".$p_id;
			}
			$q=$dbcon->query("select ap.*,sum(ap.p_qty) as ap_qty,sum(ap.pen_qty) as apen_qty,p.product_type,p.product_name,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 
			left join product_mst as p on p.product_id=ap.p_product_id 
			left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
			left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id 
			where ap.process_id=".$process_id." ".$ser." and ap.p_status IN(0,1) ".$p_id_val." and pr_process_type='$process_type' group by ap.p_product_id" );
			
			$cnt=1;
			$datacheck="";
			while($rel=mysqli_fetch_array($q))
			{
				$pid=$rel['p_product_id'];
				
				$where='';
				if($rel['p_status']==1){
				$min_machine111=0;
				 $q1=$dbcon->query("select ap.*,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 
				
					left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
					
					left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id 
					
				where ap.process_id=".$process_id." ".$p_id_val." and ap.p_product_id=".$rel['p_product_id']." and ap.p_status=1  and pr_process_type='$process_type'" );
				while($rel_n=mysqli_fetch_array($q1)){
					
					$min_machine=$rel_n['start_qty'];
					$min_machine1111=$rel_n['strtt_qty']-$rel_n['end_qty'];
					$pending_qty1=$rel_n['pen_qty'];
					if($min_machine1111>$pending_qty1){
						$min_machine1111=$pending_qty1;
					}
					$pending_qty=$pending_qty+$pending_qty1;
					$min_machine111=$min_machine111+$min_machine1111;
				}
				
				//var_dump($min_machine111);
			}
			else if($rel['previous_process_id']==0){	
			$pending_qty=0;$min_machine111=0;
				$machine_make_new=array();
				$q1=$dbcon->query("select * from tbl_allocate_process as ap 
				where ap.process_id=".$process_id." and ap.p_product_id=".$rel['p_product_id']." ".$p_id_val." and ap.p_status=0  and pr_process_type='$process_type'" );
				while($rel_n=mysqli_fetch_array($q1)){
					$min_machine1112=0;
					$machine_make=array();
					$q12=$dbcon->query("select * from tbl_request_product as ap 
						where status=0 and perent_id=".$rel_n['p_ref_id'] );
					while($rel_n1=mysqli_fetch_array($q12)){
						
						$o_qty=convert_stock($dbcon,$rel_n1['req_qty_one'],$rel_n1['rp_id'],"base_unit");
						$o_qty=round($o_qty,6);
						$required_qty=$rel_n['p_qty']*$o_qty;
						$required_qty=round($required_qty,4);
						//var_dump($required_qty);
						//$cur_stock=reserve_stock($dbcon,$row['product_id'],$row['product_base_unit']);
						$cur_stock=reserve_stock($dbcon,$rel_n1['rp_pid'],$rel_n1['purchase_unit'],"",$rel_n1['rp_id']);
						//var_dump($cur_stock);
						$total=$cur_stock;
						
						if($total>$required_qty)
						{
							$usable=$required_qty;
						}
						else
						{
								
							//var_dump($total."===".$o_qty);	//$usable=round(($total/$rel_n1['req_qty_one']),0,PHP_ROUND_HALF_DOWN);
							//$usable=round(($total/$o_qty),0,PHP_ROUND_HALF_DOWN);
							$usable=$total/$o_qty;
							//var_dump($total/$rel_n1['req_qty_one']);
							//$usable=$usable*$rel_n1['req_qty_one'];
							$usable=$usable*$o_qty;
							
						}
						//var_dump($usable);
						//var_dump($total);
						//$machine_make[]=round(($usable/$o_qty),0,PHP_ROUND_HALF_DOWN);
						$chkp=$usable/$o_qty;
						
						$machine_make[]=number_format($chkp,4,".","");
						//$machine_make[]=round(($usable/$rel_n1['req_qty_one']),0,PHP_ROUND_HALF_DOWN);
					
						$min_machine=min($machine_make);
						$min_machine1111=$min_machine;
						
						$pending_qty1=$rel_n['pen_qty'];
						//var_dump($pending_qty1);
						if($min_machine1111>$pending_qty1){
							$min_machine1111=$pending_qty1;
						}
						
						if($min_machine1111!=$rel_n['pen_qty']){
							$min_machine1111=floor($min_machine1111);
						}
						//var_dump($min_machine1111);
					}
					$pending_qty=$pending_qty+$rel_n['pen_qty'];
					$min_machine1112=$min_machine1112+$min_machine1111;
					//$machine_make_new[]=$min_machine1111;
					//$min_machine1=min($machine_make_new);
					//$min_machine1112=$min_machine1;
					if($min_machine1112>$pending_qty){
						$min_machine1112=$pending_qty;
					}
					$min_machine111=$min_machine111+$min_machine1112;
					//var_dump($min_machine111);
				}
			}else{
				$min_machine111=0;
				$q1=$dbcon->query("select * from tbl_allocate_process as ap 
				where ap.process_id=".$process_id." ".$p_id_val." and ap.p_product_id=".$rel['p_product_id']." and ap.p_status=0  and pr_process_type='$process_type'" );
				while($rel_n=mysqli_fetch_array($q1)){
					
					 $q22="select * from tbl_allocate_process as bt 
							where bt.p_id=".$rel_n['previous_process_id'];
					$q23=$dbcon->query($q22);
					$row12=mysqli_fetch_array($q23);
					
					$min_machine=$row12['process_stock']-$row12['process_used_stock'];
					//var_dump($min_machine);
					$min_machine1111=$min_machine;
					//$pending_qty11=$min_machine;
					$pending_qty1=$rel_n['pen_qty'];
					if($min_machine1111>$pending_qty1){
						$min_machine1111=$pending_qty1;
					}
					$pending_qty=$pending_qty+$pending_qty1;
					$min_machine111=$min_machine111+$min_machine1111;
				}
				//var_dump($min_machine111);
			}
		}
			
		return $min_machine111;
		//echo "11";
}
/*function update_common_no($dbcon,$type_id){
	$quer="select invoicetype_id from  tbl_invoicetype where status=0 and type_id=".$type_id." and company_id=".$_SESSION['company_id'];
	$ro=mysqli_fetch_assoc($dbcon->query($quer));
	
	$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$ro['invoicetype_id']);
}
function load_common_no($dbcon,$type_id){
	$row=array();
			$quer="select invoicetype_id from  tbl_invoicetype where status=0 and type_id=".$type_id." and company_id=".$_SESSION['company_id'];
			$ro=mysqli_fetch_assoc($dbcon->query($quer));
			
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$ro['invoicetype_id'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$invoiceno = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$invoiceno = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$invoiceno = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$invoiceno = str_pad($id,3,"0",STR_PAD_LEFT);
			}
			//$row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			//echo json_encode($row);
		return $invoiceno;
}*/

function bom_show($dbcon,$bom_id,$qty,$num,$call,$space){
	$query_m="select * from tbl_bom as bom where bom_status=0 and bom_id=".$bom_id;
	$result_m=$dbcon->query($query_m);
	$rel_m=mysqli_fetch_assoc($result_m);
	
	$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
		left join product_mst as pro on pro.product_id=bom_trn.product_id
		left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
		where bom_trn_status=0 and bom_id=".$bom_id;
	$result1=$dbcon->query($query1);
	$k=1;$new_call=$call+1;
	for ($x = 1; $x <= $call; $x++) {
			$space=$space."&nbsp;&nbsp;";
		}
	while($rel1=mysqli_fetch_assoc($result1)){ 
		$new_num=$num.".".$k; 
		
			$base_one_qty=$rel1['product_base_qty']/$rel_m['product_base_qty'];
			$base_qty=$base_one_qty*$qty;
			$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");
		
		?>
		<tr>
			<td style="border:0.5px #444 solid;"><?=$space?><?=$new_num?></td>
			<td style="border:1px #444 solid;" ><?=get_product_type_by_id($dbcon,$rel1['product_type'])?></td>
			<td style="border:0.5px #444 solid;"><?=$rel1['product_name']?></td>
			<td style="border:1px #444 solid;" >
				<?phpif($rel1['product_base_unit']!=$rel1['product_conv_unit']){ ?>
				<?=$base_qty?>  <?=$rel1['base_unit_name']?><br/>
				<?=$conv_stock?>  <?=$rel1['conv_unit_name']?>
				<?}else{?>
				<?=$base_qty?>  <?=$rel1['base_unit_name']?>
				<?}?>
			</td>
			<td style="border:1px #444 solid;" >
				<?$query="select mst.*,p.process_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id where mst.status = 0 AND  mst.product_id=".$rel1['product_id']." order by process_priority";
				$result=$dbcon->query($query);
				$cnt=mysqli_num_rows($result);
				if($cnt>0){ ?>
					<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
						<tr>
							<th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
						</tr>
					<?phpwhile($rel=mysqli_fetch_assoc($result)){ 
						if($rel['process_type']==1){
							$process_type="Inhouse";
						}else{
							$process_type="Outside";
						}
					?>
					
						<tr>
							<td style="border:0.5px #444 solid;text-align:center;" ><?=$rel['process_priority']?></td>
							<td style="border:0.5px #444 solid;text-align:center;" ><?=$process_type?></td>
							<td style="border:0.5px #444 solid;text-align:center;" ><?=$rel['process_name']?></td>
						</tr>
					<?php} ?>
					</table>
				<?php} ?>
			</td>
		</tr>
		<?phpecho bom_show($dbcon,$rel1['p_bom_id'],$base_qty,$new_num,$new_call,$space);
		$k++;
	}
}
function min_max_bom_show($dbcon,$bom_id,$qty,$num,$call,$space,$sp_id,$perent_id){
	$query_m="select * from tbl_bom as bom where bom_status=0 and bom_id=".$bom_id;
	$result_m=$dbcon->query($query_m);
	$rel_m=mysqli_fetch_assoc($result_m);
	
	$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
		left join product_mst as pro on pro.product_id=bom_trn.product_id
		left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
		where bom_trn_status=0 and bom_id=".$bom_id;
	$result1=$dbcon->query($query1);
	$k=1;$new_call=$call+1;
	for ($x = 1; $x <= $call; $x++) {
			//$space=$space."&nbsp;&nbsp;";
		}
	while($rel1=mysqli_fetch_assoc($result1)){ 
		$new_num=$num.".".$k; 
		
			$base_one_qty=$rel1['product_base_qty']/$rel_m['product_base_qty'];
			$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");
			$base_qty=$base_one_qty*$qty;
			$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");
			/* 
			//if($rel1['product_base_unit']!=$rel1['product_conv_unit']){ 
				$base_qty  $rel1['base_unit_name']
				$conv_stock $rel1['conv_unit_name']
			//}else{
				$base_qty $rel1['base_unit_name']
			}  */
			
			$info_sub['sp_id']				= $sp_id;
			$info_sub['sr_no']				= $new_num;
			$info_sub['rp_pid']				= $rel1['product_id'];//product_id
			$info_sub['rp_req_qty']			= $conv_stock;//required qty
			$info_sub['req_qty_one']		= $conv_one_qty;//required qty
			$info_sub['rp_po_qty']			= "";//po qty
			$info_sub['in_process_qty']		= "";//process qty
			$info_sub['rp_req_type']		= "min_max";//type
			$info_sub['process_unit']		= $rel1['product_base_unit'];
			$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
			$info_sub['perent_id']			= $perent_id;
			$info_sub['status']				= 3;
			//$info_sub['main_request']		= $POST['g_total'];
			
			$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon);
		
		min_max_bom_show($dbcon,$rel1['p_bom_id'],$base_qty,$new_num,$new_call,$space,$sp_id,$inserid_sub);
		$k++;
	}
}
function update_common_no($dbcon,$type_id){
	$quer="select invoicetype_id from  tbl_invoicetype where status=0 and type_id=".$type_id." and company_id=".$_SESSION['company_id'];
	$ro=mysqli_fetch_assoc($dbcon->query($quer));
	
	$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$ro['invoicetype_id']);
}
function load_common_no($dbcon,$type_id){
	$row=array();
			$quer="select invoicetype_id from  tbl_invoicetype where status=0 and type_id=".$type_id." and company_id=".$_SESSION['company_id'];
			$ro=mysqli_fetch_assoc($dbcon->query($quer));
			
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$ro['invoicetype_id'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$invoiceno = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$invoiceno = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$invoiceno = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$invoiceno = str_pad($id,3,"0",STR_PAD_LEFT);
			}
			//$row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			//echo json_encode($row);
		return $invoiceno;
}
function get_user_report($dbcon,$user_id){
	$str=''; $sel='';
    $query="select user_id,user_name from users where user_id in (".$user_id.")";
	$rs_dispatch=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$str.= '<option '.$sel.' value="'.$rel['user_id'].'">'.$rel['user_name'].'</option>';
	}
	return $str;
}
function get_year(){
	$minyear= 2018;
	$maxyear=(date('m')<'04') ? date('Y',strtotime('-1 year')) : date('Y');
	 $str="";
	for($y=$minyear;$y<=$maxyear;$y++)
	{
		$sel='';
		if($maxyear==$y)
		{
			$sel='selected="selected"';
		}
		$ny=$y+1;
		$str.="<option ".$sel." value='".$y."'> ".$y."-".$ny."</option>";	
	
	} 
	return $str; 
}
function check_user_chein($dbcon,$user_id,$status){
	$query="select user_id,user_name from users where active=0 and user_id=".$user_id;
	$rs_dispatch=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		//$sel[]=$user_id;
		if($status==1){
			//$sel=" and find_in_set(".$user_id.",task.assign_user_ids) ";
			$sel= $user_id;
		}else{
			//$sel .=" or find_in_set(".$user_id.",task.assign_user_ids) ";
			$sel= ",".$user_id;
		}
		
		$query1="select user_id,user_name from users where active=0 and report_to_user_id=".$user_id;
		$rs_dispatch1=$dbcon->query($query1);
		$pending_count=mysqli_num_rows($rs);		
		while($rel1=mysqli_fetch_assoc($rs_dispatch1))
		{
			if(!empty($rel1['user_id'])){
				$sel.=check_user_chein($dbcon,$rel1['user_id'],0);
			}
		}
	}
	return $sel;
}
function get_assign_users_inq($dbcon,$sid)
{
	 $qry="select * from users where active=0 and user_type!=1";
	$rs_state=$dbcon->query($qry);	
	$str='';
        $str .="<option value=''>Choose Assigned User</option>";
	$e_id=explode(",",$sid);
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		// $sel='';
		//if($row['user_id']==$sid)
		if(in_array($row['user_id'],$e_id))
		{$sel='selected="selected"';} else {$sel="";}
		$str.= '<option '.$sel.' value="'.$row['user_id'].'">'.$row['user_name'].'</option>';
	}
	echo $str;
}
function load_series_no($dbcon,$type_id){
	//Load no by Type ID
	$row=array();
	$query1="select * from tbl_invoicetype where status=0 and type_id=".$type_id." and company_id=".$_SESSION['company_id'];
	$rows=mysqli_fetch_assoc($dbcon->query($query1));
	$id=$rows['taxinvoice_start'];
	$id=$id+1;
	if($rows['invoice_format']=='2'){
		$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
	}
	else if($rows['invoice_format']=='1'){
		$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
	}
	else if($rows['invoice_format']=='3'){
		$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
	}
	else{
		$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
	}
	return $row['invoiceno'];
}
function find_qc_process_ids($dbcon,$grn_trn_id){
	$process_id=array();
	$query="select jobwork_id from tbl_grn_sub_trn as trn 
			where status=0 and grn_trn_id=".$grn_trn_id;
	$result=$dbcon->query($query);
	while($row=mysqli_fetch_assoc($result)){
		$query1="select p_id from tbl_jobwork_process as trn 
				where status=0 and jobwork_id=".$row['jobwork_id'];
		$result1=$dbcon->query($query1);
		while($rel=mysqli_fetch_assoc($result1)){
			array_push($process_id,$rel['p_id']);
		}
	}
	$uniq_ids=array_unique($process_id);
	$str=implode(",",$uniq_ids);
	$asing_user=explode(",",$str);
	$str=array_unique($asing_user);
	$str=implode(",",$str);
	return $str;
	//return $grn_trn_id;
	
}
function find_jobwork_id($dbcon,$product_id,$process_type,$process_id){
	$query13="select group_concat(trn.jobwork_id ORDER BY trn.jobwork_id ASC) AS job_id from tbl_jobwork as trn 
			where status=0 and j_qty>used_qty and j_pr_process_id=".$process_id." and j_process_type=".$process_type." and j_product_id=".$product_id;
	$rel3=mysqli_fetch_assoc($dbcon->query($query13));
	
	return $rel3['job_id'];
}
function show_user_ids($dbcon,$assing_user_id){
	$cou=count($assing_user_id);
	$row=array();
	for($i=0;$i<$cou;$i++)
	{
		$asing=check_crm_find_in_set_new($dbcon,$assing_user_id[$i],1);
		//$asing=check_crm_find_in_set($dbcon,$assing_user_id[$i],1);
		array_push($row,$asing);
	}
	$uniq_ids=array_unique($row);
	$str=implode(",",$uniq_ids);
	$asing_user=explode(",",$str);
	$str=array_unique($asing_user);
	$str=implode(",",$str);
	return $str;
}
function check_crm_find_in_set_new($dbcon,$user_id,$status){
	$query="select user_id,user_name,report_to_user_id from users where user_id=".$user_id;
	$rs_dispatch=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		//$sel[]=$user_id;
		if($status==1){
			//$sel=" and find_in_set(".$user_id.",task.assign_user_ids) ";
			$sel= $user_id;
		}else{
			//$sel .=" or find_in_set(".$user_id.",task.assign_user_ids) ";
			$sel= ",".$user_id;
		}
		
		$query1="select user_id,user_name from users where user_id=".$rel['report_to_user_id'];
		$rs_dispatch1=$dbcon->query($query1);
		$pending_count=mysqli_num_rows($rs);		
		while($rel1=mysqli_fetch_assoc($rs_dispatch1))
		{
			if(!empty($rel1['user_id'])){
				$sel.=check_crm_find_in_set_new($dbcon,$rel1['user_id'],0);
			}
		}
	}
	return $sel;
}
function check_crm_find_in_set($dbcon,$user_id,$status){
	$query="select user_id,user_name from users where user_id=".$user_id;
	$rs_dispatch=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		//$sel[]=$user_id;
		if($status==1){
			//$sel=" and find_in_set(".$user_id.",task.assign_user_ids) ";
			$sel= $user_id;
		}else{
			//$sel .=" or find_in_set(".$user_id.",task.assign_user_ids) ";
			$sel= ",".$user_id;
		}
		
		$query1="select user_id,user_name from users where report_to_user_id=".$user_id;
		$rs_dispatch1=$dbcon->query($query1);
		$pending_count=mysqli_num_rows($rs);		
		while($rel1=mysqli_fetch_assoc($rs_dispatch1))
		{
			if(!empty($rel1['user_id'])){
				$sel.=check_crm_find_in_set($dbcon,$rel1['user_id'],0);
			}
		}
	}
	return $sel;
}
function get_tree_user($dbcon,$user_id,$sel1){
	$query="select us.user_id,us.user_name,rus.user_name as pname from users as us
			left join users as rus on rus.user_id=us.report_to_user_id
		where us.user_id=".$user_id;
	$rs_dispatch=$dbcon->query($query);
$str="";	
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel=''; 
		if($rel['user_id']==$sel1)
		{$sel ="selected='selected'";}
		$str.= '<option '.$sel.' value="'.$rel['user_id'].'">'.$rel['user_name'].' - '.$rel['pname'].'</option>';
		
		$query1="select user_id,user_name from users where report_to_user_id=".$user_id;
		$rs_dispatch1=$dbcon->query($query1);	
		while($rel1=mysqli_fetch_assoc($rs_dispatch1))
		{
			if(!empty($rel1['user_id'])){
				$str.=get_tree_user($dbcon,$rel1['user_id'],$sel1);
			}
		}
	}
	return $str;
}
function total_print_tax($dbcon,$ledger,$invoice_id){
	$query="select * from tbl_invoicetrn as pro where trancation_status=0 and invoice_id=".$invoice_id." and company_id = $_SESSION[company_id]";
	$rs_dispatch=$dbcon->query($query);
	$tax=0;
	while($rel=mysqli_fetch_assoc($rs_dispatch)){
		
		$query11="select * from tbl_used_tax as pro where tax_used_status=0 and used_transaction_id=".$rel['trancation_id']." and table_name='tbl_invoicetrn' and ledger_id=".$ledger." and company_id = $_SESSION[company_id]";
		$rs_dispatch11=$dbcon->query($query11);
		$rel112=mysqli_fetch_assoc($rs_dispatch11);
		
		$tax=$tax+$rel112['tax_amount'];
	}
	return $tax;
}
function print_tax($dbcon,$ledger,$trancation_id,$type){
	
	$query11="select * from tbl_used_tax as pro where tax_used_status=0 and used_transaction_id=".$trancation_id." and table_name='tbl_invoicetrn' and ledger_id=".$ledger." and company_id = $_SESSION[company_id]";
	$rs_dispatch11=$dbcon->query($query11);
	$rel112=mysqli_fetch_assoc($rs_dispatch11);
	
	if($type=="per"){
		return $rel112['tax_per'];
	}else{
		return $rel112['tax_amount'];
	}
	//return $query11;
}
function count_stock_procuct_req($dbcon)
{
	$query='select 
		pro.product_id,pro.product_base_unit,pro.product_name,pro.product_status,pro.product_min_stock, pro.product_opening, reqqty,(IFNULL(((IFNULL((select IFNULL(sum(qc.base_stock),0) as base_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id='.$_SESSION['company_id'].' 
				group by qc.product_id),0)+IFNULL((select IFNULL(sum(qc.convert_stock),0) as con_stock_add from tbl_stock_trn as qc 
				where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id='.$_SESSION['company_id'].' 
				group by qc.product_id),0))-(IFNULL((select IFNULL(sum(qc.base_stock),0) as base_stock_minus from tbl_stock_trn as qc 
				where qc.stock_status!=2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id='.$_SESSION['company_id'].' 
				group by qc.product_id),0)+IFNULL((select IFNULL(sum(qc.convert_stock),0) as con_stock_minus from tbl_stock_trn as qc 
				where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id='.$_SESSION['company_id'].' 
				group by qc.product_id),0))),0)+IFNULL(reqqty,0))-(((IFNULL((select sum(base_stock) as base_addqty from tbl_reserve_stock where stock_status!=2 and stock_flage=1 and base_unit=pro.product_base_unit and product_id=pro.product_id group by product_id),0)+IFNULL((select sum(convert_stock) as conv_addqty from tbl_reserve_stock where stock_status!=2 and base_unit!=convert_unit and stock_flage=1 and convert_unit=pro.product_base_unit and product_id=pro.product_id group by product_id),0))-(IFNULL((select sum(base_stock) as base_usedqty from tbl_reserve_stock where stock_status!=2 and stock_flage=2 and base_unit=pro.product_base_unit and product_id=pro.product_id group by product_id),0)+IFNULL((select sum(convert_stock) as conv_usedqty from tbl_reserve_stock where stock_status!=2 and base_unit!=convert_unit and stock_flage=2 and convert_unit=pro.product_base_unit and product_id=pro.product_id group by product_id),0)))+(IFNULL((select sum(s_qty) as base_addqty1 from tbl_complain_spare_part as com
				left join tbl_complaint as c on c.complaint_id=com.s_comp_id
				where c.complaint_status=0 and sp_sent_status="yes" and s_inv_status=0 and s_product=pro.product_id group by s_product),0))) as stock_in_new
	from product_mst as pro 
	left join (select sum(req.rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0  group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id
	where pro.product_status=0 having stock_in_new < 0';
	
	$rs=$dbcon->query($query);
	$pending_count=mysqli_num_rows($rs);
	//$row=mysqli_fetch_array($rs);
	
	return $pending_count;
}
function get_part_invoice_not_done_send($dbcon,$product_id){
	$query='select sum(s_qty) as base_addqty1 from tbl_complain_spare_part as com
				left join tbl_complaint as c on c.complaint_id=com.s_comp_id
				where c.complaint_status=0 and sp_sent_status="yes" and s_inv_status=0 and s_product='.$product_id.' group by s_product';
	$result=$dbcon->query($query);
	$row=mysqli_fetch_assoc($result);
	return $row['base_addqty1'];
}
function service_reserve_stock($dbcon,$product_id,$type,$ref_id,$qty){
	$query="select * from product_mst where product_id=".$product_id;
	$result=$dbcon->query($query);
	$row=mysqli_fetch_assoc($result);
				$conv_stock=convert_stock($dbcon,$qty,$product_id,"conv_unit");
				$info['reserve_date']		=date('Y-m-d');
				$info['product_id']			=$product_id;
				$info['base_unit']			=$row['product_base_unit'];
				$info['base_stock']			=$qty;
				$info['convert_unit']		=$row['product_conv_unit'];
				$info['convert_stock']		=$conv_stock;
				$info['stock_flage']		=$type;
				$info['request_id']			=$ref_id;
				$info['ref_name']			="service";
				
				$info['cdate']				=date('Y-m-d H:i:s');
				$info['user_id']			=$_SESSION['user_id'];
				$info['company_id']			=$_SESSION['company_id'];
				$inserid=add_record('tbl_reserve_stock', $info, $dbcon);
}
function find_request_id($dbcon,$perent_id,$product_id){
	$query="select * from tbl_request_product as res
	where status=0 and perent_id=".$perent_id." and rp_pid=".$product_id;
	$result=$dbcon->query($query);
	$row=mysqli_fetch_assoc($result);
	return $row['rp_id'];
}
function deduct_remove_stock($dbcon,$request_id,$stock_qty,$unit_id){
	$query="select res.*,pro.product_base_unit,pro.product_conv_unit from tbl_reserve_stock as res
	left join product_mst as pro on pro.product_id=res.product_id
	where stock_status!=2 and ref_name='request' and stock_flage=1 and request_id =".$request_id;
	$result=$dbcon->query($query);
	while($row=mysqli_fetch_assoc($result)){
		//request_id
		$product_id=$row['product_id'];
		$stock=reserve_stock($dbcon,$product_id,$unit_id,$row['reserve_id']);
	if($stock_qty!="")
	{
		if($stock_qty!=0)
		{
			if($stock_qty>=$stock)
			{
				if($row['product_conv_unit']==$unit_id){
					$type="base_unit";
					$con_stock=$stock;
					$base_stock=convert_stock($dbcon,$stock,$product_id,$type);
				}else{
					$type="conv_unit";
					$base_stock=$stock;
					$con_stock=convert_stock($dbcon,$stock,$product_id,$type);
				}
				$info['reserve_date']		=date('Y-m-d');
				$info['product_id']			=$product_id;
				$info['base_unit']			=$row['product_base_unit'];
				$info['base_stock']			=$base_stock;
				$info['convert_unit']		=$row['product_conv_unit'];
				$info['godown_id']			=$row['godown_id'];
				$info['convert_stock']		=$con_stock;
				$info['stock_flage']		=2;
				$info['request_id']			=$row['request_id'];
				$info['ref_name']			="request";
				$info['ref_id']				=$row['reserve_id'];
				
				$info['cdate']				=date('Y-m-d H:i:s');
				$info['user_id']			=$_SESSION['user_id'];
				$info['company_id']			=$_SESSION['company_id'];
				$inserid=add_record('tbl_reserve_stock', $info, $dbcon);
				$stock_qty=$stock_qty-$stock;
				$q=$dbcon->query("update tbl_reserve_stock set stock_status='1' where reserve_id=".$row['reserve_id']);
			}else{
				if($row['product_conv_unit']==$unit_id){
					$type="base_unit";
					$con_stock=$stock_qty;
					$base_stock=convert_stock($dbcon,$stock_qty,$product_id,$type);
				}else{
					$type="conv_unit";
					$base_stock=$stock_qty;
					$con_stock=convert_stock($dbcon,$stock_qty,$product_id,$type);
				}
				$info['reserve_date']		=date('Y-m-d');
				$info['product_id']			=$product_id;
				$info['base_unit']			=$row['product_base_unit'];
				$info['base_stock']			=$base_stock;
				$info['convert_unit']		=$row['product_conv_unit'];
				$info['godown_id']			=$row['godown_id'];
				$info['convert_stock']		=$con_stock;
				$info['stock_flage']		=2;
				$info['request_id']			=$row['request_id'];
				$info['ref_name']			="request";
				$info['ref_id']				=$row['reserve_id'];
				
				$info['cdate']				=date('Y-m-d H:i:s');
				$info['user_id']			=$_SESSION['user_id'];
				$info['company_id']			=$_SESSION['company_id'];
				$inserid=add_record('tbl_reserve_stock', $info, $dbcon);
				
				if($row['product_conv_unit']==$unit_id){
					//$con_stock=$stock_qty;
					$stock_qty=$stock_qty-$con_stock;
				}else{
					//$base_stock=$stock_qty;
					$stock_qty=$stock_qty-$base_stock;
				}
			}
		}
	}
		
	}
}
function reserve_stock($dbcon,$product_id,$unit_id,$reserve_id,$request_id){
	
	if(!empty($reserve_id)){
		$rwhser=" and reserve_id=".$reserve_id;
	}
	if(!empty($request_id)){
		$rwhser1=" and request_id=".$request_id;
	}
	
	$query1="select sum(base_stock) as base_addqty from tbl_reserve_stock where stock_status !=2 and stock_flage=1 and base_unit=".$unit_id." ".$rwhser." ".$rwhser1." and product_id=".$product_id;
	$result1=$dbcon->query($query1);
	$row1=mysqli_fetch_assoc($result1);
	
	$query2="select sum(convert_stock) as conv_addqty from tbl_reserve_stock where stock_status !=2 and base_unit!=convert_unit and stock_flage=1 ".$rwhser." ".$rwhser1." and convert_unit=".$unit_id." and product_id=".$product_id;
	$result2=$dbcon->query($query2);
	$row2=mysqli_fetch_assoc($result2);
	
	$query3="select sum(base_stock) as base_usedqty from tbl_reserve_stock where stock_status!=2 and stock_flage=2 and base_unit=".$unit_id." ".$rwhser." ".$rwhser1." and product_id=".$product_id;
	$result3=$dbcon->query($query3);
	$row3=mysqli_fetch_assoc($result3);
	
	$query4="select sum(convert_stock) as conv_usedqty from tbl_reserve_stock where stock_status!=2 and base_unit!=convert_unit ".$rwhser." ".$rwhser1." and stock_flage=2 and convert_unit=".$unit_id." and product_id=".$product_id;
	$result4=$dbcon->query($query4);
	$row4=mysqli_fetch_assoc($result4);
	
	$res_qty=($row1['base_addqty']+$row2['conv_addqty'])-($row3['base_usedqty']+$row4['conv_usedqty']);
	//$j=$row1['base_addqty']."-1".$row2['conv_addqty']."-2".$row3['base_usedqty']."-3".$row4['conv_usedqty'];
	return $res_qty;
	//return $query3;
	//return $j;
}
function pending_reserve_qty($dbcon,$request_id){
	$query="select IFNULL(rp_req_qty,0) as rp_req_qty,process_unit from tbl_request_product where rp_id=".$request_id;
	$result=$dbcon->query($query);
	$row=mysqli_fetch_assoc($result);
	
	
	$query1="select IFNULL(sum(base_stock),0) as base_usedqty from tbl_reserve_stock where stock_status!=2 and stock_flage=1 and ref_name='request' and base_unit=".$row['process_unit']." and request_id=".$request_id;
	$result1=$dbcon->query($query1);
	$row1=mysqli_fetch_assoc($result1);
	
	$query2="select IFNULL(sum(convert_stock),0) as conv_usedqty from tbl_reserve_stock where stock_status!=2 and ref_name='request' and base_unit!=convert_unit and stock_flage=1 and convert_unit=".$row['process_unit']." and request_id=".$request_id;
	$result2=$dbcon->query($query2);
	$row2=mysqli_fetch_assoc($result2);
	
	$penqty=($row['rp_req_qty']-($row1['base_usedqty']+$row2['conv_usedqty']));
	
	return $penqty;
	//return $row['rp_req_qty']."---".$row1['base_usedqty']."---".$row2['conv_usedqty'];
}
function add_request_reserve_stock($dbcon,$request_id,$stock_qty,$unit_id){
	$query="select req.*,pro.product_conv_unit,pro.product_base_unit from tbl_request_product as req
			left join product_mst as pro on pro.product_id=req.rp_pid
			where rp_id in (".$request_id.")";
	$result=$dbcon->query($query);
	while($row=mysqli_fetch_assoc($result)){
		$product_id=$row['rp_pid'];
		if($row['perent_id']!=0){
		$reserve_stock=reserve_stock($dbcon,$product_id,$row['process_unit']);
		$current_stock=get_current_stock_new($dbcon,$product_id,$row['process_unit']);
		$stock=$current_stock-$reserve_stock;
			
		$reserve_pending_qty=pending_reserve_qty($dbcon,$row['rp_id']);
			if($row['product_conv_unit']!=$row['product_base_unit']){
				$conv_stock=convert_stock($dbcon,$reserve_pending_qty,$product_id,"base_unit");
			}else{
				$conv_stock=$reserve_pending_qty;
			} 
			//var_dump("id");
			//var_dump($row['rp_id']);
			//var_dump("current stock");
			//var_dump($current_stock);
			//var_dump("reserve_stock");
			//var_dump($reserve_stock);
			//var_dump("pending");
			//var_dump($reserve_pending_qty);
			//var_dump("br");
		if($stock>=$reserve_pending_qty){	
		
		if($row['product_conv_unit']==$unit_id){
			$type="base_unit";
			//$con_stock=$stock_qty;
			$con_stock=$reserve_pending_qty;
			//$base_stock=convert_stock($dbcon,$stock_qty,$product_id,$type);
			$base_stock=convert_stock($dbcon,$reserve_pending_qty,$product_id,$type);
		}else{
			$type="conv_unit";
			//$base_stock=$stock_qty;
			$base_stock=$reserve_pending_qty;
			//$con_stock=convert_stock($dbcon,$stock_qty,$product_id,$type);
			$con_stock=convert_stock($dbcon,$reserve_pending_qty,$product_id,$type);
		}
		
			$info['reserve_date']		=date('Y-m-d');
			$info['product_id']			=$product_id;
			$info['base_unit']			=$row['product_base_unit'];
			$info['base_stock']			=$base_stock;
			$info['convert_unit']		=$row['product_conv_unit'];
			$info['convert_stock']		=$con_stock;
			$info['stock_flage']		=1;
			$info['request_id']			=$row['rp_id'];
			$info['ref_name']			="request";
			
			$info['cdate']				=date('Y-m-d H:i:s');
			$info['user_id']			=$_SESSION['user_id'];
			$info['company_id']			=$_SESSION['company_id'];
			$inserid=add_record('tbl_reserve_stock', $info, $dbcon);
			$reserve_pending_qty=pending_reserve_qty($dbcon,$row['rp_id']);
			if($reserve_pending_qty<="0"){
				$q=$dbcon->query("update tbl_request_product set reserve_status='1' where rp_id=".$row['rp_id']);
			}
		}else if($stock>0){
			if($row['purchase_unit']!=$row['process_unit']){
				$conv_stock=convert_stock($dbcon,$stock,$product_id,"base_unit");
			}else{
				$conv_stock=$stock;
			}
			$info['reserve_date']		=date('Y-m-d');
			$info['product_id']			=$product_id;
			$info['base_unit']			=$row['purchase_unit'];
			$info['base_stock']			=$stock;
			$info['convert_unit']		=$row['process_unit'];
			$info['convert_stock']		=$conv_stock;
			$info['stock_flage']		=1;
			$info['request_id']			=$row['rp_id'];
			$info['ref_name']			="request";
			
			$info['cdate']				=date('Y-m-d H:i:s');
			$info['user_id']			=$_SESSION['user_id'];
			$info['company_id']			=$_SESSION['company_id'];
			$inserid=add_record('tbl_reserve_stock', $info, $dbcon);
		} 
		//var_dump($reserve_pending_qty);
		//var_dump($stock);
		//var_dump($info);
		//var_dump("122");
	}
	
	}
	//return $current_stock;
}

function add_request_reserve_stock1($dbcon,$request_id,$stock_qty,$unit_id){
	$query="select req.*,pro.product_conv_unit,pro.product_base_unit from tbl_request_product as req
			left join product_mst as pro on pro.product_id=req.rp_pid
			where rp_id=".$request_id;
	$result=$dbcon->query($query);
	while($row=mysqli_fetch_assoc($result)){
		$product_id=$row['rp_pid'];
		
		//$reserve_stock=reserve_stock($dbcon,$product_id,$row['purchase_unit']);
		//$current_stock=get_current_stock_new($dbcon,$product_id,$row['purchase_unit']);
		//$stock=$current_stock-$reserve_stock;
			
		//if($stock>=$reserve_pending_qty){
			/* if($row['product_conv_unit']!=$row['product_base_unit']){
				$conv_stock=convert_stock($dbcon,$reserve_pending_qty,$product_id,"base_unit");
			}else{
				$conv_stock=$reserve_pending_qty;
			} */
			
		if($row['product_conv_unit']==$unit_id){
			$type="base_unit";
			$con_stock=$stock_qty;
			$base_stock=convert_stock($dbcon,$stock_qty,$product_id,$type);
		}else{
			$type="conv_unit";
			$base_stock=$stock_qty;
			$con_stock=convert_stock($dbcon,$stock_qty,$product_id,$type);
		}
	
			$info['reserve_date']		=date('Y-m-d');
			$info['product_id']			=$product_id;
			$info['base_unit']			=$row['product_base_unit'];
			$info['base_stock']			=$base_stock;
			$info['convert_unit']		=$row['product_conv_unit'];
			$info['convert_stock']		=$con_stock;
			$info['stock_flage']		=1;
			$info['request_id']			=$row['rp_id'];
			$info['ref_name']			="request";
			
			$info['cdate']				=date('Y-m-d H:i:s');
			$info['user_id']			=$_SESSION['user_id'];
			$info['company_id']			=$_SESSION['company_id'];
			$inserid=add_record('tbl_reserve_stock', $info, $dbcon);
		$reserve_pending_qty=pending_reserve_qty($dbcon,$row['rp_id']);
		if($reserve_pending_qty<="0"){
			$q=$dbcon->query("update tbl_request_product set reserve_status='1' where rp_id=".$row['rp_id']);
		}
		/* }else if($stock>0){
			if($row['purchase_unit']!=$row['process_unit']){
				$conv_stock=convert_stock($dbcon,$stock,$product_id,"base_unit");
			}else{
				$conv_stock=$stock;
			}
			$info['reserve_date']		=date('Y-m-d');
			$info['product_id']			=$product_id;
			$info['base_unit']			=$row['purchase_unit'];
			$info['base_stock']			=$stock;
			$info['convert_unit']		=$row['process_unit'];
			$info['convert_stock']		=$conv_stock;
			$info['stock_flage']		=1;
			$info['request_id']			=$row['rp_id'];
			$info['ref_name']			="request";
			
			$info['cdate']				=date('Y-m-d H:i:s');
			$info['user_id']			=$_SESSION['user_id'];
			$info['company_id']			=$_SESSION['company_id'];
			$inserid=add_record('tbl_reserve_stock', $info, $dbcon);
		} */
		//var_dump($reserve_pending_qty);
		//var_dump($stock);
		//var_dump($info);
		//var_dump("122");
	}
	//return $current_stock;
}
function rquest_qty_deduct($dbcon,$product_id,$stock_qty){
	$query="select * from tbl_request_product where used_status=0 and status=0 and rp_pid=".$product_id;
	$result=$dbcon->query($query);
	while($row=mysqli_fetch_assoc($result)){
		$pending_qty=$row['rp_req_qty']-$row['used_rp_req_qty'];
		if($stock_qty>0){
			if($pending_qty<=$stock_qty){
				$q=$dbcon->query("update tbl_request_product set used_status='1',used_rp_req_qty=used_rp_req_qty+".$pending_qty." where rp_id=".$row['rp_id']);
				$stock_qty=$stock_qty-$pending_qty;
			}else{
				$q=$dbcon->query("update tbl_request_product set used_rp_req_qty=used_rp_req_qty+".$stock_qty." where rp_id=".$row['rp_id']);
				$stock_qty=$stock_qty-$stock_qty;
			}
		}
	}
}
function find_user_name($dbcon,$user_id){
	$que_po1="select user_name from users where user_id=".$user_id;
	$resi_grn1=$dbcon->query($que_po1);
	$re1=mysqli_fetch_assoc($resi_grn1);
	return $re1['user_name'];
}
function delete_back_process($dbcon,$grn_id,$jobwork_id){
	$que_po1="select j_alloc_process_id from tbl_jobwork where jobwork_id=".$jobwork_id;
	$resi_grn1=$dbcon->query($que_po1);
	$re1=mysqli_fetch_assoc($resi_grn1);
	$process_id=$re1['j_alloc_process_id'];
	
	$que_po2="select * from tbl_allocate_process_trn where p_status=1 and pt_process_id=".$jobwork_id." and grn_id=".$grn_id;
	$resi_grn2=$dbcon->query($que_po2);
	$re2=mysqli_fetch_assoc($resi_grn2);
	$qty=$re2['pt_qty'];
	
	$info['p_status']		= 2; 
	$updateid=update_record('tbl_allocate_process_trn', $info,"pt_id=".$re2['pt_id'] ,$dbcon);
	
	$dbcon->query("update tbl_jobwork set used_qty=used_qty-".$qty." where jobwork_id=".$jobwork_id."");
	
	$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty+".$qty." where p_id='$process_id'");
			
	$bb="update tbl_allocate_process set p_status=1,task_status=1 where p_id='$process_id'";
	$dbcon->query($bb);
			
	
}
function close_grn_to_process_19_8_2020($dbcon,$grn_id,$jobwork_id,$qty){
	//delete_back_process($dbcon,$grn_id,$jobwork_id);
	$que_po1="select j_alloc_process_id from tbl_jobwork where jobwork_id=".$jobwork_id;
	$resi_grn1=$dbcon->query($que_po1);
	$re1=mysqli_fetch_assoc($resi_grn1);
	$process_id=$re1['j_alloc_process_id'];
	
	$que_po="select * from tbl_allocate_process where p_id=".$process_id;
	$resi_grn=$dbcon->query($que_po);
	$re=mysqli_fetch_assoc($resi_grn);
	
	add_process_trn($dbcon,$process_id,$re['p_ref_id'],$re['p_product_id'],$re['process_id'],$qty,"1",$grn_id);
	
	$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$qty." where jobwork_id=".$jobwork_id."");
	
	$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-".$qty." where p_id='$process_id'");
			
			$set11="select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
			where p_id=".$process_id;
			$se=mysqli_fetch_assoc($dbcon->query($set11));
			$sss1=$se['start_qty']-$se['end_qty'];
			if($se['start_qty']<=$se['end_qty']){
				$bb="update tbl_allocate_process set p_status=0,task_status=0 where p_id='$process_id'";
				$dbcon->query($bb);
			}
			if($se['p_qty']==$se['end_qty']){
				$date=date("Y-m-d h:i:sa");
				$dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='".$date."',p_status=3 where p_id='$process_id'");
			}
}
function close_grn_to_process($dbcon,$grn_id,$jobwork_id,$qty){
	delete_back_process($dbcon,$grn_id,$jobwork_id);
	$que_po1="select p_id,qty,used_qty,(qty-used_qty) as pen_qty,jobwork_process_id from tbl_jobwork_process where jobwork_id=".$jobwork_id." having qty>used_qty";
	$resi_grn1=$dbcon->query($que_po1);
	$nqty=$qty;
	while($re1=mysqli_fetch_assoc($resi_grn1)){
		if($nqty>0){
			if($nqty>=$re1['pen_qty']){
				//$re1['pen_qty']
				$process_id=$re1['p_id'];
				$que_po="select * from tbl_allocate_process where p_id=".$process_id;
				$resi_grn=$dbcon->query($que_po);
				$re=mysqli_fetch_assoc($resi_grn);
				add_process_trn($dbcon,$process_id,$re['p_ref_id'],$re['p_product_id'],$re['process_id'],$re1['pen_qty'],"1",$grn_id);
		
				$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$re1['pen_qty']." where jobwork_process_id=".$re1['jobwork_process_id']."");
				
				$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$re1['pen_qty']." where jobwork_id=".$jobwork_id."");
		
				$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-".$re1['pen_qty']." where p_id='$process_id'");
				
				$set11="select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
				where p_id=".$process_id;
				$se=mysqli_fetch_assoc($dbcon->query($set11));
				$sss1=$se['start_qty']-$se['end_qty'];
				if($se['start_qty']<=$se['end_qty']){
					$bb="update tbl_allocate_process set p_status=0,task_status=0 where p_id='$process_id'";
					$dbcon->query($bb);
				}
				if($se['p_qty']==$se['end_qty']){
					$date=date("Y-m-d h:i:sa");
					$dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='".$date."',p_status=3 where p_id='$process_id'");
				}
				$nqty=$nqty-$re1['pen_qty'];
			}else{
				//$nqty
				$process_id=$re1['p_id'];
				$que_po="select * from tbl_allocate_process where p_id=".$process_id;
				$resi_grn=$dbcon->query($que_po);
				$re=mysqli_fetch_assoc($resi_grn);
				add_process_trn($dbcon,$process_id,$re['p_ref_id'],$re['p_product_id'],$re['process_id'],$nqty,"1",$grn_id);
		
				$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$nqty." where jobwork_id=".$jobwork_id."");
				
				$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$nqty." where jobwork_process_id=".$re1['jobwork_process_id']."");
		
				$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-".$nqty." where p_id='$process_id'");
				
				$set11="select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
				where p_id=".$process_id;
				$se=mysqli_fetch_assoc($dbcon->query($set11));
				$sss1=$se['start_qty']-$se['end_qty'];
				if($se['start_qty']<=$se['end_qty']){
					$bb="update tbl_allocate_process set p_status=0,task_status=0 where p_id='$process_id'";
					$dbcon->query($bb);
				}
				if($se['p_qty']==$se['end_qty']){
					$date=date("Y-m-d h:i:sa");
					$dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='".$date."',p_status=3 where p_id='$process_id'");
				}
				$nqty=$nqty-$nqty;
			}
		}
	}
}
function add_process_trn($dbcon,$allocate_id,$ref_id,$product_id,$process_id,$qty,$status,$grn_id){
		$info5['pt_alloc_id']		= $allocate_id;		
		$info5['pt_ref_id']			= $ref_id;		
		$info5['pt_product_id']		= $product_id;		
		$info5['pt_process_id']		= $process_id;		
		$info5['pt_qty']			= $qty;		
		$info5['p_status']			= $status;		
		$info5['grn_id']			= $grn_id;		
		
		$info5['cdate']				= date("Y-m-d H:i:s");
		$info5['process_time']		= date("Y-m-d H:i:s");
		$info5['user_id']			= $_SESSION['user_id'];
		$info5['company_id']		= $_SESSION['company_id'];	
		
		$inserid_alloc=add_record('tbl_allocate_process_trn', $info5, $dbcon);
	return $inserid_alloc;
}
function load_job_no($dbcon,$type_id){
	$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id='".$type_id."'";
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$row['invoiceno'] = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			}
			$row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			//echo json_encode($row);
		return $row['invoiceno'];
}
function add_process_stock($dbcon,$allocate_process_id,$accept_qty,$reject_qty,$next_process){
	$que_po="select * from tbl_allocate_process where p_id=".$allocate_process_id;
	$resi_grn=$dbcon->query($que_po);
	$re=mysqli_fetch_assoc($resi_grn);
	
	if($re['previous_process_id']!=0){
		$minq=$accept_qty+$reject_qty;
		$dbcon->query("update tbl_allocate_process set process_used_stock=process_used_stock+".$minq." where p_id=".$re['previous_process_id']."");
		
		//$info['company_id']	= $_SESSION['company_id'];
		//$updateid=update_record('tbl_allocate_process', $info,"p_id=".$POST['eid'] , $dbcon);
	}
	if(!empty($next_process)){
		$dbcon->query("update tbl_allocate_process set process_stock=process_stock+".$accept_qty." where p_id=".$allocate_process_id."");
	}else{
		$dbcon->query("update tbl_allocate_process set process_stock=process_stock+".$accept_qty." where p_id=".$allocate_process_id."");
		
		$dbcon->query("update tbl_allocate_process set process_used_stock=process_used_stock+".$accept_qty." where p_id=".$allocate_process_id."");
	}
}
function deduct_process_stock($dbcon,$allocate_process_id,$accept_qty,$reject_qty,$next_process){
	
	if(!empty($next_process)){
		$dbcon->query("update tbl_allocate_process set process_used_stock=process_used_stock+".$reject_qty." where p_id=".$allocate_process_id."");
	}else{
		$dedu=$accept_qty+$reject_qty;
		$dbcon->query("update tbl_allocate_process set process_used_stock=process_used_stock+".$dedu." where p_id=".$allocate_process_id."");
	}
}
function add_process_stock_new($dbcon,$allocate_process_id,$accept_qty){
	$que_po="select * from tbl_allocate_process where p_id=".$allocate_process_id;
	$resi_grn=$dbcon->query($que_po);
	$re=mysqli_fetch_assoc($resi_grn);
	
	if($re['previous_process_id']!=0){
		$minq=$accept_qty;
		$dbcon->query("update tbl_allocate_process set process_used_stock=process_used_stock+".$minq." where p_id=".$re['previous_process_id']."");
	}
		$dbcon->query("update tbl_allocate_process set process_stock=process_stock+".$accept_qty." where p_id=".$allocate_process_id."");
}
function process_allocate($dbcon,$pre_process_id,$process_id,$process_qty,$ref_id,$ref_type,$product_id,$process_type,$process_unit,$process_priority){
	
		$set11="select p_id,p_qty,pen_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty1 from tbl_allocate_process as alo
		where previous_process_id=".$pre_process_id." having start_qty1=0";
		$se=mysqli_fetch_assoc($dbcon->query($set11));
	
	if($se['p_id']){
		$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty+".$process_qty.",p_qty=p_qty+".$process_qty." where p_id=".$se['p_id']);
	}else{
		$info5['process_id']			= $process_id;			
		$info5['p_start_time']			= '';		
		$info5['p_end_time']			= '';		
		$info5['p_qty']					= $process_qty;		
		$info5['pen_qty']				= $process_qty;		
		$info5['process_unit']			= $process_unit;		
		$info5['p_ref_id']				= $ref_id;		
		$info5['p_ref_type']			= $ref_type;		
		$info5['p_product_id']			= $product_id;		
		$info5['pr_process_type']		= $process_type;		
		$info5['process_priority']		= $process_priority;		
		$info5['previous_process_id']	= $pre_process_id;		
		
		$info5['cdate']				= date("Y-m-d H:i:s");
		$info5['user_id']			= $_SESSION['user_id'];
		$info5['company_id']		= $_SESSION['company_id'];	
		
		$inserid_alloc=add_record('tbl_allocate_process', $info5, $dbcon);
	}
}
function get_current_godown_stock_new($dbcon,$pro_id,$unit_id,$godown_id)
{
	$query='SELECT pro.product_id,base_stock_add,base_stock_minus,con_stock_minus,con_stock_add FROM `product_mst` as pro 
	 
	left join (select sum(qc.base_stock) as base_stock_add,qc.product_id from tbl_stock_trn as qc 
	where qc.stock_status!=2 and stock_flage=1 and qc.base_unit='.$unit_id.' and qc.godown_id='.$godown_id.' and qc.company_id='.$_SESSION['company_id'].' 
	group by qc.product_id) as qc on qc.product_id=pro.product_id
	
	left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id from tbl_stock_trn as qc 
	where qc.stock_status!=2 and stock_flage=2 and qc.base_unit='.$unit_id.' and qc.godown_id='.$godown_id.' and qc.company_id='.$_SESSION['company_id'].' 
	group by qc.product_id) as qc1 on qc1.product_id=pro.product_id
	
	left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id from tbl_stock_trn as qc 
	where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit='.$unit_id.' and qc.godown_id='.$godown_id.' and qc.company_id='.$_SESSION['company_id'].' 
	group by qc.product_id) as qc2 on qc2.product_id=pro.product_id
	
	left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id from tbl_stock_trn as qc 
	where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit='.$unit_id.' and qc.godown_id='.$godown_id.' and qc.company_id='.$_SESSION['company_id'].' 
	group by qc.product_id) as qc3 on qc3.product_id=pro.product_id
	
	where pro.product_id='.$pro_id ;
	$rows=mysqli_fetch_assoc($dbcon->query($query));
	$stock=($rows['base_stock_add']+$rows['con_stock_add'])-($rows['base_stock_minus']+$rows['con_stock_minus']);
	
	return floatval($stock);
	//return floatval($pro_id);
} 
function get_current_stock_new($dbcon,$pro_id,$unit_id)
{
	$query='SELECT pro.product_id,base_stock_add,base_stock_minus,con_stock_minus,con_stock_add FROM `product_mst` as pro 
	 
	left join (select sum(qc.base_stock) as base_stock_add,qc.product_id from tbl_stock_trn as qc 
	where qc.stock_status!=2 and stock_flage=1 and qc.base_unit='.$unit_id.' and qc.company_id='.$_SESSION['company_id'].' 
	group by qc.product_id) as qc on qc.product_id=pro.product_id
	
	left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id from tbl_stock_trn as qc 
	where qc.stock_status!=2 and stock_flage=2 and qc.base_unit='.$unit_id.' and qc.company_id='.$_SESSION['company_id'].' 
	group by qc.product_id) as qc1 on qc1.product_id=pro.product_id
	
	left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id from tbl_stock_trn as qc 
	where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit='.$unit_id.' and qc.company_id='.$_SESSION['company_id'].' 
	group by qc.product_id) as qc2 on qc2.product_id=pro.product_id
	
	left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id from tbl_stock_trn as qc 
	where qc.stock_status!=2 and stock_flage=2 and qc.base_unit!=qc.convert_unit and qc.convert_unit='.$unit_id.' and qc.company_id='.$_SESSION['company_id'].' 
	group by qc.product_id) as qc3 on qc3.product_id=pro.product_id
	
	where pro.product_id='.$pro_id ;
	$rows=mysqli_fetch_assoc($dbcon->query($query));
	$stock=($rows['base_stock_add']+$rows['con_stock_add'])-($rows['base_stock_minus']+$rows['con_stock_minus']);
	
	//$stock=($row['base_stock_add']+$row['con_stock_add'])-($row['base_stock_minus']+$row['con_stock_minus']);
					
	
	return floatval($stock);
	//return $query;
}
function convert_stock($dbcon,$stock,$product_id,$type){
	$que_po="select * from product_mst where product_id=".$product_id;
	$resi_grn=$dbcon->query($que_po);
	$re=mysqli_fetch_assoc($resi_grn);
	if($re['product_base_unit']!=$re['product_conv_unit']){
		if($type=="base_unit"){
			$ret_qty=($stock/$re['product_conv_qty'])*$re['product_base_qty'];
		}else{
			$ret_qty=($stock/$re['product_base_qty'])*$re['product_conv_qty'];
		}
	}else{
		$ret_qty=$stock;
	}
	return $ret_qty;
	//return $type;
}
function update_grn_status($dbcon,$po_id){
	$que_po="select * from tbl_potrancation where po_id=".$po_id;
		$resi=$dbcon->query($que_po);
		while($re_po=mysqli_fetch_assoc($resi)){
			if($re_po['grn_id']!="0"){
				 $que_grn="select grn.grn_id,gtrn.product_qty,grn.grn_no,grn.grn_date,pro.product_name,led.l_name,(select IFNULL(sum(product_qty),0) as qty  from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty from tbl_grn as grn where grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type=2 and grn.grn_id=".$re_po['grn_id']." having gtrn.product_qty > used_qty";
					$resi_grn=$dbcon->query($que_grn);
					$re_grn=mysqli_fetch_assoc($resi_grn);
				if(!empty($re_grn['grn_id'])){
					$info['purchase_status']=0;
					$updateid=update_record('tbl_grn',$info,"grn_id=".$re_grn['grn_id'] , $dbcon);
				}else{
					$info['purchase_status']=1;
					$updateid=update_record('tbl_grn',$info,"grn_id=".$re_po['grn_id'] , $dbcon);
				}
					 
			}
		}
}
function find_leat_vender($dbcon,$product_id){
	
	$que_po="select min(party_rate) as mrate from tbl_product_party_purchase where party_product=".$product_id;
		$resi=$dbcon->query($que_po);
		$re_po=mysqli_fetch_assoc($resi);
		
	$que1="select * from tbl_product_party_purchase where party_product=".$product_id." and party_rate=".$re_po['mrate'];
		$row1=$dbcon->query($que1);
		$row=mysqli_fetch_assoc($row1);
		
	return $row['party_id']; 
	//return 1; 
}
 function delete_po_req_status($dbcon,$po_trn_id){
	$que_po="select * from tbl_purchaseordertrn where temptrn_ref_id!='' and purchaseordertrn_id=".$po_trn_id;
		$resi=$dbcon->query($que_po);
		while($re_po=mysqli_fetch_assoc($resi))
		{
			$infos1['purchaseordertrn_req_status']		= 2;
			update_record('tbl_purchaseorder_req_trn', $infos1,"purchaseordertrn_id=".$re_po['purchaseordertrn_id'], $dbcon);
			
			$infos2['po_trn_req_status']		= 0;
			update_record('tbl_purchasetrntemp', $infos2,"purchaseordertrn_id in (".$re_po['temptrn_ref_id'].")", $dbcon);
		}
} 
  function update_poreq_status_edit($dbcon,$po_trn_id){
		$que_po="select * from tbl_purchaseordertrn where temptrn_ref_id!='' and purchaseordertrn_id=".$po_trn_id;
		$resi=$dbcon->query($que_po);
		while($re_po=mysqli_fetch_assoc($resi))
		{
			$infos1['purchaseordertrn_req_status']		= 2;
			update_record('tbl_purchaseorder_req_trn', $infos1,"purchaseordertrn_id=".$re_po['purchaseordertrn_id'], $dbcon);
			
			$infos2['po_trn_req_status']		= 0;
			update_record('tbl_purchasetrntemp', $infos2,"purchaseordertrn_id in (".$re_po['temptrn_ref_id'].")", $dbcon);
			
			$used_qty=$re_po['product_qty'];
			
			$que_sub="select * from tbl_purchasetrntemp where purchaseordertrn_id in (".$re_po['temptrn_ref_id'].")";
			$resub=$dbcon->query($que_sub);
			while($re_sub=mysqli_fetch_assoc($resub))
			{ 
				$query_p="select sum(used_qty) as used_qty from tbl_purchaseorder_req_trn where purchaseordertrn_req_status=0 and req_id=".$re_sub['purchaseordertrn_id'];
				$rels=mysqli_fetch_assoc($dbcon->query($query_p));
				$pending_qty=$re_sub['product_qty']-$rels['used_qty'];
				if($used_qty>0){
					if(!empty($used_qty)){
					if($pending_qty>0){
						if($pending_qty<=$used_qty){
							$info1['req_id']					= $re_sub['purchaseordertrn_id'];
							$info1['purchaseordertrn_id']		= $re_po['purchaseordertrn_id'];
							$info1['used_qty']					= $pending_qty;
							$info1['user_id']					= $_SESSION['user_id'];
							$info1['company_id']				= $_SESSION['company_id'];
							$ins_id=add_record('tbl_purchaseorder_req_trn', $info1, $dbcon);
							$used_qty=$used_qty-$pending_qty;
							
							$infos['po_trn_req_status']		= 1;
							update_record('tbl_purchasetrntemp', $infos,"purchaseordertrn_id=".$re_sub['purchaseordertrn_id'], $dbcon);
						}else{
							$info1['req_id']					= $re_sub['purchaseordertrn_id'];
							$info1['purchaseordertrn_id']		= $re_po['purchaseordertrn_id'];
							$info1['used_qty']					= $used_qty;
							$info1['user_id']					= $_SESSION['user_id'];
							$info1['company_id']				= $_SESSION['company_id'];
							$ins_id=add_record('tbl_purchaseorder_req_trn', $info1, $dbcon);
							$used_qty=$used_qty-$pending_qty;
						}
					}
					}
				}
			}
		}
} 
function update_poreq_status($dbcon,$po_id){
		 $que_po="select * from tbl_purchaseordertrn where temptrn_ref_id!='' and purchaseorder_id=".$po_id;
		$resi=$dbcon->query($que_po);
		while($re_po=mysqli_fetch_assoc($resi))
		{
			 $infos1['purchaseordertrn_req_status']		= 2;
			update_record('tbl_purchaseorder_req_trn', $infos1,"purchaseordertrn_id=".$re_po['purchaseordertrn_id'], $dbcon);
			
			$infos2['po_trn_req_status']		= 0;
			update_record('tbl_purchasetrntemp', $infos2,"purchaseordertrn_id in (".$re_po['temptrn_ref_id'].")", $dbcon);
			
			$used_qty=$re_po['product_qty'];
			
			$que_sub="select * from tbl_purchasetrntemp where purchaseordertrn_id in (".$re_po['temptrn_ref_id'].")";
			$resub=$dbcon->query($que_sub);
			while($re_sub=mysqli_fetch_assoc($resub))
			{ 
				$query_p="select sum(used_qty) as used_qty from tbl_purchaseorder_req_trn where purchaseordertrn_req_status=0 and req_id=".$re_sub['purchaseordertrn_id'];
				$rels=mysqli_fetch_assoc($dbcon->query($query_p));
				$pending_qty=$re_sub['product_qty']-$rels['used_qty'];
				if($used_qty>0){
					if($pending_qty>0){
						if($pending_qty<=$used_qty){
							$info1['req_id']					= $re_sub['purchaseordertrn_id'];
							$info1['purchaseordertrn_id']		= $re_po['purchaseordertrn_id'];
							$info1['used_qty']					= $pending_qty;
							$info1['user_id']					= $_SESSION['user_id'];
							$info1['company_id']				= $_SESSION['company_id'];
							$ins_id=add_record('tbl_purchaseorder_req_trn', $info1, $dbcon);
							$used_qty=$used_qty-$pending_qty;
							
							$infos['po_trn_req_status']		= 1;
							update_record('tbl_purchasetrntemp', $infos,"purchaseordertrn_id=".$re_sub['purchaseordertrn_id'], $dbcon);
						}else{
							$info1['req_id']					= $re_sub['purchaseordertrn_id'];
							$info1['purchaseordertrn_id']		= $re_po['purchaseordertrn_id'];
							$info1['used_qty']					= $used_qty;
							$info1['user_id']					= $_SESSION['user_id'];
							$info1['company_id']				= $_SESSION['company_id'];
							$ins_id=add_record('tbl_purchaseorder_req_trn', $info1, $dbcon);
							$used_qty=$used_qty-$pending_qty;
						}
					}
				}
			} 
		} 
}
function load_led_no($dbcon,$type,$ref_id){
	if($type=="tbl_payment"){
		$qry1="select * from tbl_receipt as cert 
			where receipt_id=".$ref_id;
		$ro=$dbcon->query($qry1);
		$re=mysqli_fetch_assoc($ro);
		//$ret=$re['receipt_no'];
		$type_label = ($re['payment_type'] == '2') ? 'Payment No : ' : 'Recipt No : ';

		//Amish Soni 15-09-2020
		$remark = ($re['payment_type'] == '2') ? ' ('.$re['payment_remark'].') ' : '';
		$ret=$type_label.$re['receipt_no'].$remark;
	}else if($type=="account_voucher_trn"){
		$qry1="select * from account_voucher_trn as cert 
			left join account_voucher_mst as jou on jou.voucher_mstid=cert.voucher_mstid
			where voucher_trnid=".$ref_id;
		$ro=$dbcon->query($qry1);
		$re=mysqli_fetch_assoc($ro);
		$ret=$re['voucher_no'];
	}else if($type=="tbl_invoice"){
		$qry1="select * from tbl_invoice as cert 
			where invoice_id=".$ref_id;
		$ro=$dbcon->query($qry1);
		$re=mysqli_fetch_assoc($ro);
		$ret=$re['invoice_no'];
	}
	else if($type=="tbl_purchase"){
		$qry1="select * from tbl_pono as cert 
			where po_id=".$ref_id;
		$ro=$dbcon->query($qry1);
		$re=mysqli_fetch_assoc($ro);
		$ret=$re['po_no'];
	}else if($type=="tbl_expense_detail"){
		$qry1="select com.complaint_no,cert.remark from tbl_expense_detail as cert 
				left join tbl_complaint as com on com.complaint_id=cert.expense_complain
			where ex_id=".$ref_id;
		$ro=$dbcon->query($qry1);
		$re=mysqli_fetch_assoc($ro);
		$ret=$re['complaint_no'].'('.$re['remark'].')';
	}else if($type=="tbl_expense_detail_account"){
		$qry1="select com.complaint_no,cert.remark,led.l_name from tbl_expense_detail as cert 
				left join tbl_complaint as com on com.complaint_id=cert.expense_complain
				left join tbl_ledger as led on led.l_id=cert.emp_id
			where ex_id=".$ref_id;
		$ro=$dbcon->query($qry1);
		$re=mysqli_fetch_assoc($ro);
		$ret=$re["complaint_no"].'-'.$re["l_name"].'-('.$re["remark"].')';
	}
	return $ret;
}
function get_ledger_amount($dbcon,$ledger_id,$end_date){
	
	$query="select opn_balance as opening_balance,balance_typeid,debitamount,creditamount from tbl_ledger as cust 
		left join 
		(select sum(amount) as debitamount,invoice.ledger_id from tbl_general_book as invoice where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=2 and invoice.company_id=".$_SESSION['company_id']." and ref_date <= '".date('Y-m-d',strtotime($end_date))."'  group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 
		
		left join 
		(select sum(amount) as creditamount,rec.ledger_id from tbl_general_book as rec 
			where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=1 and company_id=".$_SESSION['company_id']." and ref_date <= '".date('Y-m-d',strtotime($end_date))."'  group by rec.ledger_id) as creditcust on creditcust.ledger_id=cust.l_id 
		
		where cust.l_id=".$ledger_id;
		
		
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		/* $op_balance=($rel['balance_typeid']=="2"?(-$rel['opening_balance']):$rel['opening_balance']);
		// $balance=$op_balance+$rel['debitamount']-($rel['creditamount']);
		 $balance=($op_balance+$rel['creditamount'])-($rel['debitamount']);
		  */
		 $op_balance=($rel['balance_typeid']=="2"?($rel['opening_balance']):-$rel['opening_balance']);
		 $balance=$op_balance+$rel['debitamount']-($rel['creditamount']);
		return $balance;
}
function get_group_ledger_amount($dbcon,$group_id,$end_date){
	$query="select debit_op,cradit_op,debitamount,creditamount from tbl_group as cust 
		left join 
			(select sum(opn_balance) as debit_op,led.l_group from tbl_ledger as led 
				where l_status=0 and balance_typeid=2 and led.company_id=".$_SESSION['company_id']."  group by led.l_group) as debit_opening on debit_opening.l_group=cust.g_id 
		left join 
			(select sum(opn_balance) as cradit_op,led.l_group from tbl_ledger as led 
				where l_status=0 and balance_typeid=1 and led.company_id=".$_SESSION['company_id']."  group by led.l_group) as credit_opening on credit_opening.l_group=cust.g_id 
		
		left join 
			(select sum(amount) as debitamount,lde.l_group from tbl_general_book as invoice 
				left join tbl_ledger as lde on lde.l_id=invoice.ledger_id
				where genral_book_status=0 and lde.l_status=0 and table_name!='tbl_ledger' and entry_type=2 and invoice.company_id=".$_SESSION['company_id']." and ref_date <= '".date('Y-m-d',strtotime($end_date))."'  group by lde.l_group) as debitinvoice on debitinvoice.l_group=cust.g_id 
		
		left join 
			(select sum(amount) as creditamount,lde.l_group from tbl_general_book as rec
				left join tbl_ledger as lde on lde.l_id=rec.ledger_id
				where rec.genral_book_status=0 and lde.l_status=0 and table_name!='tbl_ledger' and entry_type=1 and rec.company_id=".$_SESSION['company_id']." and ref_date < '".date('Y-m-d',strtotime($end_date))."'  group by lde.l_group) as creditcust on creditcust.l_group=cust.g_id 
		
		where cust.g_id=".$group_id;
		
		
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		//$op_balance=($rel['balance_typeid']=="2"?(-$rel['opening_balance']):$rel['opening_balance']);
		 $balance=($rel['creditamount']+$rel['cradit_op'])-($rel['debitamount']-$rel['debit_op']);
		 
		return $balance;
		//return $query;
}
function get_general_book_id($dbcon,$table_name,$table_id,$ledger_id){
	//pathik created
	$qry1="select general_book_id from tbl_general_book as cert where genral_book_status=0 and table_id=".$table_id." and table_name='".$table_name."' and ledger_id=".$ledger_id." " ;
	$ro=$dbcon->query($qry1);
	$re=mysqli_fetch_assoc($ro);
	
	return $re['general_book_id'];
}
function get_ledger($dbcon,$ledger_id,$where){
	//add pathik
	$str='';
	
	$query="select * from tbl_ledger as pro where l_status=0 ".$where." and company_id = $_SESSION[company_id] order by TRIM(l_name) ASC";
	$rs_dispatch=$dbcon->query($query);	
	$str .= '<option value="">--select ledger--</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		if($rel['l_id']==$ledger_id)
		{$sel ="selected='selected'";}
		
		$str .= '<option '.$sel.' value="'.$rel['l_id'].'">'.$rel['l_name'].'</option>';
	}
	return $str;
}
function group_ledger_amount($dbcon,$end_date,$group_id){
		$query="select l.opn_balance,l.balance_typeid,exp_amt.exp_amount,pay_amt.pay_amount from tbl_ledger as l
					
		left join 
			( select sum(paid_amount) as exp_amount,emp_id,expense_date from tbl_expense_detail where expense_approve_status!=2 and expense_status=0 and expense_date < '".date('Y-m-d',strtotime($s_date[0]))."' group by emp_id ) as exp_amt on exp_amt.emp_id=l.l_id
		
		left join 
			( select sum(total_paid_amount) as pay_amount,cust_id,receipt_date from tbl_receipt where status=0 and  receipt_date < '".date('Y-m-d',strtotime($s_date[0]))."' group by cust_id ) as pay_amt on pay_amt.cust_id=l.l_id
			
			where l.l_id='$emp_id'
		";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		
		$op_balance=($rel['balance_typeid']=="2"?(-$rel['opn_balance']):$rel['opn_balance']);//1credit,2debit
		
		$balance=($op_balance+$rel['exp_amount'])-$rel['pay_amount'];
}
function expance_paid_amount($dbcon,$ex_id){
	//pathik genrated
	$qry='SELECT sum(total_amount) as pamount FROM tbl_receipt_trn as quot 
			WHERE status=0 and ex_id='.$ex_id;
	$qry_rel=mysqli_fetch_assoc($dbcon->query($qry));
	return $qry_rel['pamount'];
	//return $qry;
}
function common_log_entry($dbcon,$log_form,$log_mode,$ref_tbl,$ref_id){
	$cdate = date("Y-m-d H:i:s");
	$ins_qry="INSERT INTO tbl_log_all(`log_form`, `log_mode`, `ref_tbl`, `ref_id`, `user_id`, `cdate`, `company_id`) VALUES ('".$log_form."','".$log_mode."','".$ref_tbl."','".$ref_id."','".$_SESSION['user_id']."','".$cdate."','".$_SESSION['company_id']."')";
	$ins_qry_rs=$dbcon->query($ins_qry);
	
}
function count_pend_po_upload($dbcon){
	$where='';
	if($_SESSION['user_type']!='2' && $_SESSION['user_type']!='9') {
		$where.=' and inq.won_user_id='.$_SESSION['user_id'];
	}
$qry='SELECT COUNT(quot.quotation_id) as ttl_pen_po FROM tbl_quotation as quot 
left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
WHERE quot.quotation_status = 0 and revise_status=0 and approve_status=1 and stage_prob>=90 and po_approve_status!=3'.$where;
	$qry_rel=mysqli_fetch_assoc($dbcon->query($qry));
	return floatval($qry_rel['ttl_pen_po']);
}
function count_pend_appoint($dbcon){
	$user_id=$_SESSION['user_id'];
	$qry='SELECT count(task.task_id) as ttl_pen_disp
from tbl_task as task 
where task.task_status = 0 and task.entry_type=2 and task.alert_date_time!="0000-00-00 00:00:00" and task.alert_date_time!="1970-01-01 05:30:00" and find_in_set('.$user_id.',task.assign_user_ids) and alert_date_time<"'.date('Y-m-d H:i:s').'"';
	$qry_rel=mysqli_fetch_assoc($dbcon->query($qry));
	return floatval($qry_rel['ttl_pen_disp']);
}
function count_pend_disp($dbcon){
	$qry='SELECT count(trn.quot_trn_id) as ttl_pen_disp
from tbl_quotation as quot 
left join tbl_quotation_trn as trn on trn.quotation_id=quot.quotation_id
where quot.quotation_status = 0 and payment_approve_status=1 and trn.quot_trn_status=0 and trn.inv_done_status=0';
	$qry_rel=mysqli_fetch_assoc($dbcon->query($qry));
	return floatval($qry_rel['ttl_pen_disp']);
}
function count_usr_pen_tsk($dbcon,$task_type_id,$user_id){
	$fis=check_crm_find_in_set($dbcon,$user_id,1);
	//$fis1=' and instr("'.$fis.'",task.assign_user_ids)';
	//$fis1="  and FIND_IN_SET (".$_SESSION['user_id'].",task.show_user_ids)";
	$fis1="  and FIND_IN_SET (".$user_id.",task.show_user_ids)";
	 /* $qry='SELECT count(task.task_id) as ttl_pen_tasks
from tbl_task as task
WHERE task.task_status=0 and task.entry_type=1 and task.alert_date_time!="0000-00-00 00:00:00" and task.alert_date_time!="1970-01-01 05:30:00" and find_in_set('.$_SESSION['user_id'].',task.assign_user_ids) and alert_date_time<"'.date('Y-m-d H:i:s').'" and task.task_type_id='.$task_type_id.' order by task.task_priority_id,task.alert_date_time'; */ 

 /*$qry='SELECT count(DISTINCT task.task_id) as ttl_pen_tasks
from tbl_task as task
WHERE task.task_status=0 and task.entry_type=1 and task.alert_date_time!="0000-00-00 00:00:00" and task.alert_date_time!="1970-01-01 05:30:00" '.$fis1.' and alert_date_time<"'.date('Y-m-d H:i:s').'" and task.task_type_id='.$task_type_id.' order by task.task_priority_id,task.alert_date_time'; */
$qry='SELECT count(DISTINCT task.task_id) as ttl_pen_tasks
        from tbl_task as task
        WHERE task.task_status=0 and task.entry_type=1 
        and DATE_FORMAT(task.task_due_date,"%Y-%m-%d")<="'.date('Y-m-d').'"
        and task.task_type_id='.$task_type_id.' 
        '.$fis1.'
        order by task_due_date DESC'; 


	$qry_rel=mysqli_fetch_assoc($dbcon->query($qry));
	return floatval($qry_rel['ttl_pen_tasks']);
	//return $task_type_id;
	//return $qry;
}
function count_usr_pen_tsk1($dbcon,$task_type_id,$user_id){
	$fis=check_crm_find_in_set($dbcon,$user_id,1);
	//$fis1=' and instr("'.$fis.'",task.assign_user_ids)';
	//$fis1=' and instr("'.$user_id.'",task.assign_user_ids)';
	$fis1="  and FIND_IN_SET (".$user_id.",task.assign_user_ids)";
	 /* $qry='SELECT count(task.task_id) as ttl_pen_tasks
from tbl_task as task
WHERE task.task_status=0 and task.entry_type=1 and task.alert_date_time!="0000-00-00 00:00:00" and task.alert_date_time!="1970-01-01 05:30:00" and find_in_set('.$_SESSION['user_id'].',task.assign_user_ids) and alert_date_time<"'.date('Y-m-d H:i:s').'" and task.task_type_id='.$task_type_id.' order by task.task_priority_id,task.alert_date_time'; */ 

 /*$qry='SELECT count(DISTINCT task.task_id) as ttl_pen_tasks
from tbl_task as task
WHERE task.task_status=0 and task.entry_type=1 and task.alert_date_time!="0000-00-00 00:00:00" and task.alert_date_time!="1970-01-01 05:30:00" '.$fis1.' and alert_date_time<"'.date('Y-m-d H:i:s').'" and task.task_type_id='.$task_type_id.' order by task.task_priority_id,task.alert_date_time'; */

$qry='SELECT count(DISTINCT task.task_id) as ttl_pen_tasks
from tbl_task as task
WHERE task.task_status=0 and task.entry_type=1 
and DATE_FORMAT(task.task_due_date,"%Y-%m-%d")<="'.date('Y-m-d').'" 
'.$fis1.' 
and task.task_type_id='.$task_type_id.' 
order by task_due_date DESC'; 
	$qry_rel=mysqli_fetch_assoc($dbcon->query($qry));
	return floatval($qry_rel['ttl_pen_tasks']);
	//return $task_type_id;
	//return $qry;
}
function get_for_cast_by($dbcon,$id)
{
	$pro_tml='';
	$pro_tml.= '<option value="1" '.(($id=="1")?"selected":"").'>Calender Year</option>';
	$pro_tml.= '<option value="2" '.(($id=="2")?"selected":"").'>Financial Year</option>';
	return $pro_tml;
}
function get_for_cast_by_name($id){
	switch($id){
		case "1":
			$name="Calender Year";
			break;
		case "2":
			$name="Financial Year";
			break;
		default:
			$name="";
	}
	return $name;
}
function get_for_target_p($dbcon,$id)
{
	$html='';
	$html.= '<option value="1" '.(($id=="1")?"selected":"").'>Monthly</option>';
	$html.= '<option value="2" '.(($id=="2")?"selected":"").'>Quarterly</option>';
	$html.= '<option value="3" '.(($id=="3")?"selected":"").'>Half Yearly</option>';
	$html.= '<option value="4" '.(($id=="4")?"selected":"").'>Yearly</option>';
	return $html;
}
function get_for_target_p_name($dbcon,$id)
{
	switch($id){
		case "1":
			$name="Monthly";
			break;
		case "2":
			$name="Quarterly";
			break;
		case "3":
			$name="Half Yearly";
			break;
		case "4":
			$name="Yearly";
			break;
		default:
			$name="";
	}
	return $name;
}
function getMonthNumber($monthStr) {
//e.g, $month='Jan' or 'January' or 'JAN' or 'JANUARY' or 'january' or 'jan'
$m = ucfirst(strtolower(trim($monthStr)));
switch ($m) {
    case "January":        
    case "Jan":
        $m = "01";
        break;
    case "February":
    case "Feb":
        $m = "02";
        break;
    case "March":
    case "Mar":
        $m = "03";
        break;
    case "April":
    case "Apr":
        $m = "04";
        break;
    case "May":
        $m = "05";
        break;
    case "June":
    case "Jun":
        $m = "06";
        break;
    case "July":        
    case "Jul":
        $m = "07";
        break;
    case "August":
    case "Aug":
        $m = "08";
        break;
    case "September":
    case "Sep":
        $m = "09";
        break;
    case "October":
    case "Oct":
        $m = "10";
        break;
    case "November":
    case "Nov":
        $m = "11";
        break;
    case "December":
    case "Dec":
        $m = "12";
        break;
    default:
        $m = false;
        break;
}
return $m;
}
function get_for_period($dbcon,$f_by_id,$f_target_period,$f_period_id){
	
	$query="select f_period_id,f_period_name from forecast_period_mst where f_period_status=0 and f_by_id=".$f_by_id." and f_target_period=".$f_target_period."";

	$rs_dispatch=$dbcon->query($query);	
	$str='<option value="">Choose Period</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel='';
		if($rel['f_period_id']==$f_period_id){
			$sel='selected="selected"';
		}
		$str.='<option '.$sel.' value="'.$rel['f_period_id'].'">'.$rel['f_period_name'].'</option>';
	}
	return $str;
}
function load_f_by_year($f_by_id,$f_year){
	$str='';
	if($f_by_id=='1'){//Calender Year
		$s_year=2018;
		$e_year=date("Y");
		for($i=$e_year;$i>=$s_year;$i--){
			$sel='';
			if($f_year==$i) {
				$sel='selected="selected"';
			}
			$str.='<option value="'.$i.'" '.$sel.'>'.$i.'</option>';
		}
	}
	else if($f_by_id=='2'){//Financial Year
		$minyear= 2018;
		$maxyear=(date('m')<'04') ? date('Y',strtotime('-1 year')) : date('Y');
		$end = $start+1;
		for($y=$minyear;$y<=$maxyear;$y++)
		{
			$sel='';
			if($f_year==$y) {
				$sel='selected="selected"';
			}
			$str.='<option '.$sel.' value="'.$y.'">'.$y.'-'.($y+1).'</option>';
		}
	}
	
	return $str;
}
function get_child_users($dbcon,$id)
{
	$query="select user_id,user_name from users where active=0 and report_to_user_id='$_SESSION[user_id]' and company_id='$_SESSION[company_id]'";
	$rs_dispatch=$dbcon->query($query);	
	$str='<option value="">Choose Users</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel='';
		if($rel['user_id']==$id) {
			$sel='selected="selected"';
		}
		$str.='<option '.$sel.' value="'.$rel['user_id'].'">'.$rel['user_name'].'</option>';
	}
	return $str;
}
function get_inquiry($dbcon,$id)
{
	$query="select inquiry_id,inquiry_no from tbl_inquiry where inquiry_status=0 and company_id='$_SESSION[company_id]'";
	$rs_dispatch=$dbcon->query($query);	
	$str='<option value="">Choose Inquiry</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		// $sel='';
		if($rel['inquiry_id']==$id) {
			$sel='selected="selected"';
		}
		$str.='<option '.$sel.' value="'.$rel['inquiry_id'].'">'.$rel['inquiry_no'].'</option>';
	}
	return $str;
}
function get_contactperson_all($dbcon,$eid)
{
	$query="select c_con_id,c_con_fname,c_con_lname from tbl_cust_contact where c_con_status=0";
	$rs_cust=$dbcon->query($query);	
	$str='<option value="">Choose Contact Person</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{
		// $sel='';
		if($rel['c_con_id']==$eid){
			$sel="selected='selected'";
		}
		$str.= '<option '.$sel.' value="'.$rel['c_con_id'].'">'.$rel['c_con_fname'].' '.$rel['c_con_lname'].'</option>';
	}
	return $str;
}
function get_rel_task($dbcon,$sid)
{
	$qry="select * from task_rel_mst where task_rel_status=0";
	$rs_state=$dbcon->query($qry);	
	$str='';
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		$sel='';
		if($row['task_rel_id']==$sid)
		{$sel='selected="selected"';} else { $sel="";}
		$str.= '<option '.$sel.' value="'.$row['task_rel_id'].'">'.$row['task_rel_name'].'</option>';
	}
	return $str;
}
function get_alert_mintes($dbcon,$task_alert_id){
	$query="select * from task_alert_mst where task_alert_status=0 and task_alert_id=".$task_alert_id;
	$query_rs=$dbcon->query($query);	
	$query_rel=mysqli_fetch_assoc($query_rs);
	return floatval($query_rel['task_gap_minutes']);
}
function get_inquiry_stage($dbcon,$eid)
{
	$qry="select opp_id,opp_stage from tbl_opportunity_mst where opp_status=0 order by opp_priority";
	$rs_state=$dbcon->query($qry);	
	$str="<option value=''>Choose Stage</option>";
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		// $sel='';
		if($row['opp_id']==$eid)
		{$sel='selected="selected"';}
		$str.= '<option '.$sel.' value="'.$row['opp_id'].'">'.$row['opp_stage'].'</option>';
	}
	return $str;
} 
function get_product_common_tax($dbcon,$product_amount,$formulaid)
{
	$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
	$row=$dbcon->query($qry);
	$rate_total=$total=$product_amount;
	$i=1;
	while($tax=mysqli_fetch_assoc($row))
	{	
		$info['tax_name'.$i]=$tax['tax_name'];
		$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
		$rate_total+=$tax_amount;
		$i++;
	}
	for($j=$i;$j<=3;$j++)
	{
		$info['tax_name'.$j]='';
		$info['tax_amount'.$j]='';		
	}
	$info['product_total']=$rate_total;
	return $info;
}
function get_annexure_types($dbcon,$eid){
	$query="select an_id,an_name from tbl_annexure where an_status=0";
	$rs_dispatch=$dbcon->query($query);	
	$str='<option value="">Choose Annexure</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel='';
		if($rel['an_id']==$eid){
			$sel='selected="selected"';
		}
		$str.='<option '.$sel.' value="'.$rel['an_id'].'">'.$rel['an_name'].'</option>';
	}
	return $str;
}
function get_cust_inq($dbcon,$eid,$cust_id){
	$query="select inquiry_id,inquiry_name,inquiry_no from tbl_inquiry where inquiry_status=0 and cust_id=".$cust_id;
	$rs_dispatch=$dbcon->query($query);
	$str='<option value="">Choose Inquiry</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel='';
		if($rel['inquiry_id']==$eid){
			$sel='selected="selected"';
		}
		$str.='<option '.$sel.' value="'.$rel['inquiry_id'].'">'.$rel['inquiry_no'].'</option>';
	}
	return $str;
}
function get_cust_contactperson($dbcon,$eid,$cust_id)
{
	$query="select c_con_id,c_con_fname,c_con_lname from tbl_cust_contact where c_con_status=0 and cust_id='$cust_id'";
	$rs_cust=$dbcon->query($query);	
	$str='<option value="">Choose Contact Person</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{
		$sel='';
		if($rel['c_con_id']==$eid){
			$sel="selected='selected'";
		}
		$str.= '<option '.$sel.' value="'.$rel['c_con_id'].'">'.$rel['c_con_fname'].' '.$rel['c_con_lname'].'</option>';
	}
	return $str;
}
function getcust_crm($dbcon,$id)
{   
	//$query="select * from tbl_customer where cust_status=0 and  company_id in (0,$_SESSION[company_id])";
	$query="select * from tbl_customer  where cust_status=0 and company_id='$_SESSION[company_id]'";
	$rs_cust=$dbcon->query($query);
	echo '<option value="">Choose Company</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		// $sel='';
		if($rel['cust_id']==$id)
		{
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['cust_id'].'">'.$rel['cust_name'].'</option>';
	}

}
function get_terms_category($dbcon,$sid)
{
	$qry="select * from tbl_terms_category where tc_status=0";
	$rs_state=$dbcon->query($qry);	
	
	while($row=mysqli_fetch_assoc($rs_state))
	{	
			$sel='';
			if($row['tc_id']==$sid)
			{$sel='selected="selected"';} else { $sel="";}
			echo '<option '.$sel.' value="'.$row['tc_id'].'">'.$row['tc_name'].'</option>';
	}
}
function get_all_territory($dbcon,$sid)
{
	$qry="select t_id,t_name from territory_mst where t_status=0 order by t_id";
	$rs_state=$dbcon->query($qry);	
	$sid=explode(",",$sid);
	while($row=mysqli_fetch_assoc($rs_state))
	{	
			$sel='';
			if(in_array($row['t_id'],$sid))
			{$sel='selected="selected"';}
			echo '<option '.$sel.' value="'.$row['t_id'].'">'.$row['t_name'].'</option>';
	}
}
function get_master_category($dbcon,$sid)
{
	$qry="select * from tbl_master_category where mc_status=1";
	$rs_state=$dbcon->query($qry);	
	while($row=mysqli_fetch_assoc($rs_state))
	{	
			$sel='';
			if($row['mc_id']==$sid)
			{$sel='selected="selected"';} else { $sel="";}
			echo '<option '.$sel.' value="'.$row['mc_id'].'">'.$row['mc_name'].'</option>';
	}
}

function get_org_currency($dbcon,$sid)
{
	$qry="select c.currency_name,c.currency_id ,c.currency_code from tbl_org_currency as org left join tbl_currency as c on c.currency_id=org.curren_id where org.comp_id='$_SESSION[company_id]'";
	$rs_state=$dbcon->query($qry);	
	$str="";
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		$sel='';
		if($row['currency_id']==$sid)
		{$sel='selected="selected"';}else{ $sel=''; }
		$str.= '<option '.$sel.' value="'.$row['currency_id'].'">'.$row['currency_name'].' - '.$row['currency_code'].'</option>';
	}
	echo $str;
}
function get_task_alert_types($dbcon,$sid)
{
	$qry="select * from task_alert_mst where task_alert_status=0";
	$rs_state=$dbcon->query($qry);	
	$str='';
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		// $sel='';
		if($row['task_alert_id']==$sid)
		{$sel='selected="selected"';} else { $sel="";}
		$str.= '<option '.$sel.' value="'.$row['task_alert_id'].'">'.$row['task_alert_name'].'</option>';
	}
	return $str;
}
function get_task_priority($dbcon,$sid)
{
	$qry="select * from task_priority_mst where task_priority_status=0";
	$rs_state=$dbcon->query($qry);	
	$str='';
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		// $sel='';
		if($row['task_priority_id']==$sid)
		{$sel='selected="selected"';} else { $sel="";}
		$str.= '<option '.$sel.' value="'.$row['task_priority_id'].'">'.$row['task_priority_name'].'</option>';
	}
	return $str;
}

function get_users_typewise($dbcon,$sid,$whr)
{
	$qry="select * from users where active=0 and user_type!=1 ".$whr;
	$rs_state=$dbcon->query($qry);	
	$str='';
	$e_id=explode(",",$sid);
	$str.='<option value="">Choose User</option>';
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		// $sel='';
		//if($row['user_id']==$sid)
		if(in_array($row['user_id'],$e_id))
		{$sel='selected="selected"';} else {$sel="";}
		$str.= '<option '.$sel.' value="'.$row['user_id'].'">'.$row['user_name'].'</option>';
		get_users_typewise($dbcon,$row['user_id'],$whr);
	}
	return $str;
}

function get_assign_users($dbcon,$sid,$whr)
{
	$qry="select usr.*,type.usertype_name from users as usr
	left join tbl_usertype as type on type.usertype_id=usr.user_type
	where usr.active=0 and usr.user_type!=1".$whr;
	$rs_state=$dbcon->query($qry);	
	$str='';
	$e_id=explode(",",$sid);
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		$sel='';
		//if($row['user_id']==$sid)
		if(in_array($row['user_id'],$e_id))
		{$sel='selected="selected"';} else {$sel="";}
		$str.= '<option '.$sel.' value="'.$row['user_id'].'">'.$row['user_name'].' - '.$row['usertype_name'].'</option>';
	}
	return $str;
}
function get_master_category_dtl($dbcon,$sid,$mcd_cat_id,$inquiry_id=0,$type=0)
{
	$str='';
	// $wher="";
	if($type=="1"){
		$qry1="select * from tbl_quotation where quotation_status=0 and inquiry_id=".$inquiry_id;
		$rs_state1=$dbcon->query($qry1);	
		$row1=mysqli_fetch_assoc($rs_state1);
		if(empty($row1['quotation_no'])){
			$wher=" and mcd_id not in (20,21)";
		}else{
			$wher="";
		} 
	}
	$qry="select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 ".$wher." and mcd_cat_id=".$mcd_cat_id;
	$rs_state=$dbcon->query($qry);	
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		// $sel='';
		if($row['mcd_id']==$sid)
		{$sel='selected="selected"';}
		$str.= '<option '.$sel.' value="'.$row['mcd_id'].'">'.$row['mcd_name'].'</option>';
	}
	return $str;
	//return $qry;
}
function get_refer_by($dbcon,$sid)
{
	$qry="select * from tbl_refer_by where rb_status=0";
	$rs_state=$dbcon->query($qry);	
	$str="<option value=''>Choose Source / Referred By</option>";
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		$sel='';
		if($row['rb_id']==$sid)
		{$sel='selected="selected"';} else { $sel="";}
		$str.= '<option '.$sel.' value="'.$row['rb_id'].'">'.$row['rb_name'].'</option>';
	}
	echo $str;
}

function get_customer_type($dbcon,$sid)
{
	$qry="select * from tbl_customer_type where ct_status=0";
	$rs_state=$dbcon->query($qry);	
	
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		$sel='';
			if($row['ct_id']==$sid)
			{$sel='selected="selected"';} else { $sel="";}
			echo '<option '.$sel.' value="'.$row['ct_id'].'">'.$row['ct_name'].'</option>';
	}
}

function get_customer_industries($dbcon,$sid)
{
	$qry="select * from tbl_customer_industry where ci_status=0";
	$rs_state=$dbcon->query($qry);	
	
	while($row=mysqli_fetch_assoc($rs_state))
	{	
			$sel='';
			if($row['ci_id']==$sid)
			{$sel='selected="selected"';} else { $sel="";}
			echo '<option '.$sel.' value="'.$row['ci_id'].'">'.$row['ci_name'].'</option>';
	}
}

function get_customer_category($dbcon,$sid)
{
	$qry="select * from tbl_customer_category where cc_status=0";
	$rs_state=$dbcon->query($qry);	
	
	while($row=mysqli_fetch_assoc($rs_state))
	{	
			$sel='';
			if($row['cc_id']==$sid)
			{$sel='selected="selected"';} else { $sel="";}
			echo '<option '.$sel.' value="'.$row['cc_id'].'">'.$row['cc_name'].'</option>';
	}
}	
function get_customer_code($dbcon)
{
	$sel=$dbcon->query("select max(cust_code_series) as mid from tbl_customer");
	$row=mysqli_fetch_array($sel);
	$mid=$row['mid']+1;
	
	$month=date("m");
	$year=date("Y");
	
	$code="CUS".$month.$year.'-'.$mid;
	return $code;
}

function get_customer_code_series($dbcon)
{
	$sel=$dbcon->query("select max(cust_code_series) as mid from tbl_customer");
	$row=mysqli_fetch_array($sel);
	$mid=$row['mid']+1;
	
	return $mid;
}
function load_cust_prowise_model($dbcon,$id,$product_id,$cust_id) {
	$str='';
	$query="select sold_pro.model_id,model.model_name from tbl_cust_sold_pro as sold_pro
		inner join model_mst as model on model.model_id=sold_pro.model_id
		where cust_sold_pro_status=0 and cust_id=".$cust_id." and sold_pro.product_id=".$product_id." and sold_pro.company_id in(0,$_SESSION[company_id]) ";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Model</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['model_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['model_id'].'">'.$rel['model_name'].'</option>';
	}
	return $str;
}
function load_prowise_model($dbcon,$id,$product_id) {
	$str='';
	$query="select sold_pro.model_id,model.model_name from tbl_cust_sold_pro as sold_pro
		inner join model_mst as model on model.model_id=sold_pro.model_id
		where cust_sold_pro_status=0 and sold_pro.product_id=".$product_id." and sold_pro.company_id in(0,$_SESSION[company_id]) ";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Model</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['model_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['model_id'].'">'.$rel['model_name'].'</option>';
	}
	return $str;
}
function load_cust_sold_pro($dbcon,$id,$cust_id) {
	$str='';
	$query="select sold_pro.product_id,pro.product_name from tbl_cust_sold_pro as sold_pro
		inner join product_mst as pro on pro.product_id=sold_pro.product_id left join tbl_invoicetrn as i on i.product_id=sold_pro.product_id where cust_sold_pro_status=0 and cust_id=".$cust_id." and sold_pro.company_id in(0,$_SESSION[company_id]) group by sold_pro.cust_sold_pro_id";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Product</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['product_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name'].'</option>';
	}
	return $str;
}
function getproduct_typewise($dbcon,$id,$type)
{	
	$str='';$whr='';
	if($type!=''){
		$whr=' and product_type in('.$type.')';
	}
	$query="select product_id,product_name from product_mst as pro where product_status=0 ".$whr." order by product_name";
	$rs_dispatch=$dbcon->query($query);
	$str .= '<option value="">Choose Product</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		if($rel['product_id']==$id)
		{$sel ="selected='selected'";}
		$str .= '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name'].'</option>';
	}
	return $str;
}
function getproduct($dbcon,$id) {
	$str='';
	$query="select p.product_id,p.product_name,p.product_desc from product_mst as p
	where p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) ";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Product</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['product_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name']."-- ( ".$rel['product_desc'].')'.'</option>';
	}
	return $str;
}
function get_complaint_type($dbcon,$id) {
	$str='';
	$query="select `complaint_type_id`,`complaint_type_name` from complaint_type_mst where complaint_type_status=0 and company_id in(0,$_SESSION[company_id]) ";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Complaint Type</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['complaint_type_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['complaint_type_id'].'">'.$rel['complaint_type_name'].'</option>';
	}
	return $str;
} 
function get_prowise_model($dbcon,$id,$product_id) {
	$str='';
	$query="select `model_id`,`model_name` from model_mst where model_status=0 and product_id=".$product_id." and company_id in(0,$_SESSION[company_id]) ";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Model</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['model_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['model_id'].'">'.$rel['model_name'].'</option>';
	}
	return $str;
} 
function get_expense_head($dbcon,$id) {
	$str='';
	$query="select `expense_head_id`,`expense_head_name` from expense_head_mst where expense_head_status=0 and company_id in(0,$_SESSION[company_id]) ";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Expense Head</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['expense_head_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['expense_head_id'].'">'.$rel['expense_head_name'].'</option>';
	}
	return $str;
} 
function get_product_typewise($dbcon,$id,$type) {
	$str='';
	$query="select `product_id`,`product_name`,`product_type` from product_mst where product_status=0 and company_id in(0,$_SESSION[company_id]) and product_type in($type) ";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Product</option>';
	$type_arr=array('','FINISH PRODUCT','ASSEMBLY PRODUCT','SEMI-FINISH','RAW MATERIAL','FINISH COMPONENT');
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		$type_name=$type_arr[$rel['product_type']];//Get Product Type Name
		if($rel['product_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name'].' - '.$type_name.'</option>';
	}
	return $str;
}
function get_product($dbcon,$id,$type) {
	$str='';
	$query="select p.product_id,p.product_name,c.cat_name from product_mst as p left join tbl_category as c on c.cat_id=p.product_category where p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) and p.product_type in($type) ";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Product</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['product_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name']."-- ( ".$rel['cat_name'].')'.'</option>';
	}
	return $str;
}
/*function get_product_type_name($dbcon,$status_type){
	//Dont Change Sort of array, Add only at end
	$type_arr=array('','FINISH PRODUCT','ASSEMBLY PRODUCT','SEMI-FINISH','RAW MATERIAL','FINISH COMPONENT');
	return $type_arr[$status_type];
}*/
function get_product_type_name($dbcon,$status_type){
	//Dont Change Sort of array, Add only at end
	$type_arr=array('FINISH PRODUCT','ASSEMBLY PRODUCT','SUB ASSEMBLY','RAW MATERIAL','FINISH PART','BOI','CAPITAL GOODS','CONSUMABLE','SERVICE');
	return $type_arr[$status_type];
}
function get_comp_pay_sts_name($dbcon,$status_type){
	//Dont Change Sort of array, Add only at end
	$type_arr=array('','Free','Paid');
	return $type_arr[$status_type];
}
function get_zone($dbcon,$id) {
	$str='';
	$query="select `zone_id`,`zone_name` from zone_mst where zone_status=0 and company_id in(0,$_SESSION[company_id])";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Zone</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['zone_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['zone_id'].'">'.$rel['zone_name'].'</option>';
	}
	return $str;
}
function getcust_person($dbcon,$id,$cust_id)
{
	$str='';
	$query="select `cust_contact_person_id`,`cust_contact_person_name` from tbl_cust_contact_person where cust_contact_person_status=0 and cust_id=".$cust_id;
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Person</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['cust_contact_person_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['cust_contact_person_id'].'">'.$rel['cust_contact_person_name'].'</option>';
	}
	return $str;
}

function getcust_purchase($dbcon,$id,$product_id)
{
	$str='';
	//$query="select cust.cust_id,cust.company_name from tbl_customer as cust where cust_status=0 and cust.company_id in (0,$_SESSION[company_id])";
	
	$query="select l.l_id,l.l_name,p.po_id,pt.po_id,pt.po_landing_cost from tbl_ledger as l left join tbl_pono as p on p.vender_id=l.l_id left join tbl_potrancation as pt on pt.po_id=p.po_id  where l.l_status=0 and l.l_form='customer_form' and pt.product_id='$product_id' order by pt.po_landing_cost desc";
	$rs_cust=$dbcon->query($query);	
	$str= '<option value="">Choose Company</option>';
	$cnt=1;
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($id=='')
		{
			if($cnt==1) { $sel.="selected='selected'"; }
		}
		else
		{
			if($rel['l_id']==$id) { $sel.="selected='selected'"; }
		}
		
		$str.= '<option '.$sel.' value="'.$rel['l_id'].'">'.$rel['l_name'].'</option>';
		
		$cnt++;
	}
	//return $str;
	return $query;
}

function get_company_data($dbcon,$company_id)
{
	$query="select * from tbl_company where company_id=".$company_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	return $rel;
}
function get_cust_data_arr($dbcon,$cust_id)
{
	$query="select * from tbl_ledger where l_id='".$cust_id."'";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	return $rel;
}
function getunit($dbcon,$id)
{
	$str='';
	$query="select `unitid`,`unit_name` from unit_mst where unit_status=0 order by unit_name";
	$rs_country=$dbcon->query($query);	
	$str = '<option value="">Choose Unit</option>';
	while($rel=mysqli_fetch_assoc($rs_country))
	{
		$sel='';
		if($rel['unitid']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['unitid'].'">'.$rel['unit_name'].'</option>';
	}
	return $str;
}	

function getunit_converted($dbcon,$id)
{
	$str='';
	$query="select p.*,u.unit_name from tbl_product_unit as p left join unit_mst as u on u.unitid=p.unit_alt_unit where unit_product='$id'";
	$rs_country=$dbcon->query($query);	
	$str = '<option value="">Choose Unit</option>';
	while($rel=mysqli_fetch_assoc($rs_country))
	{
		$sel='';
		if($rel['unit_alt_unit']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['unit_alt_unit'].'">'.$rel['unit_name'].'</option>';
	}
	return $str;
}	

function get_country($dbcon,$id)
{
	$str='';
	$query="select `countryid`,`country_name` from country_mst where country_status=0 order by country_name";
	$rs_country=$dbcon->query($query);	
	$str = '<option value="">Choose Country</option>';
	while($rel=mysqli_fetch_assoc($rs_country))
	{
		$sel='';
		if($rel['countryid']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['countryid'].'">'.$rel['country_name'].'</option>';
	}
	return $str;
}
function addDayswithdate($date,$days){
	$date = strtotime("+".$days." days", strtotime($date));
	return  date("Y-m-d", $date);
}
function getquestion($dbcon,$id,$cond)
{
	$query="select * from tbl_question where status=0 ";
	$rs_cust=$dbcon->query($query);	
	$q= '<option value="">Choose Your Security Question </option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($rel['question_id']==$id)
		{
			$sel="selected='selected'";
		}
		$q .='<option '.$sel.' value="'.$rel['question_id'].'">'.$rel['question'].'</option>';
	}
	return $q;
}

function getusertype($dbcon,$sid,$con)
{
	$usertype='';
	$qry="select * from tbl_usertype where status=0 ".$con;
	$rs_type=$dbcon->query($qry);	
	//$usertype .='<option value="" selected="selected">Choose User Type</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$sel='';
		if($row['usertype_id']==$sid)
		{$sel='selected="selected"';}
		$usertype .= '<option '.$sel.' value="'.$row['usertype_id'].'">'.$row['usertype_name'].'</option>';
	}
	return $usertype;
}


function getalluser($dbcon,$sid)
{
	$usertype='';
	$qry="select usr.user_id,usr.user_name,type.usertype_name from users as usr
	left join tbl_usertype as type on type.usertype_id=usr.user_type
	where usr.active=0 and usr.user_type!=1 and usertype_name IS NOT NULL" ;
	$rs_type=$dbcon->query($qry);	
	//$usertype .='<option value="" selected="selected">Choose User Type</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		// $sel='';
		if($row['user_id']==$sid)
		{$sel='selected="selected"';}
		$usertype .= '<option '.$sel.' value="'.$row['user_id'].'">'.$row['user_name'].' - '.$row['usertype_name'].'</option>';
	}
	return $usertype;
}

function getmenu($dbcon,$sid)
{
	$menu='';
	$qry="select * from tbl_menu where status=0 and pid=0";
	$rs_menu=$dbcon->query($qry);	
	$menu .='<option value="" selected="selected">Choose Menu</option>';
	while($row=mysqli_fetch_assoc($rs_menu))
	{	
		$sel='';
		if($row['menu_id']==$sid)
		{$sel='selected="selected"';}
		$menu .= '<option '.$sel.' value="'.$row['menu_id'].'">'.$row['menu_name'].'</option>';
	}
	return $menu;
}
function get_state_all($dbcon,$sid,$cid)
{
	$qry="select * from state_mst where state_status=0 and countryid=".$cid;
	$rs_state=$dbcon->query($qry);		
	$str='';
	$sid=explode(",",$sid);
	$str.= '<option value="" disabled>Choose State</option>';
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		$sel='';
		if(in_array($row['stateid'],$sid))
		{ $sel='selected="selected"'; }
		$str.='<option '.$sel.' value="'.$row['stateid'].'">'.$row['state_name'].'</option>';
	}
	return $str;
}
function get_state($dbcon,$sid,$cid)
{
	$qry="select * from state_mst where state_status=0 and countryid=".$cid;
	$rs_state=$dbcon->query($qry);		
	$str='';
	$str.= '<option value="">Choose State</option>';
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		$sel='';
		if($row['stateid']==$sid)
		{ $sel='selected="selected"'; }
		$str.='<option '.$sel.' value="'.$row['stateid'].'">'.$row['state_name'].'</option>';
	}
	return $str;
}
function getstate($dbcon,$sid)
{
	$qry="select * from state_mst where state_status=0";
	$rs_state=$dbcon->query($qry);		
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		$sel='';
		if($row['stateid']==$sid)
		{$sel='selected="selected"';}
		echo '<option '.$sel.' value="'.$row['stateid'].'">'.$row['state_name'].'</option>';
	}
}
function getcity($dbcon,$sid,$cid)
{
	$city='';
	$c_qry="select * from city_mst where city_status=0 and stateid=".$sid.' order by city_name';
	$rs_city=$dbcon->query($c_qry);	
	$city.= '<option value="">Choose City</option>';	
	while($r=mysqli_fetch_assoc($rs_city))
	{	
		// $sel='';	
		if($r['cityid']==$cid)
		{$sel='selected="selected"';}
		$city .= '<option '.$sel.' value="'.$r['cityid'].'">'.$r['city_name'].'</option>';
	}						
	return $city;								
}

function get_city_all($dbcon,$sid,$cid)
{
	$qry="select * from city_mst where city_status=0 and stateid in(".$cid.")";
	$rs_state=$dbcon->query($qry);		
	$str='';
	$sid=explode(",",$sid);
	$str.= '<option value="" disabled>Choose City</option>';
	while($row=mysqli_fetch_assoc($rs_state))
	{	
		// $sel='';
		if(in_array($row['cityid'],$sid))
		{ $sel='selected="selected"'; }
		$str.='<option '.$sel.' value="'.$row['cityid'].'">'.$row['city_name'].'</option>';
	}
	return $str;
}
function getcity_all($dbcon,$cid)
{
	$city='';
	$c_qry="select * from city_mst where city_status=0 order by city_name";
	$rs_city=$dbcon->query($c_qry);	
	$city.= '<option value="">Choose City</option>';	
	while($r=mysqli_fetch_assoc($rs_city))
	{	
		// $sel='';	
		if($r['cityid']==$cid)
		{$sel='selected="selected"';}
		$city .= '<option '.$sel.' value="'.$r['cityid'].'">'.$r['city_name'].'</option>';
	}						
	return $city;								
}

function get_ledger_bank($dbcon,$ledger_id){
	$str='';
	
	$query="select * from tbl_ledger as pro where l_status=0 and l_form in ('bank_form','cash') and company_id = $_SESSION[company_id] order by TRIM(l_name) ASC";
	$rs_dispatch=$dbcon->query($query);	
	$str .= '<option value="">--Select Account--</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		// $sel=''; 
		if($rel['l_id']==$ledger_id)
		{$sel ="selected='selected'";}
		$str .= '<option '.$sel.' value="'.$rel['l_id'].'">'.$rel['l_name'].'</option>';
	}
	return $str;
}
function get_pay_ledger($dbcon,$id)
{   
	$str='';
	//$query="select cust.cust_id,cust.company_name from tbl_customer as cust where cust_status=0 and cust.company_id in (0,$_SESSION[company_id])";
	
	$query="select l.l_id,l.l_name,c.city_name from tbl_ledger as l left join city_mst as c on c.cityid=l.cityid where l.l_status=0 and l.l_form in('customer_form','emp_form') and l.company_id in (0,$_SESSION[company_id])";
	$rs_cust=$dbcon->query($query);	
	$str= '<option value="">Choose Company</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($rel['l_id']==$id) { $sel="selected='selected'"; }
		$str.= '<option '.$sel.' value="'.$rel['l_id'].'">'.$rel['l_name'].' ( '.$rel['city_name'].' )'.'</option>';
	}
	return $str;
} 
/*
 *  added by Dimple Panchal 30-sep-2020
 *  when inquiry added from india mart, customer got created, but was not showing in inquiry edit.
 */
function getcustomer($dbcon,$id){
        $str='';
	$query="select cust.cust_id,cust.cust_name from tbl_customer as cust 
            where cust_status=0 and cust.company_id in (0,$_SESSION[company_id])";
	
	$rs_cust=$dbcon->query($query);	
	$str .= '<option value="">Choose Company</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($rel['cust_id']==$id) { $sel="selected='selected'"; }
		$str.= '<option '.$sel.' value="'.$rel['cust_id'].'">'.$rel['cust_name'].'</option>';
	}
	return $str;
}
function getcust($dbcon,$id,$group_id)
{   
	$str='';
	//$query="select cust.cust_id,cust.company_name from tbl_customer as cust where cust_status=0 and cust.company_id in (0,$_SESSION[company_id])";
	// $where="";
	if(!empty($group_id)){
		$where=" and l_group=".$group_id;
	}
	
	$query="select l.l_id,l.l_name,c.city_name from tbl_ledger as l left join city_mst as c on c.cityid=l.cityid where l.l_status=0 ".$where." and l.l_form='customer_form' and l.company_id in (0,$_SESSION[company_id])";
	$rs_cust=$dbcon->query($query);	
	$str= '<option value="">Choose Company</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		// $sel='';
		if($rel['l_id']==$id) { $sel="selected='selected'"; }
		$str.= '<option '.$sel.' value="'.$rel['l_id'].'">'.$rel['l_name'].' ( '.$rel['city_name'].' )'.'</option>';
	}
	return $str;
	//return $query;
} 
/*Code By Umair: For Currency*/
function getcurrency($dbcon,$cid)
{   
	$query="select * from currency_mst where currency_status=0";
	$rs_cust=$dbcon->query($query);
	echo '<option value="">Choose Currency</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($rel['currencyid']==$cid)
		{
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['currencyid'].'">'.$rel['currency_name'].'</option>';
	}

} 
function getreportcust($dbcon,$id)
{	
	$query="select * from tbl_customer where cust_status=0 and company_id in (0,$_SESSION[company_id])";
	$rs_cust=$dbcon->query($query);	
	echo '<option value="">All Company</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($rel['cust_id']==$id)
		{
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['cust_id'].'">'.$rel['company_name'].'</option>';
	}
	
}

function getbalance_type($dbcon,$id) {
	$query="select * from mst_balance_type where status=0";
	$rs_cust=$dbcon->query($query);	
	echo '<option value="">Select Type</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($rel['balance_typeid']==$id) {
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['balance_typeid'].'">'.$rel['balance_type_name'].'</option>';
	}
}


// get Status From Followup Table

function getFollowupStatus($dbcon,$id)
{
	$query="select * from tbl_followup_status where f_id=$id";
	$row_fo=$dbcon->query($query);
	$rel=mysqli_fetch_array($row_fo);
	return $rel['f_status_name'];
}


// get start status id

function getFollowupStatusId($dbcon)
{
	$query="select * from tbl_followup_status where f_status_name='Start'";
	$row_fo=$dbcon->query($query);
	$rel=mysqli_fetch_array($row_fo);
	return $rel['f_id'];
}

//get all Followup Status

function getAllStatus($dbcon,$id) {
	$query="select * from tbl_followup_status where f_status=0";
	$rs_cust=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		// $sel='';
		if($rel['f_id']==$id) {
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['f_id'].'">'.$rel['f_status_name'].'</option>';
	}
}

function get_ledger_accounts($dbcon,$id) {
	$query="select l.l_id,l.l_name,g.g_name from tbl_ledger as l
	left join tbl_group as g on g.g_id=l.l_group
	where l.l_status=0 ";
	$rs_cust=$dbcon->query($query);
	$str='<option value="">Choose Ledger Account</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($rel['l_id']==$id) {
			$sel="selected='selected'";
		}
		$str.= '<option '.$sel.' value="'.$rel['l_id'].'">'.$rel['l_name'].' - '.$rel['g_name'].'</option>';
	}
	return $str;
}
//get All Employee


function getAllEmployee($dbcon,$id) {
	$query="select l_id,l_name from tbl_ledger where l_status=0 and l_form='emp_form'";
	$rs_cust=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		// $sel='';
		if($rel['l_id']==$id) {
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['l_id'].'">'.$rel['l_name'].'</option>';
	}
}


//get user type 

function getEmployeeIdUser($dbcon,$id)
{
	$query="select * from users where user_id=$id";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	return $rel['employee_id'];
}

//get employee details 

function getEmployeeDetail($dbcon,$id)
{
	$query="select * from employee_mst where employee_id=$id";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	return $rel;
}

// get Complain Details

function getComplainDetail($dbcon,$id)
{
	$query="select comp.complaint_id,comp.complaint_no,comp.complaint_date,comp.cust_id,comp.complaint_type_id,comp.cdate,comp.complaint_status,comp.old_sp_part_status,comp.sp_part_status,comp.followup_status,comp.emp_id,l.l_name,comty.complaint_type_name,f.f_status_name,l.m_address,l.cust_mobile from tbl_complaint as comp inner join tbl_ledger as l on comp.cust_id=l.l_id inner join complaint_type_mst as comty on comp.complaint_type_id=comty.complaint_type_id inner join tbl_followup_status as f on comp.followup_status=f.f_id where comp.complaint_id=$id";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	return $rel;
}

//get All Status Filter 

function getAllStatus_filter($dbcon,$where,$id) {
	$query="select * from tbl_followup_status where f_status=0 ".$where;
	$rs_cust=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($rel['f_id']==$id) {
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['f_id'].'">'.$rel['f_status_name'].'</option>';
	}
}

//get all Product 

function load_all_product($dbcon,$where)
{
	// $sel='';
	$query="select * from product_mst where product_status='0'".$where;
	$rs_product=$dbcon->query($query);
	
	while($rsp=mysqli_fetch_assoc($rs_product))
	{
		echo '<option value="'.$rsp['product_id'].'">'.$rsp['product_name'].'</option>';
	}
}

function load_all_complain_product($dbcon,$id)
{
	$sel='';
	$query="select comp.*,p.product_name from tbl_complaint_trn as comp inner join product_mst as p on p.product_id=comp.product_id where comp.complaint_id=$id";
	$rs_product=$dbcon->query($query);
	
	while($rsp=mysqli_fetch_assoc($rs_product))
	{
		echo '<option '.$sel.' value="'.$rsp['product_id'].'">'.$rsp['product_name'].'</option>';
	}
}

//get All Expense

function get_expense($dbcon,$id)
{
	$query="select * from expense_mst where expense_status=0";
	$rs_expense=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_expense))
	{	
		$sel='';
		if($rel['expense_id']==$id) {
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['expense_id'].'">'.$rel['expense_name'].'</option>';
	}
}

//get all Complain

function get_all_complain($dbcon,$id,$where)
{
	
	$query="select * from tbl_complaint where complaint_status=0".$where;
	$rs_expense=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_expense))
	{	
		$sel='';
		if($rel['complaint_id']==$id) {
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['complaint_id'].'">'.$rel['complaint_no'].'</option>';
	}
	
}

//get Employee From User 

function getAllEmployeeUser($dbcon)
{
	$query="select * from users where employee_id>0";
	$rs_cust=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($rel['user_id']==$id) {
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['user_id'].'">'.$rel['user_name'].'</option>';
	}
}

//get total Spare Part count by complaint id

function get_total_spare_count($dbcon,$id)
{
	$query="select * from tbl_complain_spare_part where s_comp_id='$id'";
	$rs_comp=$dbcon->query($query);
	$count=mysqli_num_rows($rs_comp);
	return $count;
}


//get total old Spare Part count by complaint id

function get_total_spare_count_old($dbcon,$id)
{
	$query="select * from tbl_complain_close_spare_part where sc_comp_id='$id'";
	$rs_comp=$dbcon->query($query);
	$count=mysqli_num_rows($rs_comp);
	return $count;
}

//get total old Spare Part count by complaint id

function get_total_spare_count_request($dbcon,$id)
{
	$query="select * from tbl_complain_spare_part where s_comp_id='$id' and s_status='2'";
	$rs_comp=$dbcon->query($query);
	$count=mysqli_num_rows($rs_comp);
	return $count;
}

function get_product_count_close($dbcon,$id)
{
	$query="select complaint_trn_status,complaint_id from tbl_complaint_trn where complaint_id='$id' and close_status='0'";
	$rs_comp=$dbcon->query($query);
	$count=mysqli_num_rows($rs_comp);
	return $count;
}

function get_rate_product($dbcon,$id)
{
	$query="select product_rate,product_id from product_mst where product_id='$id'";
	$rs_comp=$dbcon->query($query);
	$row=mysqli_fetch_array($rs_comp);
	return $row['product_rate'];
}

function getinvoicetype($dbcon,$id)
{
	$query="select * from tbl_invoicetype where status=0 and company_id=".$_SESSION['company_id'];
	$rs_dispatch=$dbcon->query($query);	
	echo '<option value="" selected="selected">Choose Invoice Type</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel='';
		if($rel['invoicetype_id']==$id)
		{
			$sel='selected="selected"';
		}
		echo '<option '.$sel.' value="'.$rel['invoicetype_id'].'">'.$rel['invoice_type'].'</option>';
	}
}


function getpaymentterms($dbcon,$eid){
	 $query="select * from pay_terms where terms_status=0";
	$rs_dispatch=$dbcon->query($query);	
	echo '<option value="">Choose Payment Terms</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel='';
		if($rel['terms_id']==$eid)
		{
			$sel='selected="selected"';
		}
			echo '<option '.$sel.' value="'.$rel['terms_id'].'">'.$rel['payment_days'].' days'.'</option>';
	}
}

function getplaceofsupply($dbcon,$eid){
	 $query="select * from supply_place where supply_place_status=0";
	$rs_dispatch=$dbcon->query($query);	
	echo '<option value="">Choose Place Of Supply</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel='';
		if($rel['supply_place_id']==$eid)
		{
			$sel='selected="selected"';
		}
			echo '<option '.$sel.' value="'.$rel['supply_place_id'].'">'.$rel['place_supply'].'</option>';
	}
}

function getmodeofdispache($dbcon,$eid){
	 $query="select * from mode_of_dispatch where mode_des_status=0";
	$rs_dispatch=$dbcon->query($query);	
	echo '<option value="">Choose Mode Of Dispatch</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel='';
		if($rel['mode_dis_id']==$eid)
		{
			$sel='selected="selected"';
		}
			echo '<option '.$sel.' value="'.$rel['mode_dis_id'].'">'.$rel['mode_dispatch'].'</option>';
	}
}


function get_custmer_consignee($dbcon,$parentid,$id)
{	
	$str='';
	
	$query="select * from tbl_custmer_consignee where cust_status=0 and cust_ref_id=".$parentid." and company_id in (0,$_SESSION[company_id])";
	$rs_dispatch=$dbcon->query($query);	
	$str = '<option value="">Choose Consignee</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		if($rel['cust_id']==$id)
		{$sel ="selected='selected'";}
		$str .= '<option '.$sel.' value="'.$rel['cust_id'].'">'.$rel['company_name'].'</option>';
	}
	return $str;
}

function getproducttype($dbcon,$id)
{
	//pathik add services option
	$pro_tml='';
	$pro_tml.= '<option value="">Choose Product Type</option>';
	$pro_tml.= '<option value="0" '.(($id=="0")?"selected":"").'>Finish Product</option>';
	$pro_tml.= '<option value="1" '.(($id=="1")?"selected":"").'>Assembly Product</option>';
	$pro_tml.= '<option value="2" '.(($id=="2")?"selected":"").'>SUB ASSEMBLY</option>';
	$pro_tml.= '<option value="3" '.(($id=="3")?"selected":"").'>RAW MATERIA</option>';
	$pro_tml.= '<option value="4" '.(($id=="4")?"selected":"").'>FINISH PART</option>';
	$pro_tml.= '<option value="5" '.(($id=="5")?"selected":"").'>BOI</option>';
	$pro_tml.= '<option value="6" '.(($id=="6")?"selected":"").'>CAPITAL GOODS</option>';
	$pro_tml.= '<option value="7" '.(($id=="7")?"selected":"").'>CONSUMABLE</option>';
	$pro_tml.= '<option value="8" '.(($id=="8")?"selected":"").'>Services</option>';
	return $pro_tml;
}
function getformula($dbcon,$id)
{
	$formula_qry="select * from  formula_mst where formula_status=0 and company_id=".$_SESSION['company_id'];
	$rs_formula=$dbcon->query($formula_qry);	
	echo '<option value="">Choose Formula</option>';
	while($formula=mysqli_fetch_assoc($rs_formula))
	{	
		$sel='';
		if($formula['formulaid']==$id)
		{
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$formula['formulaid'].'">'.$formula['formula_name'].'</option>';
	}

}

function get_product_tax_common($dbcon,$product_amount,$formulaid)
{
	$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
	$row=$dbcon->query($qry);
	$rate_total=$total=$product_amount;
	$i=1;$tax_amt_total=0;$tax_total_amount=0;
	while($tax=mysqli_fetch_assoc($row))
	{	
		$infpotrn['tax_name'.$i]=$tax['tax_name'];
		$infpotrn['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
		$rate_total+=$tax_amount;
		$tax_amt_total+=$infpotrn['tax_amount'.$i];
		$tax_total_amount+=$tax['tax_value'];
		$i++;
	}
	for($j=$i;$j<=3;$j++)
	{
		$infpotrn['tax_name'.$i]='';
		$infpotrn['tax_amount'.$i]='';		
	}
	$infpotrn['total']=$rate_total;
	$infpotrn['tax_total']=$tax_amt_total;
	$infpotrn['tax_total_amount']=$tax_total_amount;
	return $infpotrn;
}

function get_pro_type_name($product_type){
	switch($product_type){
		case "0":
			$pro_type_name="Finish Product";
			break;
		case "1":
			$pro_type_name="Assembly Product";
			break;
		case "2":
			$pro_type_name="SUB ASSEMBLY";
			break;
		case "3":
			$pro_type_name="Raw Material";
			break;
		case "4":
			$pro_type_name="FINISH PART";
			break;
		case "5":
			$pro_type_name="BOI";
			break;
		case "6":
			$pro_type_name="CAPITAL GOODS";
			break;
		case "7":
			$pro_type_name="CONSUMABLE";
			break;
		case "8":
			$pro_type_name="Service";
			break;
		default:
			$pro_type_name="";
	}
	return $pro_type_name;
}

function getlistinvoicetype($dbcon,$id)
{
	$query="select * from tbl_invoicetype where status=0 and type_id=1 and company_id=".$_SESSION['company_id'];
	$rs_dispatch=$dbcon->query($query);	
	echo '<option value="" selected="selected">All</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		// $sel='';
		if($rel['invoicetype_id']==$id)
		{
			$sel='selected="selected"';
		}
		echo '<option '.$sel.' value="'.$rel['invoicetype_id'].'">'.$rel['invoice_type'].'</option>';
	}
}

function get_acc_type($dbcon,$parentid,$id)
{
	$str='';
	$query="SELECT * FROM `mst_accounts` where edit_status=1 and status!=2 and acc_type_id=0";
	$rs_acc=$dbcon->query($query);	
	$str = '<option value="">Choose Account Type</option>';
	while($rel_acc=mysqli_fetch_assoc($rs_acc))
	{
		$str .= '<optgroup label="'.$rel_acc['account_name'].'" data-select2-id="'.$rel_acc['accountid'].'">';
		$query="SELECT * FROM `mst_accounts` as acc where acc.status=0 and edit_status=1   and acc_type_id=".$rel_acc['accountid'];
		$rs_dispatch=$dbcon->query($query);	
		
		while($rel=mysqli_fetch_assoc($rs_dispatch))
		{	
			// $sel=''; 
			if($rel['accountid']==$id)
			{$sel ="selected='selected'";}
			$str .= '<option '.$sel.' value="'.$rel['accountid'].'">'.stripslashes($rel['account_name']).'</option>';
		}
		$str .= '</optgroup>';
	}
	return $str;
	
}

function get_voucher_type_list_common($eid,$dbcon)
{
	$str='';
	$query="Select * from mst_accounts_voucher_type where voucher_status!=2";
	$rs_type=$dbcon->query($query);	
	$str ='<option value="" selected="selected">Choose</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		// $sel='';
		if($row['voucher_typeid']==$eid)
		{$sel='selected="selected"';}
		$str .= '<option '.$sel.' value="'.$row['voucher_typeid'].'">'.$row['voucher_type_name'].'</option>';
	}
	return $str;
}


function get_all_acc_type($dbcon,$id,$condition='')
{
	$str='';
	$query="SELECT * FROM `mst_accounts` where edit_status=1 and status!=2 and acc_type_id!=0 ".$condition;
	$rs_acc=$dbcon->query($query);	
	$str = '<option value="">Choose Account</option>';
	while($rel_acc=mysqli_fetch_assoc($rs_acc))
	{
		$str .= '<optgroup label="'.$rel_acc['account_name'].'" data-select2-id="'.$rel_acc['accountid'].'">';
		$query="SELECT * FROM `mst_accounts` as acc where acc.status!=2 and edit_status=0   and acc_type_id=".$rel_acc['accountid'];
		$rs_dispatch=$dbcon->query($query);	
		
		while($rel=mysqli_fetch_assoc($rs_dispatch))
		{	
			// $sel=''; 
			if($rel['accountid']==$id)
			{$sel ="selected='selected'";}
			$str .= '<option '.$sel.' value="'.$rel['accountid'].'">'.stripslashes($rel['account_name']).'</option>';
		}
		$str .= '</optgroup>';
	}
	
	return $str;
	
}

function get_accounts_typewise($dbcon,$id,$typeid)
{
	$str='';
	$query="SELECT acc.* FROM `mst_accounts` as acc left join mst_accounts as parentacc on parentacc.accountid=acc.acc_type_id left join mst_acc_type as acctype on parentacc.acc_type_id=acctype.acc_type_id where acc.status=0 and acc.view_status=0 and acctype.acc_type_name='".$typeid."' and acc.company_id in (0,$_SESSION[company_id]) order by acc.account_name";
	$rs_dispatch=$dbcon->query($query);	
	$str= '<option value="">Choose Account Type</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		// $sel=''; 
		if($rel['accountid']==$id)
		{$sel ="selected='selected'";}
		$str .= '<option '.$sel.' value="'.$rel['accountid'].'">'.$rel['account_name'].'</option>';
	}
	return $str;
	
}

function get_opening_balance($acc_id,$dbcon,$acc_type)
{
$query="SELECT opn_balance,
(select sum(tran_amount) from tbl_banktransaction where debit_accid=".$acc_id." and status=0) as debit 
,(SELECT sum(amount)  FROM `tbl_passbookentry` where acc_id=".$acc_id." and status=0 and typeid=1) as pdebit,(select sum(tran_amount) from tbl_banktransaction where credit_accid=".$acc_id."  and status=0) as credit ,(SELECT sum(amount)  FROM `tbl_passbookentry` where acc_id=".$acc_id." and status=0 and typeid=2) as pcredit
FROM `account_mst` where acc_id=".$acc_id." and acc_status=0 and company_id=".$_SESSION['company_id']." and acc_type=".$acc_type;
$rel=mysqli_fetch_assoc($dbcon->query($query));	
$opn_balance=$rel['opn_balance']+$rel['credit']+$rel['pcredit']-($rel['debit']+$rel['pdebit']);
	return $opn_balance;
}

function getaccount($dbcon,$bankid,$condition)
{
	$bank='';
	$qry="SELECT acc_id,bank_name,branch_name,acc_number,acc_name FROM `account_mst` as accmst left join bank_mst as bmst on bmst.bankid=accmst.bankid where acc_status=0 and accmst.company_id=".$_SESSION['company_id']." and ".$condition;
	return $qry;exit;
	$rs_type=$dbcon->query($qry);	
	$bank .='<option value="">Choose Account</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		// $sel='';
		if($row['acc_id']==$bankid)
		{$sel='selected="selected"';}
		$bank .= '<option '.$sel.' value="'.$row['acc_id'].'">'.$row['acc_name'].' ('.$row['bank_name'].' - '.$row['branch_name'].')</option>';
	}
	return $bank;
}

function getpaymentmode($dbcon,$id)
{	
	$str='';
	$query="select * from tbl_payment_mode where status=0";
	$rs_payment=$dbcon->query($query);	
	echo '<option value="">Choose Mode</option>';
	while($rel=mysqli_fetch_assoc($rs_payment))
	{
		// $sel='';
		if($rel['paymentmodeid']==$id)
		{
			$sel="selected='selected'";
		}
		$str .= '<option '.$sel.' value="'.$rel['paymentmodeid'].'">'.$rel['payment_mode'].'</option>';
	}
	return $str;
}


function get_product_tax_income($dbcon,$product_amount,$formulaid,$type='exclusive')
{
	$qry="SELECT formula.*,tax.*,(select sum(tax_value)   FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid.") as tax_total  FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
	$row=$dbcon->query($qry);
	$rate_total=$total=$product_amount;
	$i=1;
	while($tax=mysqli_fetch_assoc($row))
	{	
		$info['tax_name'.$i]=$tax['tax_name'];
		if($type=='exclusive')
		{
			$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
			$rate_total+=$tax_amount;
		}
		else if($type=='inclusive')
		{
			$tax_amount=$total-(($total*100)/(100+$tax['tax_total']));
			$tax_amount=$tax_amount/2;
			$info['tax_amount'.$i]=$tax_amount;
			$rate_total-=$tax_amount;
		}
		$info['tax_name'][]=$tax['tax_name'];
		$info['tax_amount'][]=$tax_amount;	
		$i++;
	}
	for($j=$i;$j<=3;$j++)
	{
		$info['tax_name'.$i]='';
		$info['tax_amount'.$i]='';		
	}
	$info['total']=$rate_total;
	return $info;
}

function map_arr($a, $b,$specifiar) 
{ return $a . ' : ' . $b; }



function add_product_tax_data($mstid,$trn_col_name,$emode,$rs_tax,$dbcon)
{		
	$info_tax['mst_id']=$mstid;
	$info_tax['tax_for']=$emode;
	while($rel_tax=mysqli_fetch_assoc($rs_tax))
	{
		$info_tax['trn_id']			= $rel_tax[$trn_col_name];
		$info_tax['tax_id']			= $rel_tax['tax_id'];
		$info_tax['tax_rate']		= $rel_tax['tax_value'];
		$info_tax['tax_amount']		= $rel_tax['product_amount']*$rel_tax['tax_value']/100;
		add_record('tbl_tax_trn', $info_tax, $dbcon);
	}
}

function get_company_cash_accounts($dbcon)
{
	$qry="select acc_id,acc_type from account_mst where acc_type=1 and acc_status=0 and company_id=".$_SESSION['company_id'];
	$rel_acc=mysqli_fetch_assoc($dbcon->query($qry));	
	$acc_id=$rel_acc['acc_id'];
	return $acc_id;
}

function get_p_and_l_stock($startdate,$enddate,$dbcon)
{
	if(isset($enddate) && !empty($enddate))
	{
		$where_date=" between '".$startdate."' and '".$enddate."'";
	}
	else{
		$where_date=" < '".$startdate."'";
	}
	$query="select sum(productavgprice*open_stock_qty) as stock_amount from (select product_id,((product_stock+purchaseqty+creditqty)-(invoiceqty+debitqty)) as open_stock_qty,((stock_amt+purchaseamt)/(product_stock+purchaseqty)) as productavgprice from (SELECT pdt.product_id,pdt.product_stock,pdt.product_stock_rate,(pdt.product_stock*pdt.product_stock_rate) as stock_amt,COALESCE(purchase.purchaseqty,0) as purchaseqty,COALESCE(purchase.purchaseamt,0) as purchaseamt,COALESCE(purdebit.purdebitqty,0) as debitqty,COALESCE(purdebit.purdebitamt,0) as debitamt,COALESCE(invoice.invoiceqty,0) as invoiceqty,COALESCE(invoice.invoiceamt,0) as invoiceamt,COALESCE(innote.creditqty,0) as creditqty,COALESCE(innote.creditamt,0) as creditamt FROM `product_mst` as pdt 
	left join ( SELECT potrn.product_id,sum(product_qty) as purchaseqty,sum(product_amount) as purchaseamt FROM `tbl_pono` as pomst inner join tbl_potrancation as potrn on potrn.po_id=pomst.po_id where pomst.status=0 and po_date  ".$where_date." and pomst.company_id=".$_SESSION['company_id']." group by potrn.product_id) as purchase on pdt.product_id=purchase.product_id
	left join (SELECT product_id,sum(product_qty) as purdebitqty,sum(product_amount) as purdebitamt  FROM `tbl_purchasedebitnote` as pomst inner join tbl_purchasedebitnotetrn as potrn on potrn.purchasedebitnote_id=pomst.purchasedebitnote_id where pomst.debitnote_status=0 and debit_date  ".$where_date." and pomst.company_id=".$_SESSION['company_id']." group by potrn.product_id ) as purdebit on pdt.product_id=purdebit.product_id 
	left join (SELECT product_id,sum(product_qty) as invoiceqty,sum(product_amount)as invoiceamt  FROM `tbl_invoice` as invmst inner join tbl_invoicetrn as invtrn on invmst.invoice_id=invtrn.invoice_id where invmst.invoice_status=0 and invoice_date  ".$where_date." and invmst.company_id=".$_SESSION['company_id']." group by invtrn.product_id ) as invoice on pdt.product_id=invoice.product_id
	left join (SELECT product_id,sum(product_qty) as creditqty,sum(product_amount) as creditamt  FROM `tbl_invoicenote` as invmst inner join tbl_invoicenotetrn as invtrn on invmst.invoicenote_id=invtrn.invoicenote_id where invmst.noteused_status=0 and note_date  ".$where_date." and invmst.company_id=".$_SESSION['company_id']." group by invtrn.product_id ) as innote on pdt.product_id=innote.product_id) as product_data  ) as openstock";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	return $rel['stock_amount'];
}
function get_p_and_l_sale($startdate,$enddate,$dbcon)
{
	if(isset($enddate) && !empty($enddate))
	{
		$where_date=" between '".$startdate."' and '".$enddate."'";
	}
	else{
		$where_date=" < '".$startdate."'";
	}
	$query="select sum(productavgprice) as sale_amount from (select product_id,(invoiceqty-creditqty) as sale_qty,(invoiceamt-creditamt) as productavgprice from (SELECT pdt.product_id,COALESCE(invoice.invoiceqty,0) as invoiceqty,COALESCE(invoice.invoiceamt,0) as invoiceamt,COALESCE(innote.creditqty,0) as creditqty,COALESCE(innote.creditamt,0) as creditamt FROM `product_mst` as pdt 
	left join ( SELECT product_id,sum(product_qty) as invoiceqty,sum(product_amount)as invoiceamt  FROM `tbl_invoice` as invmst inner join tbl_invoicetrn as invtrn on invmst.invoice_id=invtrn.invoice_id where invmst.invoice_status=0 and invoice_date ".$where_date."  and invmst.company_id=".$_SESSION['company_id']." group by invtrn.product_id ) as invoice on pdt.product_id=invoice.product_id
	left join ( SELECT product_id,sum(product_qty) as creditqty,sum(product_amount) as creditamt  FROM `tbl_invoicenote` as invmst inner join tbl_invoicenotetrn as invtrn on invmst.invoicenote_id=invtrn.invoicenote_id where invmst.noteused_status=0 and note_date ".$where_date." and invmst.company_id=".$_SESSION['company_id']." group by invtrn.product_id ) as innote on pdt.product_id=innote.product_id) as product_data  ) as openstock";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	return $rel['sale_amount'];
}
function get_p_and_l_purchase($startdate,$enddate,$dbcon)
{
	if(isset($enddate) && !empty($enddate))
	{
		$where_date=" between '".$startdate."' and '".$enddate."'";
	}
	else{
		$where_date=" < '".$startdate."'";
	}
	$query="select sum(productavgprice*purchase_qty) as purchase_amount from (select product_id,((purchaseqty)-(debitqty)) as purchase_qty,(purchaseamt)/(purchaseqty) as productavgprice from (SELECT pdt.product_id,pdt.product_stock,pdt.product_stock_rate,(pdt.product_stock*pdt.product_stock_rate) as stock_amt,COALESCE(purchase.purchaseqty,0) as purchaseqty,COALESCE(purchase.purchaseamt,0) as purchaseamt,COALESCE(purdebit.purdebitqty,0) as debitqty,COALESCE(purdebit.purdebitamt,0) as debitamt FROM `product_mst` as pdt 
	left join ( SELECT potrn.product_id,sum(product_qty) as purchaseqty,sum(product_amount) as purchaseamt FROM `tbl_pono` as pomst inner join tbl_potrancation as potrn on potrn.po_id=pomst.po_id where pomst.status=0 and po_date ".$where_date." and pomst.company_id=".$_SESSION['company_id']."  group by potrn.product_id) as purchase on pdt.product_id=purchase.product_id
	left join (SELECT product_id,sum(product_qty) as purdebitqty,sum(product_amount) as purdebitamt  FROM `tbl_purchasedebitnote` as pomst inner join tbl_purchasedebitnotetrn as potrn on potrn.purchasedebitnote_id=pomst.purchasedebitnote_id where pomst.debitnote_status=0 and debit_date ".$where_date." and pomst.company_id=".$_SESSION['company_id']." group by potrn.product_id ) as purdebit on pdt.product_id=purdebit.product_id 
	) as product_data  ) as purchase";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	return $rel['purchase_amount'];
}
function get_p_and_l_direct_expense_spare($startdate,$enddate,$dbcon)
{
	if(isset($enddate) && !empty($enddate))
	{
		$where_date=" between '".$startdate."' and '".$enddate."'";
	}
	else{
		$where_date=" < '".$startdate."'";
	}
	$query="SELECT sum(s_amount) as direct_expense FROM `tbl_complain_spare_part` where s_date ".$where_date." and s_status=1 ";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	return $rel['direct_expense'];
}
function get_p_and_l_direct_income($startdate,$enddate,$dbcon)
{
	if(isset($enddate) && !empty($enddate))
	{
		$where_date=" between '".$startdate."' and '".$enddate."'";
	}
	else{
		$where_date=" < '".$startdate."'";
	}
	$query="SELECT cl_date,sum(cl_amount) as direct_income FROM `tbl_complain_close_detail` where cl_date ".$where_date;
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	return $rel['direct_income'];
}

function get_p_and_l_direct_income_spare($startdate,$enddate,$dbcon)
{
	if(isset($enddate) && !empty($enddate))
	{
		$where_date=" between '".$startdate."' and '".$enddate."'";
	}
	else{
		$where_date=" < '".$startdate."'";
	}
	$query="SELECT sc_date,sum(sc_amount) as direct_income FROM `tbl_complain_close_spare_part` where sc_date ".$where_date;
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	return $rel['direct_income'];
}

function get_p_and_l_total_indirect_income($startdate,$enddate,$dbcon)
{
	if(isset($enddate) && !empty($enddate))
	{
		$where_date=" between '".$startdate."' and '".$enddate."'";
	}
	else{
		$where_date=" < '".$startdate."'";
	}
	$query="SELECT inctrn.account_mst_id,account_name,sum(income_amount) as income_amount FROM income_mst as incmst inner join income_trn as inctrn on inctrn.income_mstid=incmst.incomeid 
	left join mst_accounts as mstacc on inctrn.account_mst_id=mstacc.accountid
	where incmst.income_date ".$where_date." and incmst.mst_status=0 and incmst.company_id=".$_SESSION['company_id'];
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	return $rel['income_amount'];
}
function get_p_and_l_total_indirect_expense($startdate,$enddate,$dbcon)
{
	if(isset($enddate) && !empty($enddate))
	{
		$where_date=" between '".$startdate."' and '".$enddate."'";
	}
	else{
		$where_date=" < '".$startdate."'";
	}
	$query="SELECT ex_id,sum(g_total) as expense_amount,expense_date FROM tbl_expense_detail  where expense_date ".$where_date." and expense_approve_status=1 and expense_status=0";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	return $rel['expense_amount'];
}
function get_p_and_l_indirect_expense($startdate,$enddate,$dbcon)
{
	if(isset($enddate) && !empty($enddate))
	{
		$where_date=" between '".$startdate."' and '".$enddate."'";
	}
	else{
		$where_date=" < '".$startdate."'";
	}
	$query="select ed.ex_id,ed.expense_date,sum(e.total) as eamount,e.expense_mstid,e.account_mst_id from tbl_expense_detail as ed inner join expense_trn as e on expense_mstid=ed.ex_id where expense_date ".$where_date." and expense_approve_status=1 and expense_status=0 group by e.account_mst_id";
	$rs=($dbcon->query($query));
	return $rs;
}

function get_p_and_l_cost_of_good_sold($startdate,$enddate,$dbcon)
{
	$where_date=" between '".$startdate."' and '".$enddate."'";
	$where_date1=" < '".$enddate."'";
	$query="select sum(productavgprice*sale_qty) as product_stock_amount from (select product_id,((invoiceqty-creditqty)) as sale_qty,((purchaseamt)/(purchaseqty)) as productavgprice from (SELECT pdt.product_id,pdt.product_stock,pdt.product_stock_rate,(pdt.product_stock*pdt.product_stock_rate) as stock_amt,COALESCE(purchase.purchaseqty,0) as purchaseqty,COALESCE(purchase.purchaseamt,0) as purchaseamt,COALESCE(purdebit.purdebitqty,0) as debitqty,COALESCE(purdebit.purdebitamt,0) as debitamt,COALESCE(invoice.invoiceqty,0) as invoiceqty,COALESCE(invoice.invoiceamt,0) as invoiceamt,COALESCE(innote.creditqty,0) as creditqty,COALESCE(innote.creditamt,0) as creditamt FROM `product_mst` as pdt 
left join ( SELECT potrn.product_id,sum(product_qty) as purchaseqty,sum(product_amount) as purchaseamt FROM `tbl_pono` as pomst inner join tbl_potrancation as potrn on potrn.po_id=pomst.po_id where pomst.status=0 and po_date ".$where_date1." and pomst.company_id=".$_SESSION['company_id']." group by potrn.product_id) as purchase on pdt.product_id=purchase.product_id
left join (SELECT product_id,sum(product_qty) as purdebitqty,sum(product_amount) as purdebitamt  FROM `tbl_purchasedebitnote` as pomst inner join tbl_purchasedebitnotetrn as potrn on potrn.purchasedebitnote_id=pomst.purchasedebitnote_id where pomst.debitnote_status=0 and debit_date ".$where_date1." and pomst.company_id=".$_SESSION['company_id']." group by potrn.product_id ) as purdebit on pdt.product_id=purdebit.product_id 
left join (SELECT product_id,sum(product_qty) as invoiceqty,sum(product_amount)as invoiceamt  FROM `tbl_invoice` as invmst inner join tbl_invoicetrn as invtrn on invmst.invoice_id=invtrn.invoice_id where invmst.invoice_status=0 and invoice_date ".$where_date." and invmst.company_id=".$_SESSION['company_id']." group by invtrn.product_id ) as invoice on pdt.product_id=invoice.product_id
left join (SELECT product_id,sum(product_qty) as creditqty,sum(product_amount) as creditamt  FROM `tbl_invoicenote` as invmst inner join tbl_invoicenotetrn as invtrn on invmst.invoicenote_id=invtrn.invoicenote_id where invmst.noteused_status=0 and note_date ".$where_date." and invmst.company_id=".$_SESSION['company_id']." group by invtrn.product_id ) as innote on pdt.product_id=innote.product_id) as product_data  ) as openstock ";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	return $rel['product_stock_amount'];
}

function get_expense_Complain($dbcon,$id)
{
	$query="select et.expense_trnid,et.expense_mstid,et.account_mst_id,e.account_name  from expense_trn as et left join mst_accounts as e on e.accountid=et.account_mst_id where et.expense_mstid=$id";
	
	$row=$dbcon->query($query);
	
	$ams=array();
	while($rel=mysqli_fetch_assoc($row))
	{
		$ams[]=$rel['account_name'];
	}
	return implode(",",$ams);
}

function get_expense_by_id($dbcon,$id)
{
	$query="select accountid,account_name  from mst_accounts where accountid='$id'";
	
	$row=$dbcon->query($query);
	
	$rel=mysqli_fetch_assoc($row);
	
	return $rel['account_name'];
}

function get_product_detail($dbcon,$id)
{
	$query="select * from product_mst where product_id='$id'";
	
	$row=$dbcon->query($query);
	
	$rel=mysqli_fetch_assoc($row);
	
	return $rel;
}

function get_qty_report($dbcon,$pid,$ctype,$status,$eid,$days)
{
	//$query="select ct.complaint_id,ct.product_id,ct.close_status,c.complaint_type_id,count(product_id) as ct_count from  tbl_complaint_trn as ct left join tbl_complaint as c on c.complaint_id=ct.complaint_id where c.complaint_type_id='$ctype' and ct.product_id='$pid' and ct.close_status='$status'";
	
	$where=''; $where1=''; 
	
	if($status==2)
	{
		$where.=" and f.fl_f_status='2' or f.fl_f_status='3'";
	}
	else
	{
		$where.=" and f.fl_f_status='$status'";
	}
	
	if($days!='')
	{
		$where1.=" and DATE(c.complaint_date) > (NOW() - INTERVAL 30 DAY)";
	}
	
	$query="select count(f.fl_cid) as cid,c.complaint_type_id,c.complaint_status,f.fl_e_id,ct.product_id,c.complaint_date from tbl_follow as f left join tbl_complaint as c on c.complaint_id=f.fl_cid left join tbl_complaint_trn as ct on ct.complaint_id=c.complaint_id where c.complaint_status='0' and ct.product_id='$pid' and f.fl_e_id='$eid' ".$where."  ".$where1."  group by f.fl_f_status";
	
	$row=$dbcon->query($query);
	
	$result=mysqli_fetch_array($row);
	
	return $result['cid'];
}

function get_product_complain($dbcon,$cid)
{
	$qrycust1="select c.complaint_trn_id,c.complaint_id,c.product_id,p.product_name from tbl_complaint_trn as c left join product_mst as p on p.product_id=c.product_id where c.complaint_id=".$cid;
						
	$rowc1=$dbcon->query($qrycust1);
	
	$arr=array();
	while($result1=mysqli_fetch_array($rowc1))
	{
		$arr[]=$result1['product_name'];
	}
						
	return implode(",",$arr);
}

function getvender($dbcon,$id)
{
	$query="select * from tbl_customer where cust_status=0 and company_id in (0,".$_SESSION['company_id'].") and party_type in (0,2) ";
	$rs_cust=$dbcon->query($query);	
	//echo '<option value="">Choose </option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		// $sel='';
		if($rel['cust_id']==$id)
		{
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['cust_id'].'">'.$rel['company_name'].'</option>';
	}
}


function getincome_billno($dbcon,$vendorid,$eid)
{
    $query="select * from  income_mst where mst_status=0 AND g_total>paid_amount AND customerid=".$vendorid;
	$rs_dispatch=$dbcon->query($query);	
	echo '<option value="">Choose Invoice</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		// $sel='';
		if($rel['incomeid']==$eid)
		{
			$sel='selected="selected"';
		}
			echo '<option '.$sel.' value="'.$rel['incomeid'].'">'.$rel['invoice_no'].'</option>';
	}
}

function get_income_customer_due_amount($customerid,$dbcon)
{
 $query="select cust.opening_balance,cust.balance_typeid,(SELECT sum(g_total) FROM `income_mst` as inv where inv.customerid=cust.cust_id and inv.mst_status!=2) as invoice_amount,(SELECT sum(paid_amount) FROM `tbl_receipt` as rec where rec.cust_id=cust.cust_id and rec.status!=2 and receipt_flag='income') as paid_amount from tbl_customer as cust where cust.cust_id=".$customerid;
$rel=mysqli_fetch_assoc($dbcon->query($query));
$op_balance=0;
if(!empty($rel['opening_balance']))
{
	$op_balance=($rel['balance_typeid']=="1"?-($rel['opening_balance']):$rel['opening_balance']);
}
$amount=$op_balance+$rel['invoice_amount']-$rel['paid_amount'];
return $amount;
}

function get_chequeno($acc_id,$dbcon)
{
	$query="SELECT * from account_mst where acc_id=".$acc_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
	return $rel['acc_chequeno'];
}
function get_serise_common($dbcon,$invoicetype)
{
  
    $row=array();
			$query1="select * from  tbl_invoicetype where invoice_type='".$invoicetype."' and company_id='".$_SESSION['company_id']."'";
            //echo $query1;
            $rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
	
           //$id=$rows['Max(taxinvoice_start)']+1;
	
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2'){
				$info['paymentno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1'){
				$info['paymentno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$info['paymentno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
               
			}
			else{
				$info['paymentno']=str_pad($id,3,"0",STR_PAD_LEFT);
			}
  // var_dump($info);
      return $info;
}

function get_all_emp_type($dbcon,$id)
{
	$query="select * from tbl_emp_type_master where etype_status=0 ";
	$rs_cust=$dbcon->query($query);	
	//echo '<option value="">Choose </option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		// $sel='';
		if($rel['etype_id']==$id)
		{
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['etype_id'].'">'.$rel['etype_type'].'</option>';
	}
}

function get_emp_id_transfer($dbcon,$acct)
{
	$query="select accountid,emp_id from mst_accounts where accountid=$acct";
	$rs_cust=$dbcon->query($query);
	$row=mysqli_fetch_array($rs_cust);
	return $row['emp_id'];
	//echo '<option value="">Choose </option>';
}

function get_customer_complain($dbcon,$customer,$id)
{
	$query="select * from tbl_complaint where cust_id='$customer' and pay_status='0'";
	$rs_cust=$dbcon->query($query);	
	echo '<option value="">Choose Complaint</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		// $sel='';
		if($rel['complaint_id']==$id)
		{
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['complaint_id'].'">'.$rel['complaint_no'].'</option>';
	}
}

function get_customer_complain_closed($dbcon,$customer,$id)
{
	
	$query="select c.*,cd.cl_date from tbl_complaint as c left join tbl_complain_close_detail as cd on cd.cl_id=c.complaint_id where c.cust_id='$customer' and c.pay_status='0' and c.followup_status='4' and cd.cl_date < NOW() - 7 ";
	$rs_cust=$dbcon->query($query);	
	echo '<option value="">Choose Complaint</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		// $sel='';
		if($rel['complaint_id']==$id)
		{
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['complaint_id'].'">'.$rel['complaint_no'].'</option>';
	}
}

/*
function get_customer_complain_expense($dbcon,$customer,$id)
{
	$query="select * from tbl_complaint where cust_id='$customer'";
	$rs_cust=$dbcon->query($query);	
	echo '<option value="">Choose Complaint</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($rel['complaint_id']==$id)
		{
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['complaint_id'].'">'.$rel['complaint_no'].'</option>';
	}
}
*/
function get_customer_complain_expense($dbcon,$id,$mode,$cust_id)
{
	$cur_date=date("Y-m-d");$whr='';
	if($cust_id){ $whr.=" and comp.cust_id=$cust_id"; }
	
	//Amish Soni 04-09-2020
	if($_SESSION['user_type']=='3' && $mode=='Add'){
		$emp_id=getEmployeeIdUser($dbcon,$_SESSION['user_id']);
		// $query="SELECT comp.complaint_id,comp.complaint_no,ledger.l_name FROM `tbl_follow` as flp
		// 	inner join tbl_complaint as comp on comp.complaint_id=flp.fl_cid
		// 	left join tbl_ledger as ledger on ledger.l_id=comp.cust_id
		// 	where comp.complaint_status=0 and flp.fl_e_id=$emp_id $whr and DATE(flp.fl_date) > (NOW() - INTERVAL 7 DAY) group by flp.fl_cid ";
		$whr .= " and flp.fl_e_id=$emp_id and DATE(flp.fl_date) > (NOW() - INTERVAL 7 DAY) ";
	}
	else{
		// $query="SELECT comp.complaint_id,comp.complaint_no,ledger.l_name FROM `tbl_follow` as flp
		// 	inner join tbl_complaint as comp on comp.complaint_id=flp.fl_cid
		// 	left join tbl_ledger as ledger on ledger.l_id=comp.cust_id
		// 	where comp.complaint_status=0 $whr group by flp.fl_cid ";
	}

	//display data only if current date is less than (mdate + 7 days)
	$query = "SELECT comp.complaint_id,comp.complaint_no,ledger.l_name FROM `tbl_follow` as flp
			inner join tbl_complaint as comp on comp.complaint_id=flp.fl_cid
			left join tbl_ledger as ledger on ledger.l_id=comp.cust_id
			where comp.complaint_status=0 $whr AND DATE(NOW()) <= DATE(comp.mdate + INTERVAL 7 DAY) 
			group by flp.fl_cid ";
	
	//$query="select * from tbl_complaint where complaint_status=0 and DATE(complaint_date) > (NOW() - INTERVAL 7 DAY) ";
	$rs_cust=$dbcon->query($query);	
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($rel['complaint_id']==$id)
		{
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['complaint_id'].'">'.$rel['complaint_no'].' - '.$rel['l_name'].'</option>';
	}
}

function get_complain_payment_pending($dbcon,$complain)
{
	$query="select  sum(paid_amount) as total,bill_id  from complain_payment_trn  where bill_id='$complain'";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_assoc($rs_cust);
	$paid=$rel['total'];
	
	/*$query1="select  sum(total_amount) as total_due,bill_id  from payment_trn where bill_id='$complain'";
	$rs_cust1=$dbcon->query($query1);	
	$rel1=mysqli_fetch_assoc($rs_cust1);
	$due=$rel1['total_due'];*/
	
	$service_charge=get_service_charge($dbcon,$complain);
	$spare_charge=get_spare_part_rate($dbcon,$complain);
	$due=$service_charge+$spare_charge;
	
	$grand_total=$due-$paid;
	
	return $grand_total;
	
}

function get_all_acc_type_emp($dbcon,$empid)
{
	$query="select emp_id,accountid from mst_accounts where emp_id='$empid'";
	$row=$dbcon->query($query);
	$rel=mysqli_fetch_array($row);
	return $rel['accountid'];
}

function get_all_group($dbcon,$id,$where='',$primary)
{
	$str='';
	$query="Select * from tbl_group where g_status=0 ".$where;
	$rs_type=$dbcon->query($query);
    if($id=='0'){ $psel='selected="selected"';}
	$str ='<option value="" >--Choose Group--</option>';
	if($primary!='0')
	{
	$str.='<option value="0" '.$psel.' >PRIMARY</option>';
	}
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$sel='';
		if($row['g_id']==$id)
		{$sel='selected="selected"';}
		
		$str .= '<option '.$sel.' value="'.$row['g_id'].'">'.$row['g_name'].'</option>';
	}
	return $str;
}


function get_grp_by_id($dbcon,$id)
{
	$query="select * from tbl_group where g_id='$id'";
	$row=$dbcon->query($query);
	$rel=mysqli_fetch_array($row);
	return $rel['g_name'];
}

function get_sales_order($dbcon,$id)
{	
	$str='';
	
	$query="select * from tbl_sales_order where sales_order_status=0 and cust_id=".$id." and company_id=".$_SESSION['company_id'];
	$rs_dispatch=$dbcon->query($query);	
	$str = '<option value="">Choose Sales Order</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		/*if($rel['sales_order_id']==$id)
		{$sel ="selected='selected'";}*/
		$str .= '<option '.$sel.' value="'.$rel['sales_order_id'].'">'.$rel['sales_order_no'].'</option>';
	}
	return $str;
}
function get_all_expense($dbcon,$id)
{
	$query="select l_id,l_name from tbl_ledger  where l_status=0 and l_form='expense_form' and company_id in (0,$_SESSION[company_id])";
	$rs_expense=$dbcon->query($query);
	echo '<option value="">--Select Expense--</option>';
	while($rel=mysqli_fetch_assoc($rs_expense))
	{	
		$sel='';
		if($rel['l_id']==$id) {
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['l_id'].'">'.$rel['l_name'].'</option>';
	}
}

function get_ledger_expense_by_id($dbcon,$id)
{
	$query="select l_id,l_name from tbl_ledger where l_id='$id'";
	$row=$dbcon->query($query);
	$rel=mysqli_fetch_array($row);
	return $rel['l_name'];
}

function get_total_qty_by_po($dbcon,$id)
{
	$query="select sum(product_qty) as pqty from tbl_potrancation where po_id='$id'";
	$row=$dbcon->query($query);
	$rel=mysqli_fetch_array($row);
	return $rel['pqty'];
}

function get_group_from_expense($dbcon,$id)
{
	$query="select e.expense_id,e.expense_head_id,g.g_id from expense_mst as e left join tbl_group as g  on e.expense_head_id=g.g_id where e.expense_id='$id'";
	$row=$dbcon->query($query);
	$rel=mysqli_fetch_array($row);
	return $rel['g_id'];
}

function get_product_tax_common_expense($dbcon,$product_amount,$formulaid,$type='exclusive')
{
	$qry="SELECT formula.*,tax.*,(select sum(tax_value)   FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid.") as tax_total  FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
	$row=$dbcon->query($qry);
	$rate_total=$total=$product_amount;
	$i=1;
	while($tax=mysqli_fetch_assoc($row))
	{	
		$info['tax_name'.$i]=$tax['tax_name'];
		if($type=='exclusive')
		{
			$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
			$rate_total+=$tax_amount;
		}
		else if($type=='inclusive')
		{
			$tax_amount=$total-(($total*100)/(100+$tax['tax_total']));
			$tax_amount=$tax_amount/2;
			$info['tax_amount'.$i]=$tax_amount;
			$rate_total-=$tax_amount;
		}
		$info['tax_name'][]=$tax['tax_name'];
		$info['tax_amount'][]=$tax_amount;	
		$i++;
	}
	for($j=$i;$j<=3;$j++)
	{
		$info['tax_name'.$i]='';
		$info['tax_amount'.$i]='';		
	}
	$info['total']=$rate_total;
	return $info;
}

function get_income_account($dbcon,$id)
{
	$query="select * from income_master where inc_status=0";
	$rs_expense=$dbcon->query($query);	
	echo '<option value="">--Select Income--</option>';
	while($rel=mysqli_fetch_assoc($rs_expense))
	{	
		$sel='';
		if($rel['inc_id']==$id) {
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['inc_id'].'">'.$rel['inc_name'].'</option>';
	}
}

function get_group_from_income($dbcon,$id)
{
	$query="select inc_id,inc_group from income_master where inc_id='$id'";
	$row=$dbcon->query($query);
	$rel=mysqli_fetch_array($row);
	return $rel['inc_group'];
}

function load_cust_prowise_model_invoice($dbcon,$id,$product_id,$cust_id) {
	$str='';
	$query="select sold_pro.model_id,model.model_name from tbl_cust_sold_pro as sold_pro
		inner join model_mst as model on model.model_id=sold_pro.model_id
		where cust_sold_pro_status=0  and sold_pro.product_id=".$product_id." and sold_pro.company_id in(0,$_SESSION[company_id]) ";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Model</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['model_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['model_id'].'">'.$rel['model_name'].'</option>';
	}
	return $str;
}

function get_expense_by_invoice($dbcon,$id)
{
	$query="select sum(exp_e_amount) as sum_amount from tbl_invoice_exp where exp_in_id='$id'";
	$row=$dbcon->query($query);
	$rel=mysqli_fetch_array($row);
	return $rel['sum_amount'];
}

function get_all_category($dbcon,$id,$where='')
{
	$str='';
	$query="Select * from tbl_category where cat_status=0 ".$where;
	$rs_type=$dbcon->query($query);
    if($id=='0'){ $psel='selected="selected"';}
	$str ='<option value="" >--Choose Category--</option>';
	$str.='<option value="0" '.$psel.' >PRIMARY</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		// $sel='';
		if($row['cat_id']==$id)
		{$sel='selected="selected"';}
		
		$str .= '<option '.$sel.' value="'.$row['cat_id'].'">'.$row['cat_name'].'</option>';
	}
	return $str;
}

function get_category_by_id($dbcon,$id)
{
	$query="select * from tbl_category where cat_id='$id'";
	$row=$dbcon->query($query);
	$rel=mysqli_fetch_array($row);
	return $rel['cat_name'];
}

function get_branch($dbcon,$eid){
	$query="select branch_id,branch_name from branch_mst where branch_status=0";
	$rs_dispatch=$dbcon->query($query);	
	$str='<option value="">Choose Branch</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel='';
		if($rel['branch_id']==$eid){
			$sel='selected="selected"';
		}
		$str.='<option '.$sel.' value="'.$rel['branch_id'].'">'.$rel['branch_name'].'</option>';
	}
	return $str;
}


function get_bom_producttype($dbcon,$id)
{
	$pro_tml='';
	$pro_tml.= '<option value="">Choose Product Type</option>';
	$pro_tml.= '<option value="1" '.(($id=="1")?"selected":"").'>Assembly Product</option>';
	$pro_tml.= '<option value="2" '.(($id=="2")?"selected":"").'>SUB ASSEMBLY</option>';
	$pro_tml.= '<option value="3" '.(($id=="3")?"selected":"").'>RAW MATERIA</option>';
	$pro_tml.= '<option value="4" '.(($id=="4")?"selected":"").'>FINISH PART</option>';
	$pro_tml.= '<option value="5" '.(($id=="5")?"selected":"").'>BOI</option>';
	$pro_tml.= '<option value="6" '.(($id=="6")?"selected":"").'>CAPITAL GOODS</option>';
	$pro_tml.= '<option value="7" '.(($id=="7")?"selected":"").'>CONSUMABLE</option>';
	return $pro_tml;
}



function getBomGroup($dbcon,$eid){
	$query="select * from tbl_bom_group where bg_status=0";
	$rs_dispatch=$dbcon->query($query);	
	$str='<option value="">Choose Group</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel='';
		if($rel['bg_id']==$eid ){
			$sel='selected="selected"';
		}
		$str.='<option '.$sel.' value="'.$rel['bg_id'].'">'.$rel['bg_name'].'</option>';
	}
	return $str;
}

function getrequiredproduct($dbcon,$id,$where)
{	
	$str='';
	$q='';
	if($where)
	{
		$q = $where;
	}
	$query="select p.product_id,p.product_name,p.product_desc from product_mst as p 
	where p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) ".$q." order by p.product_name";
	/*$query="select p.product_id,p.product_name,c.cat_name from product_mst as p 
	left join tbl_category as c on c.cat_id=p.product_category
	where p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) ".$q." order by p.product_name";*/
	$rs_dispatch=$dbcon->query($query);	
	$str .= '<option value="">Choose Product</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel=''; 
		if($rel['product_id']==$id)
		{$sel ="selected='selected'";}
		$str .= '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name']."-- ( ".$rel['product_desc'].')'.'</option>';
	}
	return $str;
}

function get_product_type_by_id($dbcon,$type)
{
	if($type=='0')
	{
		$retrun_type="Finish Product";
	}
	else if($type=='1')
	{
		$retrun_type="ASSEMBLY PRODUCT";
	}
	else if($type=='2')
	{
		$retrun_type="SUB ASSEMBLY";
	}
	else if($type=='3')
	{
		$retrun_type="RAW MATERIAL";
	}
	else if($type=='4')
	{
		$retrun_type="FINISH PART";
	}
	else if($type=='5')
	{
		$retrun_type="BOI";
	}
	else if($type=='6')
	{
		$retrun_type="CAPITAL GOODS";
	}
	else if($type=='7')
	{
		$retrun_type="CONSUMABLE";
	}
	else if($type=='8')
	{
		$retrun_type="SERVICE";
	}
	
	return $retrun_type;
}


function get_product_purchase_rate($dbcon,$id)
{
	$get_pro_qry = "select product_id,product_purchase_rate from product_mst where product_id=".$id;
	$get_qry_rrl  = mysqli_fetch_assoc($dbcon->query($get_pro_qry));
	return $get_qry_rrl['product_purchase_rate'];	
}

function get_images_product($dbcon,$id)
{
	$q="select * from product_mst_images where im_product=$id";
	$rel=$dbcon->query($q);
	$path='view/upload/product_images/';
	$str="";
	$str.="<table></tr>";
	while($row  = mysqli_fetch_assoc($rel))
	{
		$str.='<td>
			<div class="img-wrap">
				<span class="close">&times;</span>
				<img src="'.ROOT.'view/img/close_img.jpg" width="30" height="30">
			</div>
			<img src="'.ROOT.$path.$row['im_name'].'" height="150" width="225" class="img-thumbnail" />
				
		</td>';
	}
	$str.="</tr></table>";
	return $str;
}

function get_all_bank($dbcon,$id)
{
	$q="select * from bank_mst where bank_status='0' order by bank_name";
	$r=$dbcon->query($q);
	
	$str="";
	$str.= '<option value="">Choose Bank</option>';
	while($rel=mysqli_fetch_assoc($r))
	{
		$sel=''; 
		if($rel['bank_id']==$id)
		{$sel ="selected='selected'";}
		$str.= '<option  value="'.$rel['bankid'].'">'.$rel['bank_name'].'</option>';
	}
	return $str;
	
}

function get_stock_by_branch($dbcon,$bid,$pid,$type)
{
	$q="select * from tbl_branch_product_stock where branch_id='$bid' and product_id='$pid'";
	$r=$dbcon->query($q);
	$rel=mysqli_fetch_assoc($r);
	if($type=="stock"){
		return $rel['product_stock'];
	}else{
		return $rel['priority'];
	}
}

function get_all_customer($dbcon,$id)
{
	$q="select * from tbl_customer order by company_name";
	$r=$dbcon->query($q);
	$str="";
	$str.="<option value=''>--Select customer--</option>";
	while($rel=mysqli_fetch_assoc($r))
	{
		$str.="<option value='".$rel['cust_id']."'>".$rel['company_name']."</option>";
	}
	return $str;
}

function membersTree($dbcon,$parentKey,$bomid)
{

	  $sql = "SELECT * from tbl_bomtrn WHERE parent_id='$parentKey' and bom_id='$bomid' ";

	  $result = $dbcon->query($sql);

	  while($value = mysqli_fetch_assoc($result)){
		 $pname=get_pro_field($dbcon,$value['product_id'],'product_name');
		 $id = $value['product_id'];
		 $row1[$id]['id'] = $value['product_id'];
		 $row1[$id]['name'] = get_pro_field($dbcon,$value['product_id'],'product_name');
		 $row1[$id]['text'] = "<input type='checkbox' name='sp_pr' id='sp_pr' value='".$value['product_id']."'  />".$pname;
		 $row1[$id]['nodes'] = array_values(membersTree($dbcon,$value['product_id'],$bomid));
	  }

	  return $row1;
}

function get_bom_id($dbcon,$pid)
{
	$q="select bom_id from tbl_bom where bom_product='$pid'";
	$rel=$dbcon->query($q);
	$row=mysqli_fetch_assoc($rel);
	return $row['bom_id'];
}

function get_process_type($dbcon,$id) {
	$str='';
	$query="select `process_type_id`,`process_type_name` from process_type_mst where process_type_status=0";
	$rs_product=$dbcon->query($query);
	$str.='<option value="">--select Process Type--</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		
		
		$sel='';
		if($rel['process_type_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['process_type_id'].'">'.$rel['process_type_name'].'</option>';
	}
	return $str;
}

function get_all_process($dbcon,$id) {
	$str='';
	$query="select `process_id`,`process_name` from process_mst where process_status=0";
	$rs_product=$dbcon->query($query);
	$str.='<option value="">--select Process Type--</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['process_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['process_id'].'">'.$rel['process_name'].'</option>';
	}
	return $str;
}

function get_all_process_by_type($dbcon,$pid,$id) {
	$str='';
	$query="select `process_id`,`process_name` from process_mst where process_status=0 and process_type=$pid";
	$rs_product=$dbcon->query($query);
	//$str.='<option value="">--Select Process--</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['process_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['process_id'].'">'.$rel['process_name'].'</option>';
	}
	return $str;
}

function get_tax_percentage($dbcon,$id)
{
	$str='';
	$query="Select * from tbl_tax_per_master where tp_status=0 ";
	$rs_type=$dbcon->query($query);
  
	$str ='<option value="" >--Choose Tax--</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$sel='';
		if($row['tp_id']==$id)
		{$sel='selected="selected"';}
		
		$str .= '<option '.$sel.' value="'.$row['tp_id'].'">'.$row['tp_per'].'</option>';
	}
	return $str;
}

function get_product_tax_formula($dbcon,$pid,$type,$cust_state)
{
	$r=$dbcon->query("select stateid from tbl_company where company_id='$_SESSION[company_id]'");
	$crow=mysqli_fetch_array($r);
	$company_state=$crow['stateid'];
	
	if($company_state==$cust_state)
	{
		$category="INTER";
	}
	else{
		$category="INTRA";
	}
	
	if($type=='purchase')
	{
		$q=$dbcon->query("select p.product_purchase_gst,f.formula_name,f.formulaid,f.tax_id from product_mst as p left join formula_mst as f on f.tax_per_id=p.product_purchase_gst where p.product_id='$pid' and f.tax_cat='$category'");
	}
	else
	{
		$q=$dbcon->query("select p.product_sale_gst,f.formula_name,f.formulaid,f.tax_id from product_mst as p left join formula_mst as f on f.tax_per_id=p.product_sale_gst where p.product_id='$pid'  and f.tax_cat='$category'");
	}
	$row=mysqli_fetch_array($q);
	
	$res['name']=$row['formula_name'];
	$res['id']=$row['formulaid'];
	$res['tax_id']=$row['tax_id'];
	
	return json_encode($res);
}

function get_process_type_by_id($dbcon,$id)
{
	$q="select p.process_id,pt.process_type_id from process_mst as p inner join process_type_mst as pt on pt.process_type_id=p.process_type where p.process_id='$id'";
	$row=$dbcon->query($q);
	$rel=mysqli_fetch_assoc($row);
	return $rel['process_type_id'];
	//return $id;
}

function get_fist_bom($dbcon,$comp_id)
{
	if($comp_id==0 || !$comp_id){ $id=0; }else{ $id=$comp_id; }
	$q="select c.product_id,b.bom_id from tbl_complaint_trn as c inner join tbl_bom as b on b.bom_product=c.product_id where c.complaint_id='$id' and complaint_trn_status!=2 and b.bom_status='0' and c.user_id=".$_SESSION['user_id'];
	$row=$dbcon->query($q);
	$rel=mysqli_fetch_assoc($row);
	return $rel['bom_id'];
}

function get_fist_comp_product($dbcon,$comp_id)
{
	if($comp_id==0){ $id=0; }else{ $id=$comp_id; }
	$q="select product_id from tbl_complaint_trn where complaint_id='$id' and complaint_trn_status!=2";
	$row=$dbcon->query($q);
	$rel=mysqli_fetch_assoc($row);
	return $rel['product_id'];
}


function get_grn_for_debitnote($dbcon,$vender_id,$id,$mode)
{	
	if($mode=='Add'){
		/*$query="SELECT grn.grn_no,grn.grn_id FROM `tbl_mrn` as mrn 
		INNER join tbl_grn as grn on grn.grn_id=mrn.grn_no
		WHERE mrn.mrn_status=0 and grn.grn_status=0 and grn.vender_id=".$vender_id." and grn.grn_id not in (SELECT grn_id from tbl_debitnote_trn WHERE debitnote_trn_status=0)";*/
		
		if(!empty($id)){
			$che=" and grn.grn_id=".$id;
		}
		
		$query="SELECT mrn.mrn_id,grn.grn_id,led.l_name,mtrn.rejected_qty,mrn.qc_no,pro.product_name,qc.qc_no,qc.qc_date,grn.grn_no,grn.grn_date,(select IFNULL(sum(product_qty),0) as qty  from tbl_debitnote_trn as chtrn where chtrn.debitnote_trn_status=0 and chtrn.grn_id=mrn.grn_no and mtrn.product_id=chtrn.product_id) as used_qty FROM tbl_mrn as mrn 
		left join tbl_mrn_trn as mtrn on mtrn.mrn_no=mrn.mrn_id
		left join product_mst as pro on pro.product_id=mtrn.product_id
		left join tbl_grn as grn on grn.grn_id=mrn.grn_no
		left join tbl_qc as qc on qc.qc_id=mrn.qc_no
		left join tbl_ledger as led on led.l_id=grn.vender_id
		where mrn.mrn_status=0 and grn.vender_id=".$vender_id." having mtrn.rejected_qty > used_qty order by mrn.mrn_id";
	}
	else{
		$query="SELECT grn.grn_no,grn.grn_id FROM `tbl_mrn` as mrn 
		INNER join tbl_grn as grn on grn.grn_id=mrn.grn_no
		WHERE mrn.mrn_status=0 and grn.grn_status=0 and grn.vender_id=".$vender_id."";
	}
	
	$str='';
	$rs_dispatch=$dbcon->query($query);	
	$str = '<option value="">Choose GRN</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		if($rel['grn_id']==$id)
		{$sel ="selected='selected'";}
		$str .= '<option '.$sel.' value="'.$rel['grn_id'].'">'.$rel['grn_no'].'</option>';
	}
	return $str;
}

function get_grn_trn_for_debitnote($dbcon,$grn_id,$id,$mode)
{	
	$query="SELECT trn.product_id,pro.product_name from tbl_grn_trn as trn
		INNER join tbl_mrn as mrn on mrn.grn_no=trn.grn_id and mrn.mrn_status=0
		INNER join tbl_mrn_trn as mrntrn on mrntrn.mrn_no=mrn.mrn_id and mrntrn.mrn_trn_status=0 and mrntrn.product_id=trn.product_id
		left join product_mst as pro on pro.product_id=trn.product_id
		where trn.grn_trn_status=0 and trn.grn_id=".$grn_id;
	$str='';
	$rs_dispatch=$dbcon->query($query);	
	$str = '<option value="">Choose Product</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		if($rel['product_id']==$id)
		{$sel ="selected='selected'";}
		$str .= '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name'].'</option>';
	}
	return $str;
}
function get_grn_for_purchase($dbcon,$vender_id,$id,$mode)
{	
	if($mode=='Add'){
		/*$query="select mst.grn_id,mst.grn_no from tbl_grn as mst
		where mst.grn_status=0 and mst.qc_status=1 and vender_id=".$vender_id." and mst.grn_id not in (SELECT grn_id from tbl_potrancation WHERE potrancation_status=0) and company_id=".$_SESSION['company_id'];*/
		
		$query="select grn.grn_id,gtrn.product_qty,grn.grn_no,grn.grn_date,pro.product_name,led.l_name,(select IFNULL(sum(product_qty),0) as qty  from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty from tbl_grn as grn
		left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id
		left join product_mst as pro on pro.product_id=gtrn.product_id
		left join tbl_ledger as led on led.l_id=grn.vender_id
		where grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.vender_id=".$vender_id." and grn.company_id=".$_SESSION['company_id']." having gtrn.product_qty > used_qty order by grn.grn_id desc";
	}
	else{
		$query="select mst.grn_id,mst.grn_no from tbl_grn as mst
		where mst.grn_status=0 and mst.qc_status=1 and vender_id=".$vender_id." and company_id=".$_SESSION['company_id'];
	}
	$str='';
	$rs_dispatch=$dbcon->query($query);	
	$str = '<option value="">Choose GRN</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		if($rel['grn_id']==$id)
		{$sel ="selected='selected'";}
		$str .= '<option '.$sel.' value="'.$rel['grn_id'].'">'.$rel['grn_no'].'</option>';
	}
	return $str;
}
function get_grn_trn_for_purchase($dbcon,$grn_id,$id,$mode)
{	
	/*if($mode=='Add'){
		$query="select mst.grn_id,mst.grn_no from tbl_grn as mst
		where mst.grn_status=0 and mst.qc_status=1 and vender_id=".$vender_id." and company_id=".$_SESSION['company_id'];
	}
	else{
		$query="select mst.grn_id,mst.grn_no from tbl_grn as mst
		where mst.grn_status=0 and mst.qc_status=1 and vender_id=".$vender_id." and company_id=".$_SESSION['company_id'];
	}*/
	$query="select trn.product_id,pro.product_name,product_qty,(select IFNULL(sum(product_qty),0) as qty  from tbl_potrancation as chtrn where chtrn.potrancation_status!=2 and chtrn.grn_id=trn.grn_id and trn.product_id=chtrn.product_id) as used_qty from tbl_grn_trn as trn
			left join product_mst as pro on pro.product_id=trn.product_id
			where trn.grn_trn_status=0 and trn.grn_id=".$grn_id." having product_qty>used_qty";
	$str='';
	$rs_dispatch=$dbcon->query($query);	
	$str = '<option value="">Choose Product</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		if($rel['product_id']==$id)
		{$sel ="selected='selected'";}
		$str .= '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name'].'</option>';
	}
	return $str;
}
function get_po_for_purchase($dbcon,$id)
{	
	$str='';
	$query="select * from tbl_purchaseorder where status=0 and po_type_status=1 and purchase_status=0 and po_approval_status=1 and vender_id=".$id." and company_id=".$_SESSION['company_id'];
	$rs_dispatch=$dbcon->query($query);	
	$str = '<option value="">Choose Purchase Order</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		/*if($rel['sales_order_id']==$id)
		{$sel ="selected='selected'";}*/
		$str .= '<option '.$sel.' value="'.$rel['purchaseorder_id'].'">'.$rel['purchaseorder_no'].'</option>';
	}
	return $str;
}

function get_purchase_order_typewise_data($dbcon,$pro_type,$id)
{
	$str='';
	$query="select trn.*,pro.product_id,pro.product_name from tbl_purchaseordertrn as trn inner join product_mst as pro on pro.product_id=trn.product_id where purchaseordertrn_status=0 and use_purchase_status=0 and trn.product_type=".$pro_type." and purchaseorder_id=".$id;
	$rs_dispatch=$dbcon->query($query);	
	$que="select * from product_mst where product_status=0 and product_type=3";
	$rs_dispatch1=$dbcon->query($que);
	$str = '<option value="">Choose Purchase Order Products</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		/*if($rel['sales_order_id']==$id)
		{$sel ="selected='selected'";}*/
		$str .= '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name'].'-'.$rel['product_hsn_code'].'</option>';
	}
	while($re=mysqli_fetch_assoc($rs_dispatch1))
	{	
		$sel=''; 
		/*if($rel['sales_order_id']==$id)
		{$sel ="selected='selected'";}*/
		$str .= '<option '.$sel.' value="'.$re['product_id'].'">'.$re['product_name'].'-'.$re['product_code'].'</option>';
	}
	return $str;
}


function load_complaint_no($dbcon)
{
	$row=array();
		$query1="select * from tbl_invoicetype where status=0 and type_id=1 and company_id=".$_SESSION['company_id'];
		$rows=mysqli_fetch_assoc($dbcon->query($query1));
		$id=$rows['taxinvoice_start'];
		$id=$id+1;
		//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
		//$end = $start+1;
		if($rows['invoice_format']=='2'){
			$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
		}
		else if($rows['invoice_format']=='1'){
			$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
		}
		else if($rows['invoice_format']=='3'){
			$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
		}
		else{
			$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
		}
		$row['challanno']=str_pad($id,3,"0",STR_PAD_LEFT);
		
		return $row['invoiceno'];
}

function get_last_purchase($dbcon,$product_id)
{
	$q="select p.po_id,p.po_date,pt.po_landing_cost from tbl_pono as p left join tbl_potrancation as pt on pt.po_id=p.po_id where pt.product_id='$product_id' order by p.po_date desc";
	$query=$dbcon->query($q);
	$row=mysqli_fetch_array($query);
	
	return $row['po_landing_cost'];
	//return $product_id;
}

function get_process_product($dbcon,$product_id)
{
	$q="select p.process_id,p.process_priority,pr.process_name from tbl_product_process as p left join process_mst as pr on pr.process_id=p.process_id where p.status = 0 AND  p.product_id='$product_id' order by p.process_priority";
	$query=$dbcon->query($q);
	$arr=array();
	while($row=mysqli_fetch_array($query))
	{
		$arr[]=$row['process_name'];
	}
	$arr=array_unique($arr);
	return implode(",",$arr);
}

function get_tree_bom($dbcon,$product_id,$parent_id,$level,$cnt,$bom,$number,$qty,$bom_trn_id,$unit_name)
{
	if($level==0)
	{
		$pr_value=get_pro_field($dbcon,$product_id,'product_name');
		$pr_type=get_pro_field($dbcon,$product_id,'product_type');
		echo '
				<td class="td1">'.$number.'</td>
				<td class="td2"><strong>'.$pr_value.'</strong></td>
				<td class="td2">'.get_product_type_by_id($dbcon,$pr_type).'</td>
				<td class="td3">'.(round($qty,2)).'</td>
				<td class="td3">'.$unit_name.'</td>
				<td class="td3">'.get_last_purchase($dbcon,$product_id).'</td>
				<td class="td3">'.get_process_product($dbcon,$product_id).'</td>
				';
				
	}
					
	$getChildNodes = "select b.*,u.unit_name from tbl_bomtrn as b left join unit_mst as u on u.unitid=b.product_uom where b.parent_id = '".$bom_trn_id."' and b.bom_id='$bom' order by bom_trn_id Desc";
	$resChildNodes = $dbcon->query($getChildNodes);
	if(mysqli_num_rows($resChildNodes) > 0)
	{

		echo '<tr>';
		
		$cntt=1;
		while($childNode = mysqli_fetch_assoc($resChildNodes))
		{
			$pro_name=get_pro_field($dbcon,$childNode['product_id'],'product_name');
			
			$product_desc=get_pro_field($dbcon,$childNode['product_id'],'product_desc');
			
			$pr_type=get_pro_field($dbcon,$childNode['product_id'],'product_type');
			
			$product_specification=get_pro_field($dbcon,$childNode['product_id'],'product_specification');
			
			$getChildNodes1 = "select b.*,u.unit_name from tbl_bomtrn as b left join unit_mst as u on u.unitid=b.product_uom where b.parent_id = '".$childNode['bom_trn_id']."' and b.bom_id='$bom' order by bom_trn_id Desc";
			$resChildNodes1 = $dbcon->query($getChildNodes1);
			if(mysqli_num_rows($resChildNodes1) > 0)
			{
				$new_number=$number.'.'.$cntt;
				if($product_desc){
					$pro_name.=' | '.$product_desc;
				}
				if($product_specification){
					$pro_name.='<br/><strong>Width:</strong>'.$childNode['product_width'].'<strong>| Height:</strong>'.$childNode['product_height'].'<strong>| Thickness:</strong>'.$childNode['product_thickness'].'<strong>| Kg:</strong>'.$childNode['product_kg'].' ';
				}
				echo '<tr>
				<td  class="td1">'.$new_number.'</td>
				<td class="td2">'.$pro_name.'</td>
				<td class="td2">'.get_product_type_by_id($dbcon,$pr_type).'</td>
				<td class="td2">'.(round($childNode['product_qty'],2)).'</td>
				<td class="td3">'.$childNode['unit_name'].'</td>
				<td class="td2">'.get_last_purchase($dbcon,$childNode['product_id']).'</td>
				<td class="td3">'.get_process_product($dbcon,$childNode['product_id']).'</td>
				</tr>';
				
			
				$level++;$cnt++;$cntt++;
				
				get_tree_bom($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom,$new_number,$childNode['product_qty'],$childNode['bom_trn_id'],$childNode['unit_name']);
				
			}
			else
			{
				$new_number=$number.'.'.$cntt;
				if($product_desc){
					$pro_name.=' | '.$product_desc;
				}
				if($product_specification){
					$pro_name.='<br/><strong>Width:</strong>'.$childNode['product_width'].'<strong>| Height:</strong>'.$childNode['product_height'].'<strong>| Thickness:</strong>'.$childNode['product_thickness'].'<strong>| Kg:</strong>'.$childNode['product_kg'].' ';
				}
				echo '<tr>
					<td  class="td1">'.$new_number.'</td>
					<td class="td2">'.$pro_name.'</td>
					<td class="td2">'.get_product_type_by_id($dbcon,$pr_type).'</td>
					<td class="td2">'.(round($childNode['product_qty'],2)).'</td>
					<td class="td3">'.$childNode['unit_name'].'</td>
					<td class="td2">'.get_last_purchase($dbcon,$childNode['product_id']).'</td>
					<td class="td3">'.get_process_product($dbcon,$childNode['product_id']).'</td>
				</tr>';
				
				
				$level++;$cnt++;$cntt++;
				//get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);
				
			}
			
			//$cntt++;
		}
		
	
	}
	 
}

function check_requested($dbcon,$product,$req_no)
{
	$query="select rp_id from tbl_request_product where rp_pid='$product' and rp_req_no='$req_no' and status='0'";
	$row=$dbcon->query($query);
	$count=mysqli_num_rows($row);
	$childNode = mysqli_fetch_assoc($row);
	//return $count;
	return $childNode['rp_id'];
}
function check_reserve_stock($dbcon,$product,$req_no)
{
	$query="select reserve_stock from tbl_request_product where rp_pid='$product' and rp_req_no='$req_no' and status='0'";
	$row=$dbcon->query($query);
	$count=mysqli_num_rows($row);
	$childNode = mysqli_fetch_assoc($row);
	//return $count;
	return $childNode['reserve_stock'];
}
function check_mainrequested($dbcon,$product,$req_no)
{
	$query="select rp_id from tbl_request_product where rp_pid='$product' and rp_req_no='$req_no' and status='0'";
	$row=$dbcon->query($query);
	$count=mysqli_num_rows($row);
	$childNode = mysqli_fetch_assoc($row);
	return $childNode['rp_id'];
}

/*
function get_tree_request($dbcon,$product_id,$parent_id,$level,$cnt,$bom,$number,$qty,$bom_trn_id,$ptype,$pr_setting)
{
	global $counter_tree;
	
	if($level==0)
	{
		$pr_value=get_pro_field($dbcon,$product_id,'product_name');
		
		$pr_setting_arr=explode(",",$pr_setting);
		
		if(in_array("process_product",$pr_setting_arr))
		{
			$readonly="";
			$in_check_qty="";
		}
		else
		{
			$readonly="readonly";
			$in_check_qty="1";
		}
		
		if(check_requested($dbcon,$pr_value)==0)
		{
			$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
		}
		else
		{
			$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
			//$btn='<a class="btn btn-danger" data-original-title="" data-toggle="tooltip" data-placement="top" ><i class="fa fa-paper-plane"></i> Requested</a>';
		}
		
		echo '
				<td class="td1">'.$number.'</td>
				<td class="td2">'.$pr_value.'</td>
				<td class="td4">'.get_product_type_by_id($dbcon,get_pro_field($dbcon,$product_id,'product_type')).'</td>
				<td class="td4">'.get_pro_field($dbcon,$product_id,'product_min_stock').'</td>
				<td class="td5">'.get_product_stock($dbcon,$product_id).'</td>
				<td class="td5">
					<input type="text" class="form-control" name="req_qty'.$counter_tree.'" id="req_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)"  />
					
					<input type="hidden" class="form-control" name="total_qty'.$counter_tree.'" id="total_qty'.$counter_tree.'" value="'.$qty.'"  />
					
					
				</td>
				<td class="td5">
					<input type="text" class="form-control" name="in_process_qty'.$counter_tree.'" id="in_process_qty'.$counter_tree.'" '.$readonly.' onkeypress="return isNumberKey(event)" />
					
					<input type="hidden" class="form-control" name="in_process_qty_check'.$counter_tree.'" id="in_process_qty_check'.$counter_tree.'" value="'.$in_check_qty.'" />
				</td>
				
				<td class="td5">
					<input type="text" class="form-control" name="po_qty'.$counter_tree.'" id="po_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)" />
					<input type="hidden" class="form-control" name="pr_id'.$counter_tree.'" id="pr_id'.$counter_tree.'" value="'.$product_id.'" />
					<input type="hidden" class="form-control" name="pr_type'.$counter_tree.'" id="pr_type'.$counter_tree.'" value="'.$ptype.'" />
				</td>
				<td class="td5">'.$btn.'</td>';
				
	}
					
	$getChildNodes = "select * from tbl_bomtrn where parent_id = '".$bom_trn_id."' and bom_id='$bom'";
	$resChildNodes = $dbcon->query($getChildNodes);
	if(mysqli_num_rows($resChildNodes) > 0)
	{

		echo '<tr>';
		
		$cntt=1;
		while($childNode = mysqli_fetch_assoc($resChildNodes))
		{
			$pro_name=get_pro_field($dbcon,$childNode['product_id'],'product_name');
			
			$pro_setting=get_pro_field($dbcon,$childNode['product_id'],'product_setting_check');
			
			$pr_setting_arr=explode(",",$pro_setting);
			
			if(in_array("process_product",$pr_setting_arr))
			{
				$readonly="";
				$in_check_qty="";
			}
			else
			{
				$readonly="readonly";
				$in_check_qty="1";
			}
			
			
			$getChildNodes1 = "select * from tbl_bomtrn where parent_id = '".$childNode['bom_trn_id']."' and bom_id='$bom'";
			$resChildNodes1 = $dbcon->query($getChildNodes1);
			if(mysqli_num_rows($resChildNodes1) > 0)
			{
				$new_number=$number.'.'.$cntt;
				$counter_tree++;
				
					
				if(check_requested($dbcon,$childNode['product_id'])==0)
				{
					$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
				}
				else
				{
					$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
					//$btn='<a class="btn btn-danger" data-original-title="" data-toggle="tooltip" data-placement="top" ><i class="fa fa-paper-plane"></i> Requested</a>';
				}
				
				echo '<tr>
				<td  class="td1">'.$number.'</td>
				<td class="td2">'.$pro_name.'</td>
				<td class="td4">'.get_product_type_by_id($dbcon,get_pro_field($dbcon,$childNode['product_id'],'product_type')).'</td>
				<td class="td4">'.get_pro_field($dbcon,$childNode['product_id'],'product_min_stock').'</td>
				<td class="td5">'.get_product_stock($dbcon,$childNode['product_id']).'</td>
				<td class="td5">
					<input type="text" class="form-control" name="req_qty'.$counter_tree.'" id="req_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)" />
					
					<input type="hidden" class="form-control" name="total_qty'.$counter_tree.'" id="total_qty'.$counter_tree.'" value="'.$childNode['product_qty'].'"  />
					
				</td>
				<td class="td5">
					<input type="text" class="form-control" name="in_process_qty'.$counter_tree.'" id="in_process_qty'.$counter_tree.'" '.$readonly.' onkeypress="return isNumberKey(event)" />
					
					<input type="hidden" class="form-control" name="in_process_qty_check'.$counter_tree.'" id="in_process_qty_check'.$counter_tree.'" value="'.$in_check_qty.'" />
				</td>
				
				<td class="td5">
					<input type="text" class="form-control" name="po_qty'.$counter_tree.'" id="po_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)" />
					<input type="hidden" class="form-control" name="pr_id'.$counter_tree.'" id="pr_id'.$counter_tree.'"  value="'.$childNode['product_id'].'"  />
					<input type="hidden" class="form-control" name="pr_type'.$counter_tree.'" id="pr_type'.$counter_tree.'" value="'.$childNode['product_type'].'" />
				</td>
				<td class="td5">'.$btn.'</td>
				</tr>';
				
			
				$level++;$cnt++;$cntt++;
				
				get_tree_request($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom,$new_number,$childNode['product_qty'],$childNode['bom_trn_id'],$childNode['product_type'],$pro_setting);
				
			}
			else
			{
				$pr_setting_arr=explode(",",$childNode['product_setting_check']);
				
				$pro_setting=get_pro_field($dbcon,$childNode['product_id'],'product_setting_check');
				
				if(in_array("process_product",$pro_setting))
				{
					$readonly="";
					$in_check_qty="";
				}
				else
				{
					$readonly="readonly";
					$in_check_qty="1";
				}
				
				
				
				$new_number=$number.'.'.$cntt;
				$counter_tree++;
				
				if(check_requested($dbcon,$childNode['product_id'])==0)
				{
					$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
				}
				else
				{
					$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
					//$btn='<a class="btn btn-danger" data-original-title="" data-toggle="tooltip" data-placement="top" ><i class="fa fa-paper-plane"></i> Requested</a>';
				}
				
				echo '<tr>
				<td  class="td1">'.$new_number.'</td>
				<td   class="td2">'.$pro_name.'</td>
				<td class="td4">'.get_product_type_by_id($dbcon,get_pro_field($dbcon,$childNode['product_id'],'product_type')).'</td>
				<td class="td4">'.get_pro_field($dbcon,$childNode['product_id'],'product_min_stock').'</td>
				<td class="td5">'.get_product_stock($dbcon,$childNode['product_id']).'</td>
				<td class="td5">
					<input type="text" class="form-control" name="req_qty'.$counter_tree.'" id="req_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)" />
					
					<input type="hidden" class="form-control" name="total_qty'.$counter_tree.'" id="total_qty'.$counter_tree.'" value="'.$childNode['product_qty'].'"  />
					
				</td>
				<td class="td5">
					<input type="text" class="form-control" name="in_process_qty'.$counter_tree.'" id="in_process_qty'.$counter_tree.'" '.$readonly.' onkeypress="return isNumberKey(event)" />
					
					<input type="hidden" class="form-control" name="in_process_qty_check'.$counter_tree.'" id="in_process_qty_check'.$counter_tree.'" value="'.$in_check_qty.'" />
				</td>
			
				<td class="td5">
					<input type="text" class="form-control" name="po_qty'.$counter_tree.'" id="po_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)" />
					<input type="hidden" class="form-control" name="pr_id'.$counter_tree.'" id="pr_id'.$counter_tree.'" value="'.$childNode['product_id'].'"  />
					<input type="hidden" class="form-control" name="pr_type'.$counter_tree.'" id="pr_type'.$counter_tree.'" value="'.$childNode['product_type'].'" />
				</td>
				<td class="td5">'.$btn.'</td>
				</tr>';
				
				
				$level++;$cnt++;$cntt++;
				//get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);
				
			}
			
			//$cntt++;
		}
		
	
	}
	 
	return $counter_tree;
}
*/

function check_level_open($dbcon,$pr_id,$req_no)
{
	$q=$dbcon->query("select rp_id from tbl_request_product where rp_pid=(select product_id from tbl_bomtrn where bom_trn_id=$pr_id) and rp_req_no='$req_no'");
	$count=mysqli_num_rows($q);
	return $count;
}



function get_tree_request($dbcon,$product_id,$parent_id,$level,$cnt,$bom,$number,$qty,$bom_trn_id,$ptype,$pr_setting,$req_no,$bom_real_level,$pid)
{
	global $counter_tree;
	
	if($level==0)
	{
		$pr_value=get_pro_field($dbcon,$product_id,'product_name');
		
		$pr_setting_arr=explode(",",$pr_setting);
		
		if(in_array("process_product",$pr_setting_arr))
		{
			$readonly="";
			$in_check_qty="";
		}
		else
		{
			$readonly="readonly";
			$in_check_qty="1";
		}
		
		if($bom_real_level!=1)
		{
			$display='display:none';
		}
		else
		{
			$display='display:block';
		}
		//$rno=check_requested($dbcon,$product_id,$req_no);
		$rev_sto=check_reserve_stock($dbcon,$product_id,$req_no);
		if(check_requested($dbcon,$product_id,$req_no)==0)
		{
			$kp=str_replace(".","",$number);
			$btn='<a class="btn btn-primary dispbtn csb'.$bom_trn_id.' sho'.$kp.'" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.','.$bom_trn_id.');show_btn('.$kp.',1);change_status('.$kp.');" ><i class="fa fa-paper-plane"></i> Request</a>';
				//style="'.$display.'"
			$btn.='<input type="hidden" name="btn_validate" required id="btn_validate" value="1" />';
			
			$submi="1";
			
			$btn.='<input type="hidden" id="reqsho'.$kp.'" value="1" />';
			$text_readonly="";
			//get_current_stock_new($dbcon,$product_id,$chil_unit["product_uom"])
			//reserve_stock($dbcon,$product_id,$chil_unit["product_uom"])
			$btn.=' <input type="hidden" id="savebuttonshow'.$kp.'" name="savebuttonshow[]" value="1" />';
			
			
			//$req_value='';
		}
		else
		{
			//$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
			$kp=str_replace(".","",$number);
			$btn='<a class="btn btn-danger dispbtn csb'.$bom_trn_id.' rsho'.$kp.' " data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
			
			$btn.='<input type="hidden" name="btn_validate" required id="btn_validate" value="1" />';
			
			$btn.=' <input type="hidden" id="reqsho'.$kp.'" value="0" />';
			
			$btn.=' <input type="hidden" id="savebuttonshow'.$kp.'" name="savebuttonshow[]" value="0" />';
			
			
			$text_readonly="readonly";
			
			$submi="0";
			//$process_value=get_process_value($dbcon,$product_id,$req_no);
			
		
		}
			$get_unit = "select btrn.product_uom,btrn.product_base_unit,umst.unit_name as purchase_unit,umst1.unit_name as process_unit from tbl_bomtrn as btrn
				left join unit_mst as umst on umst.unitid=btrn.product_uom
				left join unit_mst as umst1 on umst1.unitid=btrn.product_base_unit
				where bom_trn_id = '".$bom_trn_id."'";
			$res_unit = $dbcon->query($get_unit);
			$chil_unit = mysqli_fetch_assoc($res_unit);
		$cstock=get_current_stock_new($dbcon,$product_id,$chil_unit["product_uom"]);
		$rstock=reserve_stock($dbcon,$product_id,$chil_unit["product_uom"]);
		$actualstock=$cstock-$rstock;
		//$actualstock11=$cstock."-".$rstock;
		if($actualstock<"0"){
			$actualstock=0;
		}
			$reqhi="";
			if($actualstock==0){
				$reqhi="readonly";
			}
		echo '
				<td class="td1">'.$number.'</td>
				<td class="td2" title="'.get_product_type_by_id($dbcon,get_pro_field($dbcon,$product_id,'product_type')).'">'.$pr_value.'</td>
				<!--<td class="td4">'.get_product_type_by_id($dbcon,get_pro_field($dbcon,$product_id,'product_type')).'</td>-->
				<td class="td4">'.get_pro_field($dbcon,$product_id,'product_min_stock').'</td>
				<!--<td class="td5">'.get_current_stock_new($dbcon,$product_id,$chil_unit["product_uom"]).'</td>
				<td class="td5">'.reserve_stock($dbcon,$product_id,$chil_unit["product_uom"]).'</td>-->
				<td class="td5">
					<input type="number"  class="form-control" name="at_stock'.$counter_tree.'" id="at_stock'.$counter_tree.'" onkeypress="return isNumberKey(event)" value="'.$actualstock.'" readonly />
				</td>
				<td class="td5">
					<div class="col-md-9">
					<input type="number" min="0" class="form-control rt'.$bom_trn_id.'" name="req_qty'.$counter_tree.'" id="req_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)" '.$text_readonly.' value="'.$req_value.'" />
					</div>
					<div class="col-md-3" style="font-size:16px;white-space:nowrap;">
						<strong>'.$chil_unit["purchase_unit"].'</strong>
					</div>
					
					<input type="hidden" class="form-control tct'.$bom_trn_id.'" name="total_qty'.$counter_tree.'" id="total_qty'.$counter_tree.'" value="'.$qty.'"  />
				</td>
				<td class="td5">
					<input type="text"  class="form-control" name="at_reserve'.$counter_tree.'" id="at_reserve'.$counter_tree.'" onkeypress="return isNumberKey(event)" value="'.$rev_sto.'" '.$reqhi.' '.$text_readonly.' onkeyup="check_reseve_qty('.$counter_tree.',this.value);" />
				</td>
				<td class="td5">
					<div class="col-md-9">
					<input type="number" min="0" class="form-control pt'.$bom_trn_id.'" name="in_process_qty'.$counter_tree.'" id="in_process_qty'.$counter_tree.'" '.$text_readonly.' '.$readonly.'  onkeypress="return isNumberKey(event)" onkeyup="get_inhouse_inner('.$counter_tree.','.$bom_trn_id.');check_req_qty('.$counter_tree.',this.value);" value="'.$in_req_value.'" />
					</div>
					<div class="col-md-3" style="font-size:16px;">
						<strong>'.$chil_unit["process_unit"].'</strong>
						<input type="hidden" class="form-control" name="process_unit'.$counter_tree.'" id="process_unit'.$counter_tree.'" value="'.$chil_unit["product_base_unit"].'"  />
					</div>
					
					<input type="hidden" class="form-control inpc'.$bom_trn_id.'" name="in_process_qty_check'.$counter_tree.'" id="in_process_qty_check'.$counter_tree.'" value="'.$in_check_qty.'" />
				</td>
				
				<td class="td5">
					<div class="col-md-9">
						<input type="number" min="0" class="form-control po'.$bom_trn_id.'" name="po_qty'.$counter_tree.'" id="po_qty'.$counter_tree.'" onkeypress="return isNumberKey(event);" onkeyup="check_req_qty('.$counter_tree.',this.value);" '.$text_readonly.' />
					</div>
					<div class="col-md-3" style="font-size:16px;">
						<strong>'.$chil_unit["purchase_unit"].'</strong>
						<input type="hidden" class="form-control" name="purchase_unit'.$counter_tree.'" id="purchase_unit'.$counter_tree.'" value="'.$chil_unit["product_uom"].'"  />
					</div>
					<input type="hidden" class="form-control" name="pr_id'.$counter_tree.'" id="pr_id'.$counter_tree.'" value="'.$product_id.'" />
					<input type="hidden" class="form-control" name="pr_type'.$counter_tree.'" id="pr_type'.$counter_tree.'" value="'.$ptype.'" />
				</td>
				<td class="td5">'.$btn.'
					<input type="hidden" class="perent'.$bom_trn_id.'" name="perent'.$counter_tree.'" id="perent'.$counter_tree.'" value="'.$pid.'" />
					
					<input type="hidden" class="submi'.$bom_trn_id.'" name="submi[]" id="submi'.$counter_tree.'" value="'.$submi.'" />
				</td>';
				
	}
					
	$getChildNodes = "select * from tbl_bomtrn where parent_id = '".$bom_trn_id."' and bom_id='$bom'";
	$resChildNodes = $dbcon->query($getChildNodes);
	if(mysqli_num_rows($resChildNodes) > 0)
	{

		echo '<tr>';
		
		$cntt=1;
		
		$getC = "select product_id from tbl_bomtrn where bom_trn_id	 = '".$bom_trn_id."' and bom_id='$bom'";
		$resC = $dbcon->query($getC);
		$chie = mysqli_fetch_assoc($resC);
		$produ_id=$chie['product_id'];
		
		while($childNode = mysqli_fetch_assoc($resChildNodes))
		{
			$one_q=$childNode['product_qty']/$childNode['product_base_qty'];
			//$pid1=check_re_entry($dbcon,$produ_id,$req_no);
			$pid1=check_requested($dbcon,$produ_id,$req_no);
			
			$pro_name=get_pro_field($dbcon,$childNode['product_id'],'product_name');
			
			$pro_setting=get_pro_field($dbcon,$childNode['product_id'],'product_setting_check');
			
			$pr_setting_arr=explode(",",$pro_setting);
			
			if(in_array("process_product",$pr_setting_arr))
			{
				$readonly="";
				$in_check_qty="";
			}
			else
			{
				$readonly="readonly";
				$in_check_qty="1";
			}
			
			$bom_real_level=$childNode['bom_level'];
			
			$getChildNodes1 = "select * from tbl_bomtrn where parent_id = '".$childNode['bom_trn_id']."' and bom_id='$bom'";
			$resChildNodes1 = $dbcon->query($getChildNodes1);
			if(mysqli_num_rows($resChildNodes1) > 0)
			{
				$new_number=$number.'.'.$cntt;
				$counter_tree++;$level++;
				
				if($bom_real_level==1)
				{
					$display='display:none';
				}
				else if(check_level_open($dbcon,$childNode['parent_id'],$req_no)>0)
				{
					$display='display:block';
				}
				else
				{
					$display='display:none';
				}
				
					$rev_sto=check_reserve_stock($dbcon,$childNode['product_id'],$req_no);
					if(check_requested($dbcon,$childNode['product_id'],$req_no)==0)
					{
						$kp1=str_replace(".","",$new_number);
						$btn='<a class="btn btn-primary dispbtn csb'.$childNode['bom_trn_id'].' sho'.$kp1.' " data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.','.$childNode['bom_trn_id'].');show_btn('.$kp1.',1);change_status('.$kp1.');" ><i class="fa fa-paper-plane"></i> Request</a>';
					//style="'.$display.'"
						$btn.='<input type="hidden" name="btn_validate" required id="btn_validate" value="1" />';
						
						$btn.='<input type="hidden" id="reqsho'.$kp1.'" value="1" />';
						$submi="1";
					}
					else
					{
						$kp1=str_replace(".","",$new_number);
						$btn='<a class="btn btn-danger rsho'.$kp1.'" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
							
						$btn.='<input type="hidden" name="btn_validate" required id="btn_validate" value="1" />';
						
						$btn.='<input type="hidden" id="reqsho'.$kp1.'" value="0" />';
						$submi="0";
					}
			$get_unit1 = "select btrn.product_uom,btrn.product_base_unit,umst.unit_name as purchase_unit,umst1.unit_name as process_unit from tbl_bomtrn as btrn
				left join unit_mst as umst on umst.unitid=btrn.product_uom
				left join unit_mst as umst1 on umst1.unitid=btrn.product_base_unit
				where bom_trn_id = '".$childNode['bom_trn_id']."'";
			$res_unit1 = $dbcon->query($get_unit1);
			$chil_unit1 = mysqli_fetch_assoc($res_unit1);
		
			$cstock=get_current_stock_new($dbcon,$childNode['product_id'],$chil_unit1['product_uom']);
			$rstock=reserve_stock($dbcon,$childNode['product_id'],$chil_unit1['product_uom']);
			$actualstock=$cstock-$rstock;
			if($actualstock<"0"){
				$actualstock=0;
			}
			$reqhi="";
			if($actualstock==0){
				$reqhi="readonly";
			}
			$tital=get_product_type_by_id($dbcon,get_pro_field($dbcon,$childNode['product_id'],'product_type'));
				
				echo '<tr>
				<td  class="td1">'.$new_number.'</td>
				<td class="td2" title="'.$tital.'" >'.$pro_name.'</td>
				<!--<td class="td4"></td>-->
				<td class="td4">'.get_pro_field($dbcon,$childNode['product_id'],'product_min_stock').'</td>
				<!--<td class="td5">'.get_current_stock_new($dbcon,$childNode['product_id'],$chil_unit1['product_uom']).'</td>
				<td class="td5">'.reserve_stock($dbcon,$childNode['product_id'],$chil_unit1['product_uom']).'</td>-->
				<td class="td5">
					<input type="number"  class="form-control" name="at_stock'.$counter_tree.'" id="at_stock'.$counter_tree.'" onkeypress="return isNumberKey(event)" value="'.$actualstock.'" readonly />
				</td>
				<td class="td5">
					<div class="col-md-9">
					<input type="number" min="0" class="form-control rt'.$childNode['bom_trn_id'].'" name="req_qty'.$counter_tree.'" id="req_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)"  />
					</div>
					<div class="col-md-3" style="font-size:16px;">
						<strong>'.$chil_unit1['purchase_unit'].'</strong>
					</div>
					
				<!--	<input type="text" class="form-control tct'.$childNode['bom_trn_id'].'" name="total_qty'.$counter_tree.'" id="total_qty'.$counter_tree.'" value="'.$childNode['product_qty'].'"  />-->
					<input type="hidden" class="form-control tct'.$childNode['bom_trn_id'].'" name="total_qty'.$counter_tree.'" id="total_qty'.$counter_tree.'" value="'.$one_q.'"  />
					
				</td>
				<td class="td5">
					<input type="number"  class="form-control" name="at_reserve'.$counter_tree.'" id="at_reserve'.$counter_tree.'" onkeypress="return isNumberKey(event)" value="'.$rev_sto.'" '.$reqhi.' '.$text_readonly.' onkeyup="check_reseve_qty('.$counter_tree.',this.value);" />
				</td>
				
				<td class="td5">
					<div class="col-md-9">
					<input type="number" min="0" class="form-control pt'.$childNode['bom_trn_id'].'" name="in_process_qty'.$counter_tree.'" id="in_process_qty'.$counter_tree.'" '.$readonly.' onkeypress="return isNumberKey(event)" onkeyup="get_inhouse_inner('.$counter_tree.','.$childNode['bom_trn_id'].');check_req_qty('.$counter_tree.',this.value);" />
					</div>
					<div class="col-md-3" style="font-size:16px;">
						<strong>'.$chil_unit1['process_unit'].'</strong>
						<input type="hidden" class="form-control" name="process_unit'.$counter_tree.'" id="process_unit'.$counter_tree.'" value="'.$chil_unit1['product_base_unit'].'"  />
					</div>
					
					<input type="hidden" class="form-control inpc'.$childNode['bom_trn_id'].'" name="in_process_qty_check'.$counter_tree.'" id="in_process_qty_check'.$counter_tree.'" value="'.$in_check_qty.'"  />
				</td>
				
				<td class="td5">
					<div class="col-md-9">
						<input type="number" min="0" class="form-control po'.$childNode['bom_trn_id'].'" name="po_qty'.$counter_tree.'" id="po_qty'.$counter_tree.'" onkeypress="return isNumberKey(event);" onkeyup="check_req_qty('.$counter_tree.',this.value);" />
					</div>
					<div class="col-md-3" style="font-size:16px;">
						<strong>'.$chil_unit1['purchase_unit'].'</strong>
						<input type="hidden" class="form-control" name="purchase_unit'.$counter_tree.'" id="purchase_unit'.$counter_tree.'" value="'.$chil_unit1['product_uom'].'"  />
					</div>
					<input type="hidden" class="form-control" name="pr_id'.$counter_tree.'" id="pr_id'.$counter_tree.'"  value="'.$childNode['product_id'].'"  />
					<input type="hidden" class="form-control" name="pr_type'.$counter_tree.'" id="pr_type'.$counter_tree.'" value="'.$childNode['product_type'].'" />
				</td>
				<td class="td5">'.$btn.'
					<input type="hidden" class="perent'.$childNode['bom_trn_id'].'" name="perent'.$counter_tree.'" id="perent'.$counter_tree.'" value="'.$pid1.'" />
					
					<input type="hidden" class="submi'.$childNode['bom_trn_id'].'" name="submi[]" id="submi'.$counter_tree.'" value="'.$submi.'" />
				</td>
				</tr>';
				
			
				$cnt++;$cntt++;
				$pid12=check_requested($dbcon,$childNode['product_id'],$req_no);
				//get_tree_request($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom,$new_number,$childNode['product_qty'],$childNode['bom_trn_id'],$childNode['product_type'],$pro_setting,$req_no,$bom_real_level,$pid12);
				get_tree_request($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom,$new_number,$one_q,$childNode['bom_trn_id'],$childNode['product_type'],$pro_setting,$req_no,$bom_real_level,$pid12);
				
			}
			else
			{
				
				$pro_setting=get_pro_field($dbcon,$childNode['product_id'],'product_setting_check');
				
				$pr_setting_arr=explode(",",$pro_setting);
				
				if(in_array("process_product",$pr_setting_arr))
				{
					$readonly="";
					$in_check_qty="";
				}
				else
				{
					$readonly="readonly";
					$in_check_qty="1";
				}
				
				
				
				if($bom_real_level==1)
				{
					$display='display:none';
				}
				else if(check_level_open($dbcon,$childNode['parent_id'],$req_no)>0)
				{
					$display='display:block';
				}
				else
				{
					$display='display:none';
				}
				
				
				$new_number=$number.'.'.$cntt;
				$counter_tree++;
				$rev_sto=check_reserve_stock($dbcon,$childNode['product_id'],$req_no);
				if(check_requested($dbcon,$childNode['product_id'],$req_no)==0)
				{
					$kp2=str_replace(".","",$new_number);
					$btn='<a class="btn btn-primary dispbtn csb'.$childNode['bom_trn_id'].' sho'.$kp2.' " data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.','.$childNode['bom_trn_id'].');show_btn('.$kp2.',1);change_status('.$kp2.');" ><i class="fa fa-paper-plane"></i> Request</a>';
			//style="'.$display.'"
					$btn.='<input type="hidden" name="btn_validate" required id="btn_validate" value="1" />';
					
					$btn.='<input type="hidden" id="reqsho'.$kp2.'" value="1" />';
					
					$submi="1";
				
				}
				else
				{
					$kp2=str_replace(".","",$new_number);
					//$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
					$btn='<a class="btn btn-danger rsho'.$kp2.'" data-original-title="" data-toggle="tooltip" data-placement="top" >Requested</a>';
						
						$btn.='<input type="hidden" name="btn_validate" required id="btn_validate" value="1" />';
						
					$btn.='<input type="hidden" id="reqsho'.$kp2.'" value="0" />';
					$submi="0";
				}
				$get_unit2 = "select btrn.product_uom,btrn.product_base_unit,umst.unit_name as purchase_unit,umst1.unit_name as process_unit from tbl_bomtrn as btrn
				left join unit_mst as umst on umst.unitid=btrn.product_uom
				left join unit_mst as umst1 on umst1.unitid=btrn.product_base_unit
				where bom_trn_id = '".$childNode['bom_trn_id']."'";
			$res_unit2 = $dbcon->query($get_unit2);
			$chil_unit2 = mysqli_fetch_assoc($res_unit2);
			
			$cstock=get_current_stock_new($dbcon,$childNode['product_id'],$chil_unit2["product_uom"]);
			$rstock=reserve_stock($dbcon,$childNode['product_id'],$chil_unit2["product_uom"]);
			$actualstock=$cstock-$rstock;
			if($actualstock<"0"){
				$actualstock=0;
			}
			$reqhi="";
			if($actualstock==0){
				$reqhi="readonly";
			}
			//$tital1 = get_product_type_by_id($dbcon,get_pro_field($dbcon,$childNode['product_id'],'product_type');
				echo '<tr>
				<td  class="td1">'.$new_number.'</td>
				<td   class="td2" title="'.get_product_type_by_id($dbcon,get_pro_field($dbcon,$childNode['product_id'],'product_type')).'" >'.$pro_name.'</td>
				<!--<td class="td4">'.get_product_type_by_id($dbcon,get_pro_field($dbcon,$childNode['product_id'],'product_type')).'</td>-->
				<td class="td4">'.get_pro_field($dbcon,$childNode['product_id'],'product_min_stock').'</td>
				<!--<td class="td5">'.get_current_stock_new($dbcon,$childNode['product_id'],$chil_unit2["product_uom"]).'</td>
				<td class="td5">'.reserve_stock($dbcon,$childNode['product_id'],$chil_unit2["product_uom"]).'</td>-->
				<td class="td5">
					<input type="number"  class="form-control" name="at_stock'.$counter_tree.'" id="at_stock'.$counter_tree.'" onkeypress="return isNumberKey(event)" value="'.$actualstock.'" readonly />
				</td>
				<td class="td5">
					<div class="col-md-9">
					<input type="number" min="0" class="form-control rt'.$childNode['bom_trn_id'].'" name="req_qty'.$counter_tree.'" id="req_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)"  />
					</div>
					<div class="col-md-3" style="font-size:16px;">
						<strong>'.$chil_unit2["purchase_unit"].'</strong>
					</div>
					<!--<input type="text" class="form-control tct'.$childNode['bom_trn_id'].'" name="total_qty'.$counter_tree.'" id="total_qty'.$counter_tree.'" value="'.$childNode['product_qty'].'"  />-->
					<input type="hidden" class="form-control tct'.$childNode['bom_trn_id'].'" name="total_qty'.$counter_tree.'" id="total_qty'.$counter_tree.'" value="'.$one_q.'"  />
					
				</td>
				<td class="td5">
					<input type="number"  class="form-control" name="at_reserve'.$counter_tree.'" id="at_reserve'.$counter_tree.'" onkeypress="return isNumberKey(event)" value="'.$rev_sto.'" '.$reqhi.' '.$text_readonly.' onkeyup="check_reseve_qty('.$counter_tree.',this.value);" />
				</td>
				
				<td class="td5">
					<div class="col-md-9">
					<input type="number" min="0" class="form-control pt'.$childNode['bom_trn_id'].'" name="in_process_qty'.$counter_tree.'" id="in_process_qty'.$counter_tree.'" '.$readonly.' onkeypress="return isNumberKey(event)" onkeyup="get_inhouse_inner('.$counter_tree.','.$childNode['bom_trn_id'].');check_req_qty('.$counter_tree.',this.value);" />
					</div>
					<div class="col-md-3" style="font-size:16px;">
						<strong>'.$chil_unit2["process_unit"].'</strong>
						<input type="hidden" class="form-control" name="process_unit'.$counter_tree.'" id="process_unit'.$counter_tree.'" value="'.$chil_unit2["product_base_unit"].'"  />
					</div>
					
					<input type="hidden" class="form-control inpc'.$childNode['bom_trn_id'].'" name="in_process_qty_check'.$counter_tree.'" id="in_process_qty_check'.$counter_tree.'" value="'.$in_check_qty.'"  />
				</td>
			
				<td class="td5">
					<div class="col-md-9">
					<input type="number" min="0" class="form-control po'.$childNode['bom_trn_id'].'" name="po_qty'.$counter_tree.'" id="po_qty'.$counter_tree.'" onkeypress="return isNumberKey(event);" onkeyup="check_req_qty('.$counter_tree.',this.value);" />
					</div>
					<div class="col-md-3" style="font-size:16px;">
						<strong>'.$chil_unit2["purchase_unit"].'</strong>
						<input type="hidden" class="form-control" name="purchase_unit'.$counter_tree.'" id="purchase_unit'.$counter_tree.'" value="'.$chil_unit2["product_uom"].'"  />
					</div>
					
					<input type="hidden" class="form-control" name="pr_id'.$counter_tree.'" id="pr_id'.$counter_tree.'" value="'.$childNode['product_id'].'"  />
					<input type="hidden" class="form-control" name="pr_type'.$counter_tree.'" id="pr_type'.$counter_tree.'" value="'.$childNode['product_type'].'" />
				</td>
				<td class="td5">'.$btn.'
					<input type="hidden" class="perent'.$childNode['bom_trn_id'].'" name="perent'.$counter_tree.'" id="perent'.$counter_tree.'" value="'.$pid1.'" />
					
					<input type="hidden" class="submi'.$childNode['bom_trn_id'].'" name="submi[]" id="submi'.$counter_tree.'" value="'.$submi.'" />
				</td>
				</tr>';
				
				
				$level++;$cnt++;$cntt++;
				//get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);
				
			}
			
			//$cntt++;
			
		}
		
		//return $str_tree;
	}
	 
	return $counter_tree;
}


function get_tree_request_jobwork($dbcon,$product_id,$parent_id,$level,$cnt,$bom,$number,$qty,$bom_trn_id,$ptype)
{
	global $counter_tree;
	
	if($level==0)
	{
		$pr_value=get_pro_field($dbcon,$product_id,'product_name');
		echo '
				<td class="td1">'.$number.'</td>
				<td class="td2">'.$pr_value.'</td>
				<td class="td4">'.get_pro_field($dbcon,$product_id,'product_min_stock').'</td>
				<td class="td5">'.get_product_stock($dbcon,$product_id).'</td>
				<td class="td5">
					20
				</td>
				<td class="td5">
					20
				</td>
				
				<td class="td5"><a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Allocate</a></td>
				';
				
	}
					
	$getChildNodes = "select * from tbl_bomtrn where parent_id = '".$bom_trn_id."' and bom_id='$bom'";
	$resChildNodes = $dbcon->query($getChildNodes);
	if(mysqli_num_rows($resChildNodes) > 0)
	{

		echo '<tr>';
		
		$cntt=1;
		while($childNode = mysqli_fetch_assoc($resChildNodes))
		{
			$pro_name=get_pro_field($dbcon,$childNode['product_id'],'product_name');
			
			$getChildNodes1 = "select * from tbl_bomtrn where parent_id = '".$childNode['bom_trn_id']."' and bom_id='$bom'";
			$resChildNodes1 = $dbcon->query($getChildNodes1);
			if(mysqli_num_rows($resChildNodes1) > 0)
			{
				$new_number=$number.'.'.$cntt;
				$counter_tree++;
				echo '<tr>
				<td  class="td1">'.$new_number.'</td>
				<td class="td2">'.$pro_name.'</td>
				<td class="td4">'.get_pro_field($dbcon,$childNode['product_id'],'product_min_stock').'</td>
				<td class="td5">'.get_product_stock($dbcon,$childNode['product_id']).'</td>
				<td class="td5">
					20
				</td>
				<td class="td5">
					20
				</td>
				
				<td class="td5"><a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Allocate</a></td>
				</tr>';
				
			
				$level++;$cnt++;$cntt++;
				
				get_tree_request_jobwork($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom,$new_number,$childNode['product_qty'],$childNode['bom_trn_id'],$childNode['product_type']);
				
			}
			else
			{
				$new_number=$number.'.'.$cntt;
				$counter_tree++;
				echo '<tr>
				<td  class="td1">'.$new_number.'</td>
				<td   class="td2">'.$pro_name.'</td>
				<td class="td4">'.get_pro_field($dbcon,$childNode['product_id'],'product_min_stock').'</td>
				<td class="td5">'.get_product_stock($dbcon,$childNode['product_id']).'</td>
				<td class="td5">
					20
				</td>
				<td class="td5">
					20
				</td>
				
				<td class="td5"><a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Allocate</a></td>
				</tr>';
				
				
				$level++;$cnt++;$cntt++;
				//get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);
				
			}
			
			//$cntt++;
		}
		
	
	}
	 
	return $counter_tree;
}


function get_product_stock($dbcon,$productid)
{
	 $query='SELECT pro.product_id as pid,"product" as type,product_name as pr_name,product_min_stock as min_stock,product_opening as op_stock  FROM `product_mst` as pro 
	
	 where pro.product_id='.$productid;
	 
	 $rows=mysqli_fetch_assoc($dbcon->query($query));
	
	 $stock=$rows['op_stock'];

	 return $stock;
}

function get_bom_resrev_stock($dbcon,$productid,$bom_id)
{
	$query='select sum(bomtrn.product_qty) as bomqty,bomtrn.product_id from tbl_pln_bomtrn as bomtrn 
	inner join tbl_planning_ordertrn as bom on bom.sales_ordertrn_id=bomtrn.sales_order_trn_id 
	where bomtrn.so_bom_trn_status=0 and bomtrn.product_id='.$productid.' group by bomtrn.product_id';
	$rows=mysqli_fetch_assoc($dbcon->query($query));
	
	return floatval($rows['bomqty']);
	
}

function get_check_request($dbcon,$bom_trn_id,$bom,$planning_id){
	 $getChildNodes = "select purchaseordertrn_id from tbl_purchaseordertrn where po_bom_trn_id ='$bom_trn_id'  and po_bom_id='$bom' and po_ref_id='$planning_id' and po_ref_type='planning'";
	 
	$resChildNodes = $dbcon->query($getChildNodes);
	$count=mysqli_num_rows($resChildNodes);
	//$childNode = mysqli_fetch_assoc($resChildNodes);
	
	return $count;
}

function get_tree_bom_po($dbcon,$product_id,$parent_id,$level,$cnt,$bom,$number,$qty,$bom_trn_id,$planning_id)
{
	$current_stock= 0;$resev_stock = 0;$out_of_stock = 0;
	$check_r=get_check_request($dbcon,$bom_trn_id,$bom,$planning_id);
		if($check_r > 0){
				$dyn_text='Requested';
			}
			else{
				$dyn_text='<input type="checkbox" name="bom_trn_id[]" class="chk_box" id="bom_trn_id'.$i.'" value="'.$bom_trn_id.'" style="width: 23px;height: 23px;margin-top: 0px;">';
			}
	if($level==0)
	{
		$pr_value=get_pro_field($dbcon,$product_id,'product_name');
		
		$current_stock = get_product_stock($dbcon,$product_id);
		$resev_stock = get_bom_resrev_stock($dbcon,$product_id);
		$resev_stock = ($resev_stock) - floatval($qty);
		$out_of_stock = $current_stock-$resev_stock-$qty;
		if($out_of_stock>0){
			$out_of_stock = 0;
		}else{
			$out_of_stock = abs($out_of_stock);
		}
		//$upd_out_of_stock = upd_out_of_stock_qty($out_of_stock, $bom_trn_id,$dbcon);
		
		
		echo '
				<td class="td1">'.$number.'</td>
			<td class="td2">'.$pr_value.'</td>
			<td class="td3">'.$current_stock.'</td>
			<td class="td3">'.$resev_stock.'</td>
			<td class="td3">'.$qty.'</td>
			<td class="td3">'.$out_of_stock.'</td>
			<td class="td4">'.$dyn_text.'</td>
				';
				
	}
					
	$getChildNodes = "select * from tbl_bomtrn where parent_id = '".$bom_trn_id."' and bom_id='$bom'";
	$resChildNodes = $dbcon->query($getChildNodes);
	if(mysqli_num_rows($resChildNodes) > 0)
	{

		echo '<tr>';
		
		$cntt=1;$current_stock1 = 0;$resev_stock1 = 0;$out_of_stock1 = 0;
		while($childNode = mysqli_fetch_assoc($resChildNodes))
		{
			$pro_name=get_pro_field($dbcon,$childNode['product_id'],'product_name');
			$current_stock1 = get_product_stock($dbcon,$childNode['product_id']);
			$resev_stock1 = get_bom_resrev_stock($dbcon,$childNode['product_id']);
			$resev_stock1 = ($resev_stock1) - floatval($childNode['product_qty']);
			$out_of_stock1 =	$current_stock1-$resev_stock1-$childNode['product_qty'];
			if($out_of_stock1>0){
				$out_of_stock1 = 0;
			}else{
				$out_of_stock1 = abs($out_of_stock1);
			}
			
			$pro_name=get_pro_field($dbcon,$childNode['product_id'],'product_name');
			
			$getChildNodes1 = "select * from tbl_bomtrn where parent_id = '".$childNode['bom_trn_id']."' and bom_id='$bom'";
			$resChildNodes1 = $dbcon->query($getChildNodes1);
			if(mysqli_num_rows($resChildNodes1) > 0)
			{
				$new_number=$number.'.'.$cntt;
				
				$check_r1=get_check_request($dbcon,$childNode['bom_trn_id'],$bom,$planning_id);
					if($check_r1 > 0){
							$dyn_text1='Requested';
						}
						else{
							$dyn_text1='<input type="checkbox" name="bom_trn_id[]" class="chk_box" id="bom_trn_id'.$i.'" value="'.$childNode['bom_trn_id'].'" style="width: 23px;height: 23px;margin-top: 0px;">';
						}
				
				echo '<tr>
				<td  class="td1">'.$new_number.'</td>
				<td class="td2">'.$pro_name.'</td>
				<td class="td3">'.$current_stock1.'</td>
				<td class="td3">'.$resev_stock1.'</td>
				<td class="td3">'.$childNode['product_qty'].'</td>
				<td class="td3">'.$out_of_stock1.'</td>
				<td class="td4">'.$dyn_text1.'</td>
				</tr>';
				
			
				$level++;$cnt++;$cntt++;
				
				get_tree_bom_po($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom,$new_number,$childNode['product_qty'],$childNode['bom_trn_id'],$planning_id);
				
			}
			else
			{
				$new_number=$number.'.'.$cntt;
				
				$check_r2=get_check_request($dbcon,$childNode['bom_trn_id'],$bom,$planning_id);
					if($check_r2 > 0){
							$dyn_text2='Requested';
						}
						else{
							$dyn_text2='<input type="checkbox" name="bom_trn_id[]" class="chk_box" id="bom_trn_id'.$i.'" value="'.$childNode['bom_trn_id'].'" style="width: 23px;height: 23px;margin-top: 0px;">';
						}
				
				echo '<tr>
				<td  class="td1">'.$new_number.'</td>
				<td   class="td2">'.$pro_name.'</td>
				<td class="td3">'.$current_stock1.'</td>
				<td class="td3">'.$resev_stock1.'</td>
				<td class="td3">'.$childNode['product_qty'].'</td>
				<td class="td3">'.$out_of_stock1.'</td>
				<td class="td4">'.$dyn_text2.'</td>
				</tr>';
				
				
				$level++;$cnt++;$cntt++;
				//get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);
				
			}
			
			//$cntt++;
		}
		
	
	}
	 
}

function get_service_charge($dbcon,$id)
{
	$q=$dbcon->query("select sum(comp_amount) as amount from tbl_complaint_trn where complaint_id='$id'");
	$row=mysqli_fetch_array($q);
	return $row['amount'];
}

function get_spare_part_rate($dbcon,$id)
{
	$q=$dbcon->query("select sum(s_amount) as amount from tbl_complain_spare_part where s_comp_id='$id' and s_paid_status='paid'");
	$row=mysqli_fetch_array($q);
	return $row['amount'];
}



function get_tree_complain($dbcon,$product_id,$parent_id,$level,$cnt,$bom,$number,$eid,$bom_trn_id)
{
	
		
	 //STATIC $counter_tree = 0;
	global $counter_tree;
	
   //$counter_tree++;
	
	if($level==0)
	{
		
		if($eid!='')
		{
			$qe=$dbcon->query("select * from tbl_complain_spare_part where s_comp_id='$eid' and s_product='$product_id'");
			
			if(mysqli_num_rows($qe)>0)
			{
				$re=mysqli_fetch_array($qe);
				
				$checked=" checked";
				$epstatus=$re['s_paid_status'];
				$eqty=$re['s_qty'];
				$erate=$re['s_rate'];
				$eamount=$re['s_amount'];
				$ecanme=$re['s_courier_name'];
				$ecno=$re['s_courier_no'];
				$ecdel=date("d/m/Y",strtotime($re['s_courier_del_date']));
				$esent=$re['sp_sent_status'];
				$readonly="";
			}
			else
			{
				$checked=" ";
				$epstatus="";
				$eqty="";
				$erate="";
				$eamount="";
				$ecanme="";
				$ecno="";
				$ecdel="";
				$esent="";
				$readonly="readonly";
			}
		}
		else
		{
			$checked=" ";
			$epstatus="";
			$eqty="";
			$erate="";
			$eamount="";
			$ecname="";
			$ecno="";
			$ecdel="";
			$esent="";
			$readonly="readonly";
		}
		
		$pr_value=get_pro_field($dbcon,$product_id,'product_name');
		echo '
				<td class="td1">'.$number.'<input type="hidden" name="sp_no[]" value="'.$number.'" /></td>
				<td class="td2">'.$pr_value.'<input type="hidden" name="sp_pid[]" value="'.$product_id.'" /></td>
				<td class="td3"><input type="checkbox" name="sp_part[]" id="chk'.$counter_tree.'" value="'.$counter_tree.'" onchange="enable_text(this.value)" '.$checked.'  /></td>
				<td class="td3">
					<select class="form-control" name="sp_free[]" id="sp_free'.$counter_tree.'" >
						<option value="">--select value--</option>
						<option value="free" '.($epstatus=='free' ? 'selected="selected"' : '').'>Free</option>
						<option value="paid" '.($epstatus=='paid' ? 'selected="selected"' : '').'>Paid</option>
					</select>
				</td>
				<td class="td3"><input type="text" class="form-control" name="sp_qty[]" id="qty'.$counter_tree.'" placeholder="quantity" '.$readonly.' onkeyup="get_amount_spare('.$counter_tree.')" value="'.$eqty.'" onkeypress="return isNumberKey(event)" /></td>
				<td class="td3"><input type="text" class="form-control" name="sp_rate[]" id="rate'.$counter_tree.'" placeholder="rate" '.$readonly.'  onkeyup="get_amount_spare('.$counter_tree.')" value="'.$erate.'" onkeypress="return isNumberKey(event)" /></td>
				<td class="td3"><input type="text" class="form-control" name="sp_amount[]"  id="amt'.$counter_tree.'" placeholder="amount" '.$readonly.' value="'.$eamount.'"  /></td>
				<td class="td3"><input type="text" class="form-control" name="sp_courier_name[]" id="cname'.$counter_tree.'" placeholder="courier Name" '.$readonly.' value="'.$ecname.'" /></td>
				<td class="td3"><input type="text" class="form-control" name="sp_courier_no[]" id="cno'.$counter_tree.'" placeholder="courier No" '.$readonly.' value="'.$ecno.'" /></td>
				<td class="td3"><input type="text" class="form-control default-date-picker" id="cdate'.$counter_tree.'" name="sp_courier_date[]" id="" placeholder="courier Date" '.$readonly.' value="'.$ecdel.'" onkeypress="return isNumberKey(event)" /></td>
				<td class="td3">
					<select class="form-control" name="sp_sent[]" id="sp_sent'.$counter_tree.'" >
						<option value="">--select Staus--</option>
						<option value="yes" '.($esent=='yes' ? 'selected="selected"' : '').'>YES</option>
						<option value="no" '.($esent=='no' ? 'selected="selected"' : '').'>NO</option>
					</select>
				</td>
				<td class="td3">
				    <!-- // Amish Soni 22-09-2020 -->
					<select class="form-control" name="old_sp_sent[]" id="old_sp_sent'.$counter_tree.'" >
						<option value="">--select Staus--</option>
						<option value="yes" '.($esent=='yes' || $esent=='' ? 'selected="selected"' : '').'>YES</option>
						<option value="no" '.($esent=='no' ? 'selected="selected"' : '').'>NO</option>
					</select>
				</td>
				';
				
	}
					
	$getChildNodes = "select * from tbl_bomtrn where parent_id = '".$bom_trn_id."' and po_visible_status=0 and bom_id='$bom'";
	$resChildNodes = $dbcon->query($getChildNodes);
	if(mysqli_num_rows($resChildNodes) > 0)
	{
		//echo '<ul class="jtree_parent_node">';
		
		$cntt=1;
		while($childNode = mysqli_fetch_assoc($resChildNodes))
		{
			if($eid!='')
			{
				$qe=$dbcon->query("select * from tbl_complain_spare_part where s_comp_id='$eid' and s_product='$childNode[product_id]'");
				
				if(mysqli_num_rows($qe)>0)
				{
					$re=mysqli_fetch_array($qe);
					
					$checked=" checked";
					$epstatus=$re['s_paid_status'];
					$eqty=$re['s_qty'];
					$erate=$re['s_rate'];
					$eamount=$re['s_amount'];
					$ecanme=$re['s_courier_name'];
					$ecno=$re['s_courier_no'];
					$esent=$re['sp_sent_status'];
					$ecdel=date("d/m/Y",strtotime($re['s_courier_del_date']));
					$readonly="";
					
				}
				else
				{
					$checked=" ";
					$epstatus="";
					$eqty="";
					$erate="";
					$eamount="";
					$ecanme="";
					$ecno="";
					$ecdel="";
					$esent="";
					$readonly="readonly";
				}
			}
			else
			{
				$checked=" ";
				$epstatus="";
				$eqty="";
				$erate="";
				$eamount="";
				$ecanme="";
				$ecno="";
				$ecdel="";
				$esent="";
				$readonly="readonly";
			}
			
			$pro_name=get_pro_field($dbcon,$childNode['product_id'],'product_name');
			
			$getChildNodes1 = "select * from tbl_bomtrn where parent_id = '".$childNode['bom_trn_id']."' and po_visible_status=0 and bom_id='$bom'";
			$resChildNodes1 = $dbcon->query($getChildNodes1);
			if(mysqli_num_rows($resChildNodes1) > 0)
			{
				$new_number=$number.'.'.$cntt;
				$counter_tree++;
				echo '<tr>
				<td class="td1">'.$new_number.'<input type="hidden" name="sp_no[]" value="'.$new_number.'" /></td>
				<td class="td2">'.$pro_name.'<input type="hidden" name="sp_pid[]" value="'.$childNode['product_id'].'" /></td>
				<td class="td3"><input type="checkbox" name="sp_part[]" id="chk'.$counter_tree.'" value="'.$counter_tree.'"  onchange="enable_text(this.value)" '.$checked.'  /></td>
				<td class="td3">
					<select class="form-control" name="sp_free[]" id="sp_free'.$counter_tree.'">
						<option value="">--select value--</option>
						<option value="free" '.($epstatus=='free' ? 'selected="selected"' : '').'>Free</option>
						<option value="paid" '.($epstatus=='paid' ? 'selected="selected"' : '').'>Paid</option>
					</select>
				</td>
				<td class="td3"><input type="text" class="form-control" name="sp_qty[]" id="qty'.$counter_tree.'" placeholder="quantity" '.$readonly.'  onkeyup="get_amount_spare('.$counter_tree.')" value="'.$eqty.'" onkeypress="return isNumberKey(event)" /></td>
				<td class="td3"><input type="text" class="form-control" name="sp_rate[]" id="rate'.$counter_tree.'" placeholder="rate" '.$readonly.'  onkeyup="get_amount_spare('.$counter_tree.')" value="'.$erate.'" onkeypress="return isNumberKey(event)" /></td>
				<td class="td3"><input type="text" class="form-control" name="sp_amount[]" id="amt'.$counter_tree.'"  placeholder="amount" '.$readonly.' value="'.$eamount.'" onkeypress="return isNumberKey(event)" /></td>
				<td class="td3"><input type="text" class="form-control" name="sp_courier_name[]" id="cname'.$counter_tree.'" placeholder="courier Name" '.$readonly.' value="'.$ecanme.'" /></td>
				<td class="td3"><input type="text" class="form-control" name="sp_courier_no[]" id="cno'.$counter_tree.'" placeholder="courier No" '.$readonly.' value="'.$ecno.'" /></td>
				<td class="td3"><input type="text" class="form-control  default-date-picker valid" id="cdate'.$counter_tree.'" name="sp_courier_date[]" id="" placeholder="courier Date" '.$readonly.' value="'.$ecdel.'" onkeypress="return isNumberKey(event)" /></td>
				<td class="td3">
					<select class="form-control" name="sp_sent[]" id="sp_sent'.$counter_tree.'" >
						<option value="">--select Staus--</option>
						<option value="yes" '.($esent=='yes' ? 'selected="selected"' : '').'>YES</option>
						<option value="no" '.($esent=='no' ? 'selected="selected"' : '').'>NO</option>
					</select>
				</td>
				<td class="td3">
				    <!-- // Amish Soni 22-09-2020 -->
					<select class="form-control" name="old_sp_sent[]" id="old_sp_sent'.$counter_tree.'" >
						<option value="">--select Staus--</option>
						<option value="yes" '.($esent=='yes' || $esent=='' ? 'selected="selected"' : '').'>YES</option>
						<option value="no" '.($esent=='no' ? 'selected="selected"' : '').'>NO</option>
					</select>
				</td>
				</tr>';
			
				$level++;$cnt++;$cntt++;
				
				get_tree_complain($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom,$new_number,$eid,$childNode['bom_trn_id']);
				
			}
			else
			{
				$new_number=$number.'.'.$cntt;
				$counter_tree++;
				echo '<tr data-node-id="'.$new_number.'" data-node-pid="'.$number.'">
				<td class="td1">'.$new_number.'<input type="hidden" name="sp_no[]" value="'.$new_number.'" /></td>
				<td class="td2">'.$pro_name.'<input type="hidden" name="sp_pid[]" value="'.$childNode['product_id'].'" /></td>
				<td class="td3"><input type="checkbox" name="sp_part[]" id="chk'.$counter_tree.'" value="'.$counter_tree.'" onchange="enable_text(this.value)" '.$checked.'  /></td>
				<td class="td3">
					<select class="form-control" name="sp_free[]" id="sp_free'.$counter_tree.'">
						<option value="">--select value--</option>
						<option value="free" '.($epstatus=='free' ? 'selected="selected"' : '').'>Free</option>
						<option value="paid" '.($epstatus=='paid' ? 'selected="selected"' : '').'>Paid</option>
					</select>
				</td>
				<td class="td3"><input type="text" class="form-control" name="sp_qty[]" id="qty'.$counter_tree.'" placeholder="quantity" '.$readonly.'  onkeyup="get_amount_spare('.$counter_tree.')" value="'.$eqty.'" onkeypress="return isNumberKey(event)" /></td>
				<td class="td3"><input type="text" class="form-control" name="sp_rate[]" id="rate'.$counter_tree.'" placeholder="rate" '.$readonly.'  onkeyup="get_amount_spare('.$counter_tree.')" value="'.$erate.'" onkeypress="return isNumberKey(event)" /></td>
				<td class="td3"><input type="text" class="form-control" name="sp_amount[]" id="amt'.$counter_tree.'"  placeholder="amount" '.$readonly.' value="'.$eamount.'" onkeypress="return isNumberKey(event)" /></td>
				<td class="td3"><input type="text" class="form-control" name="sp_courier_name[]" id="cname'.$counter_tree.'" placeholder="courier Name" '.$readonly.' value="'.$ecname.'" /></td>
				<td class="td3"><input type="text" class="form-control" name="sp_courier_no[]" id="cno'.$counter_tree.'" placeholder="courier No" '.$readonly.' value="'.$ecno.'" /></td>
				<td class="td3"><input type="text" class="form-control  default-date-picker valid" id="cdate'.$counter_tree.'" name="sp_courier_date[]" id="" placeholder="courier Date" '.$readonly.' value="'.$ecdel.'" onkeypress="return isNumberKey(event)" /></td>
				<td class="td3">
					<select class="form-control" name="sp_sent[]" id="sp_sent'.$counter_tree.'" >
						<option value="">--select Staus--</option>
						<option value="yes" '.($esent=='yes' ? 'selected="selected"' : '').'>YES</option>
						<option value="no" '.($esent=='no' ? 'selected="selected"' : '').'>NO</option>
					</select>
				</td>
				<td class="td3">
				    <!-- // Amish Soni 22-09-2020 -->
					<select class="form-control" name="old_sp_sent[]" id="old_sp_sent'.$counter_tree.'" >
						<option value="">--select Staus--</option>
						<option value="yes" '.($esent=='yes' || $esent=='' ? 'selected="selected"' : '').'>YES</option>
						<option value="no" '.($esent=='no' ? 'selected="selected"' : '').'>NO</option>
					</select>
				</td>
				</tr>';
				
				$level++;$cnt++;$cntt++;
				//get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);
				
			}
			
			//$cntt++;
		}
		
		
	}
	
	return $counter_tree;
	
}



function get_pro_field($dbcon,$product_id,$field_name){
	$get_pro_qry = "select $field_name from product_mst where product_id=".$product_id;
	$get_qry_rrl  = mysqli_fetch_assoc($dbcon->query($get_pro_qry));
	return $get_qry_rrl[$field_name];	
	//return $get_pro_qry;	
}

function get_tax_field_tax_id($dbcon,$tid,$field_name)
{
	$q=$dbcon->query("select $field_name from tbl_tax where tax_id='$tid'");
	$row=mysqli_fetch_array($q);
	return $row[$field_name];
}

function get_total_tax($dbcon,$pamount,$formula)
{
	$tax_total=0;
	foreach($formula as $f)
	{
		$tax_value=get_tax_field_tax_id($dbcon,$f,'tax_value');
		$tax=($tax_value*$pamount)/100;
		$tax_total+=$tax;
	}
	
	return $tax_total;
}

function getbank($dbcon,$bankid,$con='')
{
	$bank='';
	$qry="select * from bank_mst where bank_status=0".$con;
	$rs_type=$dbcon->query($qry);	
	$bank .='<option value="" selected="selected">Choose Bank</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$sel='';
		if($row['bankid']==$bankid)
		{$sel='selected="selected"';}
		$bank .= '<option '.$sel.' value="'.$row['bankid'].'">'.$row['bank_name'].'</option>';
	}
	return $bank;
}

function get_sitemap_pro($dbcon,$i,$p_name)
{
	$qry="select * from tbl_bomtrn where bom_level='$i' and bom_trn_id='$p_name'";
	$rs_type=$dbcon->query($qry);
	$row=mysqli_fetch_assoc($rs_type);
	return get_pro_field($dbcon,$row['product_id'],'product_name');
}

function generate_sitemap($dbcon,$id) { 

   $query_bom = "SELECT * FROM tbl_bomtrn WHERE bom_trn_id = '".$id."'";
   $rsCategoryId = $dbcon->query($query_bom);
   $row_rsCategoryId = mysqli_fetch_assoc($rsCategoryId);
   $parent = $row_rsCategoryId['parent_id'];

   if ($parent != 0) {
	  generate_sitemap($dbcon,$parent);
	  echo '<li>';
   } else {
	  echo '<li>';
   }

	echo '<a href="'.ROOT."bom_allocate/".$id.'">
   '.get_pro_field($dbcon,$row_rsCategoryId['product_id'],'product_name').' </a>
   </li>';
}

function get_product_by_planning($dbcon,$id)
{
	$str='';
	$query="Select pl.product_id,pl.pl_order_id,p.product_name from tbl_planning_ordertrn as pl  inner join product_mst as p on p.product_id=pl.product_id where pl.pl_ordertrn_status=0 and pl.pl_order_id='$id'";
	$rs_type=$dbcon->query($query);
    if($id=='0'){ $psel='selected="selected"';}
	$str ='<option value="" >--Choose Product--</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$sel='';
		if($row['product_id']==$id)
		{$sel='selected="selected"';}
		
		$str .= '<option '.$sel.' value="'.$row['product_id'].'">'.$row['product_name'].'</option>';
	}
	return $str;
}

function get_bom_id_by_product($dbcon,$id)
{
	$q=$dbcon->query("select bom_id from tbl_bom where bom_product='$id' and bom_status='0'");
	$row=mysqli_fetch_array($q);
	return $row['bom_id'];
}

function get_po_for_grn($dbcon,$purchaseorder_id,$vender_id,$mode)
{
	$str='';
		$query="select * from tbl_purchaseorder as so where status=0 and (select IFNULL(sum(product_qty),0) as qty from tbl_purchaseordertrn as sosub  where purchaseordertrn_status=0 and sosub.purchaseorder_id=so.purchaseorder_id ) > (select IFNULL(sum(product_qty),0) as qty  from tbl_grn as chall left join tbl_grn_trn as chtrn on chtrn.grn_id=chall.grn_id where grn_status=0 and chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=so.purchaseorder_id) and so.po_approval_status=1 and so.vender_id=".$vender_id." and company_id=".$_SESSION['company_id'];
		 
	if($mode=='Edit'){
		 $query_edit="select purchaseorder_id,purchaseorder_no from tbl_purchaseorder where status=0 and purchaseorder_id=".$purchaseorder_id." and vender_id=".$vender_id." and company_id=".$_SESSION['company_id'];
		$rs_dispatc=$dbcon->query($query_edit);	
	}
	
	$rs_dispatch=$dbcon->query($query);	
	$str = '<option value="">Choose Purchase Order</option>';
	while($res=mysqli_fetch_assoc($rs_dispatc))
	{	
		$sel=''; 
		if($res['purchaseorder_id']==$purchaseorder_id)
		{$sel ="selected='selected'";}
		$str .= '<option '.$sel.' value="'.$res['purchaseorder_id'].'">'.$res['purchaseorder_no'].'</option>';
	}
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		if($rel['purchaseorder_id']==$purchaseorder_id)
		{$sel ="selected='selected'";}
		$str .= '<option '.$sel.' value="'.$rel['purchaseorder_id'].'">'.$rel['purchaseorder_no'].'</option>';
	}
	return $str;
}

function get_all_po_for_grn($dbcon,$purchaseorder_id,$vender_id,$mode)
{
		if(!empty($vender_id)){
			$ven=" and vender_id=".$vender_id;
		}
	$str='';
		$query="select * from tbl_purchaseorder as so where status=0 and so.po_approval_status=1 and used_status=0 ".$ven." and company_id=".$_SESSION['company_id'];
		
		/* $query="select * from tbl_purchaseorder as so where status=0 and (select IFNULL(sum(product_qty),0) as qty from tbl_purchaseordertrn as sosub  where purchaseordertrn_status=0 and sosub.purchaseorder_id=so.purchaseorder_id ) > (select IFNULL(sum(product_qty),0) as qty  from tbl_grn as chall left join tbl_grn_trn as chtrn on chtrn.grn_id=chall.grn_id where grn_status=0 and chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=so.purchaseorder_id) and so.po_approval_status=1 and used_status=0 ".$ven." and company_id=".$_SESSION['company_id']; */
		
	if($mode=='Edit'){
		 $query="select purchaseorder_id,purchaseorder_no from tbl_purchaseorder where status=0 and purchaseorder_id=".$purchaseorder_id." and company_id=".$_SESSION['company_id'];
		//$rs_dispatc=$dbcon->query($query_edit);	
	}
	
	$rs_dispatch=$dbcon->query($query);	
	$str = '<option value="">Choose Order</option>';
	while($res=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		if($res['purchaseorder_id']==$purchaseorder_id)
		{$sel ="selected='selected'";}
		$str .= '<option '.$sel.' value="'.$res['purchaseorder_id'].'">'.$res['purchaseorder_no'].'</option>';
	}
	
	return $str;
}

function get_all_jobwork_for_grn($dbcon,$id,$vender_id,$mode)
{
	$str='';
		//$query="select * from tbl_jobwork as jo where status=0 and job_close_status=0 and company_id=".$_SESSION['company_id'];
		
		$query='select jo.*,pr.product_name,(select COALESCE(sum(p.product_qty),0) as tqty from tbl_grn as j left join tbl_grn_trn as p on p.grn_id=j.grn_id 
					where j.purchaseorder_id=jo.jobwork_id and grn_status=0 and ref_type=1 and grn_trn_status=0) as tqty from tbl_jobwork as jo 
				left join product_mst as pr on pr.product_id=jo.j_product_id 
				where jo.job_close_status="0" and jo.status="0" and  jo.company_id='.$_SESSION['company_id'].' HAVING j_qty>tqty';
		 
	if($mode=='Edit'){
		 $query="select * from tbl_jobwork where status=0 and jobwork_id=".$id." and company_id=".$_SESSION['company_id'];
		// $rs_dispatc=$dbcon->query($query_edit);	
	}
	
	$rs_dispatch=$dbcon->query($query);	
	$str = '<option value="">Choose Order</option>';
	while($res=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		if($res['jobwork_id']==$id)
		{$sel ="selected='selected'";}
		$str .= '<option '.$sel.' value="'.$res['jobwork_id'].'">'.$res['jobwork_no'].'</option>';
	}
	
	return $str;
}

function get_po_for_grn_trn($dbcon,$purchaseorder_id,$product_id,$mode)
{
	$str='';
	if($mode=='Edit'){
		$query="select trn.product_id,pro.product_name from tbl_purchaseordertrn as trn
		left join product_mst as pro on pro.product_id=trn.product_id
		where trn.purchaseordertrn_status=0 and trn.product_id=".$product_id." and trn.purchaseorder_id=".$purchaseorder_id;
	}
	else{
		$query="select trn.product_id,pro.product_name,trn.product_qty,main_grn_qty from tbl_purchaseordertrn as trn
		left join product_mst as pro on pro.product_id=trn.product_id
		left join (SELECT purchaseorder_id,product_id,sum(product_qty) as main_grn_qty FROM tbl_grn_trn as chtrn where chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=".$purchaseorder_id." group by chtrn.product_id,chtrn.purchaseorder_id) as chtrn on chtrn.product_id=trn.product_id
		where trn.purchaseordertrn_status=0 and trn.purchaseorder_id=".$purchaseorder_id." having trn.product_qty>main_grn_qty or main_grn_qty is NULL";
	}
	$rs_trn=$dbcon->query($query);	
	$str = '<option value="">Choose Product</option>';
	while($rel=mysqli_fetch_assoc($rs_trn))
	{	
		$sel='';
		if($rel['product_id']==$product_id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name'].'</option>';
	}
	return $str;
}

function get_all_parameter($dbcon,$id) {
	$str='';
	$query="select * from tbl_qc_param where p_status='0' order by p_name";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Parameter</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['p_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['p_id'].'">'.$rel['p_name'].'</option>';
	}
	return $str;
}

function get_all_grn($dbcon,$id) {
	$str='';
	$query="select * from tbl_grn where grn_status='0' and qc_status='0' order by grn_no";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose GRN</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['grn_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['grn_id'].'">'.$rel['grn_no'].'</option>';
	}
	return $str;
}

function check_delete_trn($dbcon,$chk_arr){
	$chk_flag=false;
	foreach($chk_arr as $fil_arr){
		$col=$fil_arr[0]; $tbl=$fil_arr[1]; $whr=$fil_arr[2];
		$chk_qry="select $col from $tbl where $whr";
		$chk_nums=mysqli_num_rows($dbcon->query($chk_qry));
		if($chk_nums){
			return $chk_flag=true;
		}
	}
	return $chk_flag;
}

function get_po_details_for_grn_trn($dbcon,$id,$type,$mode,$eid,$vender_id)
{
	$str='';
	if(!empty($eid)){
		$grn_ids=" and grn_id!=".$eid;
	}
	if(!empty($vender_id)){
		$ven=" and op.vender_id=".$vender_id;
	}
	if(!empty($id)){
		$po=" and po.purchaseorder_id=".$id;
	}
	$query="select po.*,sum(po.product_qty)as produ_qty,p.product_name,p.product_type,unit.unit_name,group_concat(po.purchaseordertrn_id ORDER BY po.purchaseordertrn_id ASC) as trn_id,group_concat(po.po_ref_id ORDER BY po.purchaseordertrn_id ASC) as ref_id from tbl_purchaseordertrn as po 
	left join product_mst as p on p.product_id=po.product_id
    left join unit_mst as unit on unit.unitid=po.unit_id
	left join tbl_purchaseorder as op on op.purchaseorder_id=po.purchaseorder_id
	where op.po_approval_status=1 and po.used_status=0 and purchaseordertrn_status=0 ".$ven." ".$po." group by po.product_id,po.unit_id";
	$rs_product=$dbcon->query($query);
	$cnt=1;
	while($row=mysqli_fetch_array($rs_product))
	{
		$query1="select sum(product_qty) as done_qty from tbl_grn_sub_trn as po where status=0 and purchaseordertrn_id in (".$row['trn_id'].")";
		$rs_product1=$dbcon->query($query1);
		$row1=mysqli_fetch_array($rs_product1);
		
		$pending_qty=$row['produ_qty']-$row1['done_qty'];
		$pr_setting=get_pro_field($dbcon,$row['product_id'],'product_setting_check');
		
		$pr_setting_arr=explode(",",$pr_setting);
		
		if(in_array("product_qc",$pr_setting_arr))
		{
			$qc_st="yes";
			$sty="display:none;";
		}else{
			$qc_st="no";
			$sty="";
		}
		if(!empty($eid)){
			$query11="select * from tbl_grn_trn as mst
			where mst.grn_id=".$eid." and product_id=".$row['product_id']." and purchaseorder_id=".$row['purchaseorder_id'];
			$rol=mysqli_fetch_assoc($dbcon->query($query11));
			
			if($rol['product_qc']==1){
				$ronly="readonly";
			}else{
				$ronly="";
			}
		}
		$tolerance=get_pro_field($dbcon,$row['product_id'],'tolerance');
		$maximum_tolerance=get_pro_field($dbcon,$row['product_id'],'maximum_tolerance');
		$minimum_tolerance=get_pro_field($dbcon,$row['product_id'],'minimum_tolerance');
		if($tolerance=="1"){
			// $maximum_tolerance=get_pro_field($dbcon,$row['product_id'],'maximum_tolerance');
			$pending_qty1=$pending_qty;
		}else{
			$pending_qty1=$pending_qty;
		}
		/* Code By Umair: 29/10/2020 
		   Comment: I have removed the max value from the input tag for tolerance functionality for grn module.	
		   ".$pending_qty1."
		*/
		$str.="<tr>
					<td>".$cnt."</td>
					<td>".get_product_type_name($dbcon,$row['product_type'])." ".$row['abc']."</td>
					<td>".$row['product_name']."<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$cnt' value='".$row['product_id']."' /></td>
					<td>".$row['produ_qty']." ".$row['unit_name']."</td>
					<td>".$pending_qty." ".$row['unit_name']."</td>
					
					<td><input type='number' min='0' max='' data-pendingqty='".$pending_qty1."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." /></td>
					<td>".$row['unit_name']."
						<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
					</td>
					<td>
						<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$cnt' required >";
						$str.= get_all_godown($dbcon,$rol['grn_godown'],1);
						$str.="</select>
						<input type='hidden' name='qc_type[]' id='qc_type$cnt' value='".$qc_st."' />
						<input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='".$rol['grn_trn_id']."' />
						<input type='hidden' name='qc_status[]' id='qc_status$cnt' value='".$rol['product_qc']."' />
						<input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='".$row['ref_id']."' />
						<input type='hidden' name='purchaseordertrn_id[]' id='purchaseordertrn_id$cnt' value='".$row['trn_id']."' />
						
					</td>
				</tr>";
		
		$cnt++;
	}
	
	return $str;
}
function grn_po_sub_trn($dbcon,$grn_trn_id,$purchaseordertrn_id){
	/* Code By Umair: 30/10/2020 
	   Comment: I have commented the below query and change the query to mange the tollerance.	
	   
	*/
	$query1="select po.*, `pm`.`tolerance`, `pm`.`maximum_tolerance`, `pm`.`minimum_tolerance` 
	from tbl_grn_trn as po 
	left join product_mst as pm ON `po`.`product_id` = `pm`.`product_id` 
	where `po`.`grn_trn_status`=0 and `po`.`grn_trn_id` =".$grn_trn_id;
	$rs_product1=$dbcon->query($query1);
	$row1=mysqli_fetch_array($rs_product1);
	$qty=$row1['product_qty'];
	$min_toll=$row1['minimum_tolerance'];
	$max_toll=$row1['maximum_tolerance'];

	$query="select * from tbl_purchaseordertrn as po where purchaseordertrn_status=0 and purchaseordertrn_id in (".$purchaseordertrn_id.")";
	$rs_product=$dbcon->query($query);
	while($row=mysqli_fetch_array($rs_product)){
			$query2="select sum(product_qty) as used_qty from tbl_grn_sub_trn as po where status=0 and purchaseordertrn_id =".$row['purchaseordertrn_id'];
			$rs_product2=$dbcon->query($query2);
			$row2=mysqli_fetch_array($rs_product2);
			$poqty=$row['product_qty']-$row2['used_qty'];

			// Calculate tollearance minimum and maximum
			$min_toll_qty = ($row['product_qty']*$min_toll)/100;
			$minium_tollerance_qty = $row['product_qty']-$min_toll_qty;

			$max_toll_qty = ($row['product_qty']*$max_toll)/100;
			$maximum_tollerance_qty = $row['product_qty']+$max_toll_qty;
			if($row2['used_qty']==''){
				$usedQty = $qty;
			}else{
				$usedQty = $row2['used_qty']+$qty;
			}
			


		if($qty!=0){
			if($qty!=""){
				if($qty>=$poqty || $usedQty>=$minium_tollerance_qty){
					$info2['product_id']			= $row['product_id'];
					$info2['grn_trn_id']			= $grn_trn_id;
					$info2['purchaseordertrn_id']	= $row['purchaseordertrn_id'];
					$info2['product_qty']			= $poqty;
					$info2['cdate']					= date("Y-m-d H:i:s");
					$info2['user_id']				= $_SESSION['user_id'];
					$info2['company_id']			= $_SESSION['company_id'];
					$tbl_grn_trn_id=add_record('tbl_grn_sub_trn', $info2, $dbcon);
					
					$info['used_status']		= 1; 
					$updateid=update_record('tbl_purchaseordertrn', $info,"purchaseordertrn_id=".$row['purchaseordertrn_id'] , $dbcon);
					
					$query3="select count(purchaseordertrn_id) as cou from tbl_purchaseordertrn as po where status=0 and used_status=0 and purchaseorder_id =".$row['purchaseorder_id'];
						$rs_product3=$dbcon->query($query3);
						$row3=mysqli_fetch_array($rs_product3);
						if($row3['cou']<=0){
							$info4['used_status']		= 1; 
							$updateid=update_record('tbl_purchaseorder', $info4,"purchaseorder_id=".$row['purchaseorder_id'], $dbcon);
						}
					$qty=$qty-$poqty;
					
				}else{
					$info2['product_id']			= $row['product_id'];
					$info2['grn_trn_id']			= $grn_trn_id;
					$info2['purchaseordertrn_id']	= $row['purchaseordertrn_id'];
					$info2['product_qty']			= $qty;
					$info2['cdate']					= date("Y-m-d H:i:s");
					$info2['user_id']				= $_SESSION['user_id'];
					$info2['company_id']			= $_SESSION['company_id'];
					$tbl_grn_trn_id=add_record('tbl_grn_sub_trn', $info2, $dbcon);
					
					$qty=$qty-$qty;
				}
			}
		}
		
	}
	return $query;
	
}
function get_vender_id($dbcon,$id,$grn_type)
{
	if($grn_type==2)
	{
		$q=$dbcon->query("select vender_id from tbl_purchaseorder where purchaseorder_id='$id'");
		$row=mysqli_fetch_array($q);
		$return=$row['vender_id'];
	}
	else
	{
		$q=$dbcon->query("select j_vendor from tbl_jobwork where jobwork_id='$id'");
		$row=mysqli_fetch_array($q);
		$return=$row['j_vendor'];
	}
	
	return $return;
}

function get_vender_name($dbcon,$id)
{
	
	$q=$dbcon->query("select l_name from tbl_ledger where l_id='$id'");
	$row=mysqli_fetch_array($q);
	$return=$row['l_name'];
	
	return $return;
}

function get_request_id_jobwork($dbcon,$id)
{
	$q=$dbcon->query("select j_ref_id from tbl_jobwork where jobwork_id='$id'");
	$row=mysqli_fetch_array($q);
	$return=$row['j_ref_id'];
	
	return $return;
}

function get_jobwork_details_for_grn_trn($dbcon,$id,$type,$mode,$eid,$vender_id)
{
	$str='';
	if(!empty($vender_id)){
		$ven=" and op.vender_id=".$vender_id;
	}
	if(!empty($id)){
		$po=" and j.jobwork_id=".$id;
	}
	$query="select j.*,p.product_name,p.product_type,sum(jt.product_qty) as tqty,unit.unit_name,unit.unitid from tbl_jobwork as j 
	left join product_mst as p on p.product_id=j.j_product_id 
	left join tbl_grn_trn as jt on jt.purchaseorder_id=j.jobwork_id 
	LEFT join unit_mst as unit on unit.unitid=j.process_unit
	where j.jobwork_id='$id' order by j.jobwork_id ";
	$rs_product=$dbcon->query($query);
	$cnt=1;
	while($row=mysqli_fetch_array($rs_product))
	{
		if(!empty($eid)){
			$grn_ids=" and grn_id!=".$eid;
		}
		$query_u="select sum(p.product_qty) as tqty from tbl_grn as j 
					left join tbl_grn_trn as p on p.grn_id=j.grn_id 
					where j.purchaseorder_id=".$row['jobwork_id']." ".$grn_ids." and grn_status=0 and ref_type=1 and grn_trn_status=0 ";
			$rs_product_u=$dbcon->query($query_u);
			$row_u=mysqli_fetch_array($rs_product_u);
			
			$query_ww="select group_concat(p.p_ref_id) as prf_id from tbl_jobwork_process as j 
					left join tbl_allocate_process as p on p.p_id=j.p_id 
					where j.jobwork_id=".$row['jobwork_id']." and status=0 ";
			$rs_product_u1=$dbcon->query($query_ww);
			$row_u1=mysqli_fetch_array($rs_product_u1);
			
			
		if(!empty($eid)){
			$query11="select * from tbl_grn_trn as mst
			where mst.grn_id=".$eid." and product_id=".$row['j_product_id']." and purchaseorder_id=".$row['jobwork_id'];
			$rol=mysqli_fetch_assoc($dbcon->query($query11));
			
			if($rol['product_qc']==1){
				$ronly="readonly";
			}else{
				$ronly="";
			}
		}
			
		$pending_qty=$row['j_qty']-$row_u['tqty'];
		
		$pr_setting=get_pro_field($dbcon,$row['j_product_id'],'product_setting_check');
		
		$pr_setting_arr=explode(",",$pr_setting);
		
		if(in_array("product_qc",$pr_setting_arr))
		{
			$qc_st="yes";
			$sty="display:none;";
		}else{
			$qc_st="no";
			$sty="";
		}
		
		$str="<tr>
			<td>".$cnt."</td>
			<td>".get_product_type_name($dbcon,$row['product_type'])."</td>
			<td>".$row['product_name']."<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$id' value='".$row['j_product_id']."' /></td>
			<td>".$row['j_qty']."</td>
			<td>".$pending_qty."</td>
			<td><input type='text' class='form-control' max='".$pending_qty."' name='grn_qty[]' id='grn_qty$id' value='".$rol['product_qty']."' ".$ronly." /></td>
			<td>".$row['unit_name']."
				<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["process_unit"]."' />
			</td>
			<td>
				<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$cnt' required >";
				$str.= get_all_godown($dbcon,$rol['grn_godown'],1);
				$str.="</select>
				<input type='hidden' name='qc_type[]' id='qc_type$cnt' value='".$qc_st."' />
				<input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='".$rol['grn_trn_id']."' />
				<input type='hidden' name='qc_status[]' id='qc_status$cnt' value='".$rol['product_qc']."' />
				<!--<input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='".$row['j_ref_id']."' />-->
				<input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='".$row_u1['prf_id']."' />
			</td>
			
		</tr>";
		
		$cnt++;
	}
	
	return $str;
	//return $query;
}
function get_jobwork_details_for_grn_trn_20_08_2020($dbcon,$id,$type,$mode,$eid)
{
	$str='';
	$query="select j.*,p.product_name,p.product_type,sum(jt.product_qty) as tqty,unit.unit_name,unit.unitid from tbl_jobwork as j 
	left join product_mst as p on p.product_id=j.j_product_id 
	left join tbl_grn_trn as jt on jt.purchaseorder_id=j.jobwork_id 
	LEFT join unit_mst as unit on unit.unitid=j.process_unit
	where j.jobwork_id='$id' order by j.jobwork_id ";
	$rs_product=$dbcon->query($query);
	$cnt=1;
	while($row=mysqli_fetch_array($rs_product))
	{
		if(!empty($eid)){
			$grn_ids=" and grn_id!=".$eid;
		}
		$query_u="select sum(p.product_qty) as tqty from tbl_grn as j 
					left join tbl_grn_trn as p on p.grn_id=j.grn_id 
					where j.purchaseorder_id=".$row['jobwork_id']." ".$grn_ids." and grn_status=0 and ref_type=1 and grn_trn_status=0 ";
			$rs_product_u=$dbcon->query($query_u);
			$row_u=mysqli_fetch_array($rs_product_u);
			
		if(!empty($eid)){
			$query11="select * from tbl_grn_trn as mst
			where mst.grn_id=".$eid." and product_id=".$row['j_product_id']." and purchaseorder_id=".$row['jobwork_id'];
			$rol=mysqli_fetch_assoc($dbcon->query($query11));
			
			if($rol['product_qc']==1){
				$ronly="readonly";
			}else{
				$ronly="";
			}
		}
			
		$pending_qty=$row['j_qty']-$row_u['tqty'];
		
		$pr_setting=get_pro_field($dbcon,$row['j_product_id'],'product_setting_check');
		
		$pr_setting_arr=explode(",",$pr_setting);
		
		if(in_array("product_qc",$pr_setting_arr))
		{
			$qc_st="yes";
			$sty="display:none;";
		}else{
			$qc_st="no";
			$sty="";
		}
		
		$str="<tr>
			<td>".$cnt."</td>
			<td>".get_product_type_name($dbcon,$row['product_type'])."</td>
			<td>".$row['product_name']."<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$id' value='".$row['j_product_id']."' /></td>
			<td>".$row['j_qty']."</td>
			<td>".$pending_qty."</td>
			<td><input type='text' class='form-control' max='".$pending_qty."' name='grn_qty[]' id='grn_qty$id' value='".$rol['product_qty']."' ".$ronly." /></td>
			<td>".$row['unit_name']."
				<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["process_unit"]."' />
			</td>
			<td>
				<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$cnt' required >";
				$str.= get_all_godown($dbcon,$rol['grn_godown'],1);
				$str.="</select>
				<input type='hidden' name='qc_type[]' id='qc_type$cnt' value='".$qc_st."' />
				<input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='".$rol['grn_trn_id']."' />
				<input type='hidden' name='qc_status[]' id='qc_status$cnt' value='".$rol['product_qc']."' />
				<input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='".$row['j_ref_id']."' />
			</td>
			
		</tr>";
		
		$cnt++;
	}
	
	return $str;
	//return $query;
}

function get_branch_from_zone($dbcon,$zone,$id,$sindex) {
	$str='';
	$query="select * from branch_mst where branch_status='0' and zoneid='$zone' order by branch_name ";
	$rs_product=$dbcon->query($query);
	
	$str.= '<option value="">Choose Branch</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['branch_id_customer']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['branch_id'].'">'.$rel['branch_name'].'</option>';
	}
	return $str;
}

function build_category_tree($dbcon,$product,$parent,$indent=""){
  $r = $dbcon->query("SELECT * FROM tbl_bomtrn WHERE parent_id = ".$parent." and sale_product_id='$product'");
  $output = '';
  while($c = mysqli_fetch_array($r)){
	  
    $output .= "<option value=\"" . $c["product_id"] . "\" " . $selected . ">" .get_pro_field($dbcon,$c["product_id"],"product_name") . "</option>";
    build_category_tree($dbcon,$product,$c["bom_trn_id"],$indent . "&nbsp;&nbsp;");
    
  }
  echo $output;
}

function getEmployeeIdComplain($dbcon,$id)
{
	$query="select emp_id from tbl_complaint where complaint_id=$id";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	return $rel['emp_id'];
}

function get_last_remark($dbcon,$ex_id)
{
	$query="select eh_remark from tbl_expense_status_history where eh_ex_id='$ex_id' order by eh_id desc";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	return $rel['eh_remark'];
}

function get_picode($dbcon)
{
	
}

function get_product_process($dbcon,$id,$product_id)
{
	$query="select * from tbl_wororder_product_process where rp_id='$id' and process_priority='1' and product_id=".$product_id;
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	//$row=array();
	$row['process_id']=$rel['process_id'];
	$row['process_type']=$rel['process_type'];
	$row['process_priority']=$rel['process_priority'];
	
	return json_encode($row);
}

function get_current_process($dbcon,$job_id,$product_id)
{
	$query="select j_pr_process_id from tbl_jobwork where j_product_id='$product_id' and jobwork_id='$job_id'";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	//$row=array();
	
	return $rel['j_pr_process_id'];
}
function get_current_process_allocate($dbcon,$job_id,$product_id)
{
	$query="select j_alloc_process_id from tbl_jobwork where j_product_id='$product_id' and jobwork_id='$job_id'";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	//$row=array();
	
	return $rel['j_alloc_process_id'];
}

function get_current_process_type($dbcon,$job_id,$product_id)
{
	$query="select j_process_type from tbl_jobwork where j_product_id='$product_id' and jobwork_id='$job_id'";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	//$row=array();
	
	return $rel['j_process_type'];
}


function count_process_qty($dbcon,$id,$type)
{
	$query="select (select COALESCE(sum(pen_qty),0) as sqty1 from tbl_allocate_process where  process_id='$id' and pr_process_type='$type')  as sqty,( select COALESCE(sum(pt_qty),0) as stqty1 from tbl_allocate_process_trn apt left join tbl_allocate_process as ap on ap.p_id=apt.pt_alloc_id where pt_process_id='$id' and ap.pr_process_type='$type') as stqty";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	
	//$total=$rel['sqty']-$rel['stqty'];
	$total=$rel['sqty'];
	
	if($total==0)
	{
		return 0;
	}
	else
	{
		return $total;
	}
	//return $id;
}


function count_working_process_qty($dbcon,$id,$type)
{
	$is_available=count_process_qty($dbcon,$id,$type);
	$p_qty=0;
	if($is_available>0)
	{
		
		$q=$dbcon->query("select ap.*,p.product_type,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 
		left join product_mst as p on p.product_id=ap.p_product_id 
		left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
			left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id
		where ap.p_status IN (0,1) and process_id=".$id." and pr_process_type='$type'");
		
		//$dddd="select ap.*,p.product_type from tbl_allocate_process as ap left join product_mst as p on p.product_id=ap.p_product_id where ap.p_status IN (0,1) and pr_process_type='$type'";
		while($rel=mysqli_fetch_array($q))
		{
			$pid=$rel['p_product_id'];
			
			$where='';
			//$pp=$rel['product_type'];
			if($rel['product_type']==0)
			{
				$where.=" and parent_id = '0' and sale_product_id='$pid'";
			}
			else
			{
				$where.=" and parent_id = (select bom_trn_id from tbl_bomtrn where product_id='$pid' order by bom_trn_id desc limit 0,1)";
			}
		if($rel['p_status']==1){
			//$unused=$rel['p_qty']-$rel['start_qty'];
			
			//$min_machine=$rel['pen_qty']-$unused;
			//$pending_qty=$rel['pen_qty']-$unused;
			//$min_machine=$rel['strtt_qty']-$rel['end_qty'];
			//$pending_qty=$rel['strtt_qty']-$rel['end_qty'];
			
				$min_machine=$rel['start_qty'];
				$min_machine111=$rel['strtt_qty']-$rel['end_qty'];
				$pending_qty=$rel['pen_qty'];
				if($min_machine111>$pending_qty){
					$min_machine111=$pending_qty;
				}
			
			
		}else if($rel['previous_process_id']==0){
			$cur_stock=0;
			$machine_make=array();
					$q12=$dbcon->query("select * from tbl_request_product as ap 
						where status=0 and perent_id=".$rel['p_ref_id'] );
					while($rel_n1=mysqli_fetch_array($q12)){
						$o_qty=convert_stock($dbcon,$rel_n1['req_qty_one'],$rel_n1['rp_id'],"base_unit");
						$required_qty=$rel['p_qty']*$o_qty;
						//var_dump($required_qty);
						//$cur_stock=reserve_stock($dbcon,$row['product_id'],$row['product_base_unit']);
						$cur_stock=reserve_stock($dbcon,$rel_n1['rp_pid'],$rel_n1['purchase_unit'],"",$rel_n1['rp_id']);
						//var_dump($cur_stock);
						$total=$cur_stock;
						if($total<0){
							$total=0;
						}
						if($total>$required_qty)
						{
							$usable=$required_qty;
						}
						else
						{
							//var_dump($total."===".$o_qty);	//$usable=round(($total/$rel_n1['req_qty_one']),0,PHP_ROUND_HALF_DOWN);
							//$usable=round(($total/$o_qty),0,PHP_ROUND_HALF_DOWN);
							$usable=$total/$o_qty;
							//var_dump($total/$rel_n1['req_qty_one']);
							//$usable=$usable*$rel_n1['req_qty_one'];
							$usable=$usable*$o_qty;
							
						}
						$chkp=$usable/$o_qty;
						
						$machine_make[]=number_format($chkp,4,".","");
						$min_machine=min($machine_make);
						//var_dump($min_machine);
						$min_machine111=$min_machine;
						//var_dump($min_machine111);
						$pending_qty=$rel['pen_qty'];
						
						if($min_machine111>$pending_qty){
							$min_machine111=$pending_qty;
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
			while($row=mysqli_fetch_array($q2))
			{
				//$required_qty=$rel['p_qty']*$row['product_act_qty'];
				$required_qty=$rel['p_qty']*$row['bom_qty'];
				
				$ri1="select rp_id from tbl_request_product as ap
				where ap.perent_id=".$rel['p_ref_id']." and rp_pid=".$row['product_id'];
					$ri11=$dbcon->query($ri1);
					$r221=mysqli_fetch_array($ri11);
				
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
 */		}else{
			/* $q22="select * from tbl_allocate_process as bt 
						where bt.p_id=".$rel['previous_process_id'];
				$q23=$dbcon->query($q22);
				$row12=mysqli_fetch_array($q23);
				
				$min_machine=$row12['process_stock']-$row12['process_used_stock'];
				$pending_qty=$min_machine; */
				
			$q22="select * from tbl_allocate_process as bt 
						where bt.p_id=".$rel['previous_process_id'];
				$q23=$dbcon->query($q22);
				$row12=mysqli_fetch_array($q23);
				
				$min_machine=$row12['process_stock']-$row12['process_used_stock'];
				$min_machine111=$min_machine;
				//$pending_qty11=$min_machine;
				$pending_qty=$rel['pen_qty'];
				if($min_machine111>$pending_qty){
					$min_machine111=$pending_qty;
				}
				
				
		}
			//$sho=$sho."n".$min_machine."-".$pp;
			//$sho=$sho."nnnnn".$q1;
			$p_qty+=$min_machine111;
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
function count_working_process_qty_24_08_20($dbcon,$id,$type)
{
	$is_available=count_process_qty($dbcon,$id,$type);
	$p_qty=0;
	if($is_available>0)
	{
		
		$q=$dbcon->query("select ap.*,p.product_type,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 
		left join product_mst as p on p.product_id=ap.p_product_id 
		left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
			left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id
		where ap.p_status IN (0,1) and process_id=".$id." and pr_process_type='$type'");
		
		//$dddd="select ap.*,p.product_type from tbl_allocate_process as ap left join product_mst as p on p.product_id=ap.p_product_id where ap.p_status IN (0,1) and pr_process_type='$type'";
		while($rel=mysqli_fetch_array($q))
		{
			$pid=$rel['p_product_id'];
			
			$where='';
			//$pp=$rel['product_type'];
			if($rel['product_type']==0)
			{
				$where.=" and parent_id = '0' and sale_product_id='$pid'";
			}
			else
			{
				$where.=" and parent_id = (select bom_trn_id from tbl_bomtrn where product_id='$pid' order by bom_trn_id desc limit 0,1)";
			}
		if($rel['p_status']==1){
			//$unused=$rel['p_qty']-$rel['start_qty'];
			
			//$min_machine=$rel['pen_qty']-$unused;
			//$pending_qty=$rel['pen_qty']-$unused;
			//$min_machine=$rel['strtt_qty']-$rel['end_qty'];
			//$pending_qty=$rel['strtt_qty']-$rel['end_qty'];
			
				$min_machine=$rel['start_qty'];
				$min_machine111=$rel['strtt_qty']-$rel['end_qty'];
				$pending_qty=$rel['pen_qty'];
				if($min_machine111>$pending_qty){
					$min_machine111=$pending_qty;
				}
			
			
		}else if($rel['previous_process_id']==0){
			$q1="select itm.product_id,itm.product_type,itm.product_purchase_rate,itm.product_name,itm.product_min_stock,itm.product_opening,bt.product_act_qty,u.unit_name,IFNULL(qcqty,0),bt.product_base_unit,(bt.product_act_qty/bt.product_base_qty) as bom_qty from tbl_bomtrn as bt 
			left join product_mst as itm on itm.product_id=bt.product_id 
			left join unit_mst as u on u.unitid=bt.product_base_unit 
			left join (select sum(qc.qc_accepted) as qcqty,qc.qc_product 
			from tbl_qc_trn as qc where qc_status=0 group by qc.qc_product) as qcd on qcd.qc_product=bt.product_id 
			where bt.bom_trn_status=0 ".$where."";
			$q2=$dbcon->query($q1);
			$machine_make=array();
			$aao="";
			while($row=mysqli_fetch_array($q2))
			{
				//$required_qty=$rel['p_qty']*$row['product_act_qty'];
				$required_qty=$rel['p_qty']*$row['bom_qty'];
				
				//$op_stock=$row['product_opening'];
				//$total=$op_stock+$row['qcqty'];
				
				$cur_stock=get_current_stock_new($dbcon,$row['product_id'],$row['product_base_unit']);
				
				
			/* $ri="select * from tbl_allocate_process as ap
			where ap.p_status IN (0,1) and pr_process_type='$type'";
			$ri1=$dbcon->query($ri);
			$r22=mysqli_fetch_array($ri1);
 */		
				
				//$cur_stock=reserve_stock($dbcon,$row['product_id'],$row['product_base_unit']);
				
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
		}else{
			/* $q22="select * from tbl_allocate_process as bt 
						where bt.p_id=".$rel['previous_process_id'];
				$q23=$dbcon->query($q22);
				$row12=mysqli_fetch_array($q23);
				
				$min_machine=$row12['process_stock']-$row12['process_used_stock'];
				$pending_qty=$min_machine; */
				
			$q22="select * from tbl_allocate_process as bt 
						where bt.p_id=".$rel['previous_process_id'];
				$q23=$dbcon->query($q22);
				$row12=mysqli_fetch_array($q23);
				
				$min_machine=$row12['process_stock']-$row12['process_used_stock'];
				$min_machine111=$min_machine;
				//$pending_qty11=$min_machine;
				$pending_qty=$rel['pen_qty'];
				if($min_machine111>$pending_qty){
					$min_machine111=$pending_qty;
				}
				
				
		}
			//$sho=$sho."n".$min_machine."-".$pp;
			//$sho=$sho."nnnnn".$q1;
			$p_qty+=$min_machine111;
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
function count_working_process_qty_old_13052020($dbcon,$id,$type)
{
	$is_available=count_process_qty($dbcon,$id,$type);
	$p_qty=0;
	if($is_available>0)
	{
		
		$q=$dbcon->query("select ap.*,p.product_type,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 
		left join product_mst as p on p.product_id=ap.p_product_id 
		left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
			left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id
		where ap.p_status IN (0,1) and process_id=".$id." and pr_process_type='$type'");
		
		//$dddd="select ap.*,p.product_type from tbl_allocate_process as ap left join product_mst as p on p.product_id=ap.p_product_id where ap.p_status IN (0,1) and pr_process_type='$type'";
		while($rel=mysqli_fetch_array($q))
		{
			$pid=$rel['p_product_id'];
			
			$where='';
			//$pp=$rel['product_type'];
			if($rel['product_type']==0)
			{
				$where.=" and parent_id = '0' and sale_product_id='$pid'";
			}
			else
			{
				$where.=" and parent_id = (select bom_trn_id from tbl_bomtrn where product_id='$pid' order by bom_trn_id desc limit 0,1)";
			}
		if($rel['p_status']==1){
			//$unused=$rel['p_qty']-$rel['start_qty'];
			
			//$min_machine=$rel['pen_qty']-$unused;
			//$pending_qty=$rel['pen_qty']-$unused;
			$min_machine=$rel['strtt_qty']-$rel['end_qty'];
			$pending_qty=$rel['strtt_qty']-$rel['end_qty'];
		}else if($rel['previous_process_id']==0){
			$q1="select itm.product_id,itm.product_type,itm.product_purchase_rate,itm.product_name,itm.product_min_stock,itm.product_opening,bt.product_act_qty,u.unit_name,IFNULL(qcqty,0),bt.product_base_unit from tbl_bomtrn as bt 
			left join product_mst as itm on itm.product_id=bt.product_id 
			left join unit_mst as u on u.unitid=bt.product_base_unit 
			left join (select sum(qc.qc_accepted) as qcqty,qc.qc_product 
			from tbl_qc_trn as qc where qc_status=0 group by qc.qc_product) as qcd on qcd.qc_product=bt.product_id 
			where bt.bom_trn_status=0 ".$where."";
			$q2=$dbcon->query($q1);
			$machine_make=array();
			$aao="";
			while($row=mysqli_fetch_array($q2))
			{
				$required_qty=$rel['p_qty']*$row['product_act_qty'];
				
				//$op_stock=$row['product_opening'];
				//$total=$op_stock+$row['qcqty'];
				
				$cur_stock=get_current_stock_new($dbcon,$row['product_id'],$row['product_base_unit']);
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
					$usable=round(($total/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
					$usable=$usable*$row['product_act_qty'];
					//$usable22=$usable22+$usable;
				}
				
				$machine_make[]=round(($usable/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
			}
			
			$min_machine=min($machine_make);
		}else{
			$q22="select * from tbl_allocate_process as bt 
						where bt.p_id=".$rel['previous_process_id'];
				$q23=$dbcon->query($q22);
				$row12=mysqli_fetch_array($q23);
				
				$min_machine=$row12['process_stock']-$row12['process_used_stock'];
				$pending_qty=$min_machine;
		}
			//$sho=$sho."n".$min_machine."-".$pp;
			//$sho=$sho."nnnnn".$q1;
			$p_qty+=$min_machine;
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

function count_re_process_qty($dbcon,$id,$type)
{
	$query="select (select COALESCE(sum(pen_qty),0) as sqty1 from tbl_allocate_re_process where  process_id='$id' and pr_process_type='$type')  as sqty,( select COALESCE(sum(pt_qty),0) as stqty1 from tbl_allocate_re_process_trn apt left join tbl_allocate_re_process as ap on ap.p_id=apt.pt_alloc_id where pt_process_id='$id' and ap.pr_process_type='$type') as stqty";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	
	//$total=$rel['sqty']-$rel['stqty'];
	$total=$rel['sqty'];
	
	if($total==0)
	{
		return 0;
	}
	else
	{
		return $total;
	}
	//return $id;
}

function count_opening_process_qty($dbcon,$id,$type)
{
	//select sum(process_opening) as opening from tbl_product_process where process_id='$id' and process_type='$type'
	
	$query="select (select COALESCE(sum(process_opening),0) as sqty1 from tbl_product_process where status = 0 AND  process_id='$id' and process_type='$type')  as sqty,(select COALESCE(sum(pt_qty),0) as stqty1 from tbl_allocate_process_trn apt left join tbl_allocate_process as ap on ap.p_id=apt.pt_alloc_id where pt_process_id='$id' and ap.pr_process_type='$type') as stqty";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	
	$total=$rel['sqty']-$rel['stqty'];
	
	if($total==0)
	{
		return 0;
	}
	else if($total<0){
		return 0;
	}
	else
	{
		return $total;
	}
	//return $query;
}

function get_process_name($dbcon,$id)
{
	$query="select process_name from process_mst where  process_id='$id'";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	return $rel['process_name'];
}

function get_next_process($dbcon,$process_id,$product_id,$rp_id)
{
	/* $query="select * from tbl_product_process where status = 0 AND  product_id='$product_id' and process_id='$process_id'";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	$cur_priority=$rel['process_priority'];
	$next_priority=$cur_priority+1;
	
	$query1="select * from tbl_product_process where status = 0 AND  product_id='$product_id' and process_priority='$next_priority'";
	$rs_cust1=$dbcon->query($query1);
	$rel1=mysqli_fetch_array($rs_cust1);
	$count=mysqli_num_rows($rs_cust1);
	 */
	$query="select * from tbl_wororder_product_process where product_id='$product_id' and process_id='$process_id' and rp_id=".$rp_id;
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	$cur_priority=$rel['process_priority'];
	$next_priority=$cur_priority+1;
	
	$query1="select * from tbl_wororder_product_process where  product_id='$product_id' and process_priority='$next_priority' and rp_id=".$rp_id;
	$rs_cust1=$dbcon->query($query1);
	$rel1=mysqli_fetch_array($rs_cust1);
	$count=mysqli_num_rows($rs_cust1);
	
//	echo $rel1['process_id'];
	if($count>0)
	{
		$row['process_id']=$rel1['process_id'];
		$row['process_type']=$rel1['process_type'];
		$row['process_priority']=$next_priority;
	
		return json_encode($row);
	
	}
	else
	{
		$row['process_id']=0;
		$row['process_type']=0;
		$row['process_priority']=0;
		return json_encode($row);
	}
}

function get_product_specification($dbcon,$id) {
	$str='';
	$query="select * from mst_material_spec where ms_status='0' order by ms_name";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Material Specification</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['ms_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['ms_id'].'">'.$rel['ms_name'].'</option>';
	}
	return $str;
}

function get_alloc_id($dbcon,$ref_id,$process_id)
{
	$query="select p_id from tbl_allocate_process where p_ref_id='$ref_id' and process_id='$process_id'";
	$rel=$dbcon->query($query);
	$row=mysqli_fetch_array($rel);
	
	return $row['p_id'];
}

function get_jobwork_qc_qty($dbcon,$id)
{
	$query="select j.*,dqty from tbl_jobwork as j left join (select sum(qc_product_qty) as dqty,po_id from tbl_qc_trn group by po_id) as apta on apta.po_id=j.jobwork_id where j.jobwork_id='$id'";
	$rel=$dbcon->query($query);
	$row=mysqli_fetch_array($rel);
	
	$qty=$row['j_qty']-$row['dqty'];
	
	return $qty;
}

function count_min_max($dbcon,$type)
{
	//+IFNULL(qc_total_rejected,0)
	$query="SELECT pro.product_id, pro.product_name,pro.product_status,pro.product_min_stock, pro.product_opening, reqqty,pro.product_setting_check,grn_total,qc_total,inv_qty,jobout_qty,process_de,qc_total_rejected,((IFNULL(grn_total,0)+IFNULL(qc_total,0)+IFNULL(add_adjustment_qty,0)+pro.product_opening)-(IFNULL(inv_qty,0)+IFNULL(jobout_qty,0)+IFNULL(remove_adjustment_qty,0)))+IFNULL(reqqty,0) as stock_in,(IFNULL(((IFNULL((select IFNULL(sum(qc.base_stock),0) as base_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=".$_SESSION['company_id']." 
				group by qc.product_id),0)+IFNULL((select IFNULL(sum(qc.convert_stock),0) as con_stock_add from tbl_stock_trn as qc 
				where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=".$_SESSION['company_id']." 
				group by qc.product_id),0))-(IFNULL((select IFNULL(sum(qc.base_stock),0) as base_stock_minus from tbl_stock_trn as qc 
				where qc.stock_status!=2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=".$_SESSION['company_id']." 
				group by qc.product_id),0)+IFNULL((select IFNULL(sum(qc.convert_stock),0) as con_stock_minus from tbl_stock_trn as qc 
				where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=".$_SESSION['company_id']." 
				group by qc.product_id),0))),0)+IFNULL(reqqty,0)) as stock_in_new from	product_mst as pro	
	
	left join (select sum(gt.product_qty) as grn_total,gt.product_id,g.grn_status,g.product_qc,p.product_setting_check from tbl_grn_trn as gt left join tbl_grn as g on g.grn_id=gt.grn_id left join product_mst as p on p.product_id=gt.product_id where g.grn_status=0 and !FIND_IN_SET('product_qc',p.product_setting_check) and gt.company_id=".$_SESSION['company_id']." group by gt.product_id) as grn on grn.product_id=pro.product_id
		
	left join (select sum(qc.qc_accepted) as qc_total,qc.qc_product,q.qc_status from tbl_qc_trn as qc left join tbl_qc as q on q.qc_id=qc.qc_id where q.qc_status=0 and q.company_id=".$_SESSION['company_id']." group by qc.qc_product) as qc on qc.qc_product=pro.product_id
		
	left join (select sum(intrn.product_qty) as inv_qty,intrn.product_id from tbl_invoicetrn as intrn where intrn.trancation_status=0 and intrn.company_id=".$_SESSION['company_id']." group by intrn.product_id) as invt on invt.product_id=pro.product_id
		
	left join (select sum(jobout.outward_product_qty) as jobout_qty,jobout.raw_product_id from tbl_jobworktrn as jobout where jobout.jobworktrn_status=0 and jobwork_id!=0 and jobout.company_id=".$_SESSION['company_id']."  group by jobout.raw_product_id) as jout on jout.raw_product_id=pro.product_id
		
	left join (select GROUP_CONCAT(prm.process_name) as process_de,pp.product_id from tbl_product_process as pp left join process_mst as prm on prm.process_id=pp.process_id WHERE pp.status = 0 group by pp.product_id ) as pr on pr.product_id=pro.product_id
		
	left join (select sum(qc.qc_rejected) as qc_total_rejected,qc.qc_product,q.qc_status from tbl_qc_trn as qc left join tbl_qc as q on q.qc_id=qc.qc_id where q.qc_status!=2 and q.company_id=".$_SESSION['company_id']." group by qc.qc_product) as qc1 on qc1.qc_product=pro.product_id
		
	left join (select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0 group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id
	
	left join (select sum(jobout.add_adjustment_qty) as add_adjustment_qty,jobout.product_id from tbl_stcok_adjustment_trn as jobout 
		left join tbl_stcok_adjustment as saj on saj.stcok_adjustment_id=jobout.stcok_adjustment_id
	where jobout.stcok_adjustment_trn_status=0 and saj.company_id=".$_SESSION['company_id']."  group by jobout.product_id) as aaje on aaje.product_id=pro.product_id
	
	left join (select sum(jobout.remove_adjustment_qty) as remove_adjustment_qty,jobout.product_id from tbl_stcok_adjustment_trn as jobout 
		left join tbl_stcok_adjustment as saj on saj.stcok_adjustment_id=jobout.stcok_adjustment_id
	where jobout.stcok_adjustment_trn_status=0 and saj.company_id=".$_SESSION['company_id']."  group by jobout.product_id) as aaje1 on aaje1.product_id=pro.product_id
	
	where pro.product_status=0 and pro.product_min_stock!=0 group by pro.product_id HAVING stock_in_new < pro.product_min_stock order by product_name";
	
	/* 
	$query="SELECT pro.product_id,pro.product_name,pro.product_min_stock,pro.product_opening,reqqty from product_mst as pro 
			
	left join (select sum(req.rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0  group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id
	
	where pro.product_status=0 and  pro.product_min_stock >= IFNULL(reqqty,0)  and pro.product_min_stock!=0  group by pro.product_id order by product_name"; */
	
	$rs=$dbcon->query($query);
	
	$count=mysqli_num_rows($rs);
	
	return $count;
	//return $query;
	
}
function count_reject_procuct_req($dbcon)
{
	$query="select count(qctrn_id) as qty from tbl_qc_trn where qc_rejected!=0 and qc_rejected_used<qc_rejected and qc_status=0";
	
	$rs=$dbcon->query($query);
	
	$row=mysqli_fetch_array($rs);
	
	return $row['qty'];
}

function get_other_po_qty($dbcon,$product_id,$po_id)
{
	$query="select COALESCE(sum(product_qty),0) as qty from  tbl_purchasetrntemp where product_id='$product_id' and purchaseorder_id!='$po_id' and po_trn_req_status='0'";
	
	$rs=$dbcon->query($query);
	
	$row=mysqli_fetch_array($rs);
	
	return $row['qty'];
}

function members_Tree($dbcon,$parentKey)
{
 
  $sql = 'SELECT g_id, g_name from tbl_group WHERE g_pid="'.$parentKey.'" order by g_name';

  $result = $dbcon->query($sql);

  while($value = mysqli_fetch_assoc($result)){
	 $id = $value['g_id'];
	 $row1[$id]['id'] = $value['g_id'];
	 $row1[$id]['name'] = $value['g_name'];
	 $row1[$id]['text'] = $value['g_name'];
	 $row1[$id]['nodes'] = array_values(members_Tree($dbcon,$value['g_id']));
  }

  return $row1;
}


function getenvelope($dbcon,$id)
{
	$query="select * from evelope_design where env_status=0 and company_id=".$_SESSION['company_id'];
	$rs_cust=$dbcon->query($query);	
	echo '<option value="">Select Envelope</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($rel['envelope_design_id']==$id)
		{
			$sel="selected='selected'";
		}
		echo '<option '.$sel.' value="'.$rel['envelope_design_id'].'">'.$rel['env_name'].'</option>';
	}

}

function get_warehouse_qty($dbcon,$pro_id,$req_qty,$eid)
{
	
	$cnt=1;$str='';
	$selb=$dbcon->query("select gd.*,gps.product_stock,gps.priority from mst_godown as gd left join tbl_branch_product_stock as gps on gd.gd_id=gps.branch_id where gd.g_status=0 and gps.product_id='$pro_id'");
	
	while($rb=mysqli_fetch_array($selb))
	{
		if($req_qty>=$rb['product_stock'])
		{
			$deducted=$rb['product_stock'];
			$req_qty=$req_qty-$rb['product_stock'];
		}
		else
		{
			$deducted=$req_qty;
			$req_qty=0;
		}
		
		$str.='
		'.$rb['gd_name'].':
		'.$deducted.'<input type="hidden" name="deducted_stock[]" id="" value="'.$deducted.'"  />
		<input type="hidden" name="deducted_gd_id[]" id="" value="'.$rb['gd_id'].'"  />
		<input type="hidden" name="product_id[]" id="" value="'.$pro_id.'"  />
		<input type="hidden" name="gst_eid[]" id="" value="'.$eid.'"  />
		<br>';
		
		$cnt++;
	}
	

	return $str;
}


function get_all_godown($dbcon,$eid,$blnk){
	$query="select gd_id,gd_name from mst_godown where g_status=0";
	$rs_dispatch=$dbcon->query($query);	
	if($blnk!="1"){
	$str='<option value="">Choose Godown</option>';
	}
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel='';
		if($rel['gd_id']==$eid){
			$sel='selected="selected"';
		}
		$str.='<option '.$sel.' value="'.$rel['gd_id'].'">'.$rel['gd_name'].'</option>';
	}
	return $str;
}


function get_current_stock($dbcon,$pro_id)
{
	
	$query='SELECT pro.product_id,pro.product_opening,pro.product_setting_check,grn_total,qc_total,inv_qty,sup_qty,jobout_qty,qc_total_rejected,remove_adjustment_qty,add_adjustment_qty  FROM `product_mst` as pro 
	 
	left join (select sum(gt.product_qty) as grn_total,gt.product_id,g.grn_status,g.product_qc,p.product_setting_check from tbl_grn_trn as gt left join tbl_grn as g on g.grn_id=gt.grn_id left join product_mst as p on p.product_id=gt.product_id where g.grn_status=0 and !FIND_IN_SET("product_qc",p.product_setting_check) and gt.company_id='.$_SESSION['company_id'].' group by gt.product_id) as grn on grn.product_id=pro.product_id
	
	left join (select sum(qc.qc_accepted) as qc_total,qc.qc_product,q.qc_status from tbl_qc_trn as qc left join tbl_qc as q on q.qc_id=qc.qc_id where q.qc_status!=2 and q.company_id='.$_SESSION['company_id'].' group by qc.qc_product) as qc on qc.qc_product=pro.product_id
	
	left join (select sum(qc.qc_rejected) as qc_total_rejected,qc.qc_product,q.qc_status from tbl_qc_trn as qc left join tbl_qc as q on q.qc_id=qc.qc_id where q.qc_status!=2 and q.company_id='.$_SESSION['company_id'].' group by qc.qc_product) as qc1 on qc1.qc_product=pro.product_id
	
	left join (select sum(intrn.product_qty) as inv_qty,intrn.product_id from tbl_invoicetrn as intrn where intrn.trancation_status=0 and intrn.company_id='.$_SESSION['company_id'].' group by intrn.product_id) as invt on invt.product_id=pro.product_id
	
	left join (select sum(suptrn.product_qty) as sup_qty,suptrn.product_id from tbl_bill_of_supplytrn as suptrn where suptrn.bill_of_supply_trn_status=0 and suptrn.company_id='.$_SESSION['company_id'].' group by suptrn.product_id) as supt on supt.product_id=pro.product_id
	
	left join (select sum(jobout.outward_product_qty) as jobout_qty,jobout.raw_product_id from tbl_jobworktrn as jobout where jobout.jobworktrn_status=0 and jobwork_id!=0 and jobout.company_id='.$_SESSION['company_id'].'  group by jobout.raw_product_id) as jout on jout.raw_product_id=pro.product_id
	
	left join (select sum(jobout.add_adjustment_qty) as add_adjustment_qty,jobout.product_id from tbl_stcok_adjustment_trn as jobout 
		left join tbl_stcok_adjustment as saj on saj.stcok_adjustment_id=jobout.stcok_adjustment_id
	where jobout.stcok_adjustment_trn_status=0 and saj.company_id='.$_SESSION['company_id'].'  group by jobout.product_id) as aaje on aaje.product_id=pro.product_id
	
	left join (select sum(jobout.remove_adjustment_qty) as remove_adjustment_qty,jobout.product_id from tbl_stcok_adjustment_trn as jobout 
		left join tbl_stcok_adjustment as saj on saj.stcok_adjustment_id=jobout.stcok_adjustment_id
	where jobout.stcok_adjustment_trn_status=0 and saj.company_id='.$_SESSION['company_id'].'  group by jobout.product_id) as aaje1 on aaje1.product_id=pro.product_id

	where pro.product_type in(0,1,2,3,4,5) and  pro.product_id='.$pro_id ;
	$rows=mysqli_fetch_assoc($dbcon->query($query));
	//+$rows['qc_total_rejected']
	$stock=($rows['product_opening']+$rows['grn_total']+$rows['qc_total']+$rows['add_adjustment_qty'])-($rows['inv_qty']+$rows['sup_qty']+$rows['jobout_qty']+$rows['remove_adjustment_qty']);
	//$stock=($rows['product_stock']+$rows['pur_qty']+$rows['jobin_qty']+$rel['strnt_qty'])-($rows['jobout_qty']+$rows['inv_qty']+$rel['strn_qty']+$rel['mwaste_qty']);
	
	return floatval($stock);
}


function get_process_stock_detail($dbcon,$pr_id)
{
	$q="select pp.*,pro.product_name,pr.process_name,qc_total,qc_total_rejected from tbl_product_process as pp 
	
	left join product_mst as pro on pro.product_id=pp.product_id left join process_mst as pr on pr.process_id=pp.process_id 
	
	left join (select sum(qc.process_stock) as qc_total,process_id from tbl_allocate_process as qc 
		 where p_product_id=".$pr_id." and company_id=".$_SESSION['company_id']." group by process_id) as qc on qc.process_id=pp.process_id
		 
		 left join (select sum(qc.process_used_stock) as qc_total_rejected,process_id from tbl_allocate_process as qc 
		 where p_product_id=".$pr_id." and company_id=".$_SESSION['company_id']." group by process_id) as qc1 on qc1.process_id=pp.process_id
	
	
	where pp.status = 0 and pp.product_id='$pr_id'
	";
	
	$rel=$dbcon->query($q);
	//$str=array();
	$str='';
	$str='<table class="table ">';
	while($row=mysqli_fetch_array($rel))
	{
		$stock = ($row['process_opening']+$row['qc_total'])-$row['qc_total_rejected'];
		
		$str.='<tr>
			<td>'.$row['process_name'].'</td>
			<td>'.$stock.'</td>
		</tr>';
		//$str[]=$row['process_name'].' - '.$row['process_opening'].'<br/>';
	}
	
	return $str;
	//return $q;
}


function get_godown_stock($dbcon,$product_id,$unit_id)
{
	$q="select gd_name,gd_id from mst_godown as gd 
			where g_status=0 order by gd_id";
	
	$rel=$dbcon->query($q);
	//$str=array();
	$str1='';
	$str1.='<table class="table ">';
	while($row=mysqli_fetch_array($rel))
	{
		$stock=get_current_godown_stock_new($dbcon,$product_id,$unit_id,$row['gd_id']);
		if($stock>0){
			$str1.='<tr>
				<td>'.$row['gd_name'].'</td>
				<td>'.$stock.'</td>
			</tr>';
		}
	}
	$str1.='</table>';
	
	return $str1;
}

//Amish Soni 04-09-2020
/* 
 * Custom function to compress image size and 
 * upload to the server using PHP 
 */ 
function compressImage($source, $destination, $quality) { 
    // Get image info 
    $imgInfo = getimagesize($source); 
    $mime = $imgInfo['mime']; 
     
    // Create a new image from file 
    switch($mime){ 
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
function getWeekendDates($date, $date2) {

	$curdate = date('d-M-Y');
	if(!$date) {
		$date = date('01-M-yy');
	}

	if(!$date2) {
		$date2 = date("t-M-Y", strtotime($curdate));
	}

	$period = new DatePeriod(
		new DateTime($date),
		new DateInterval('P1D'),
		new DateTime($date2)
	);

	$weekends = [];
	foreach ($period as $key => $value) {
		if ($value->format('N') >= 7) {
			$weekends[] = $value->format('d-m-Y');
		}  
	}

	return $weekends;
}


/*Code By Umair: Get Item Price From Purchase Crad Transaction Table based on the Vendor Selection*/
function getItemPriceByVendorId($dbcon, $vender_id, $product_id){
	 //$query="select * from tbl_purchasecardtrn where purchasecardtrn_status=0 AND vendor_id='".$vender_id."' AND product_id='".$product_id."' AND affected_date <= '".date('Y-m-d')."' order by purchasecardtrn_id desc limit 1" ;
	$query="select tpt.*, `u`.`user_name` from tbl_purchasecardtrn as tpt left join users as u ON `tpt`.`user_id`=`u`.`user_id` where `tpt`.`purchasecardtrn_status`=0 AND `tpt`.`vendor_id`='".$vender_id."' AND `tpt`.`product_id`='".$product_id."' AND `tpt`.`purchase_type`='0' AND `tpt`.`company_id`='".$_SESSION['company_id']."' AND `tpt`.`affected_date` <= '".date('Y-m-d')."' order by `tpt`.`purchasecardtrn_id` desc limit 1" ;

	 $result=$dbcon->query($query);
	 $row=mysqli_fetch_assoc($result);
	 return $row;
}

/*Code By Umair: Get Item Price From Purchase Crad Transaction Table based on the Item Selection*/
function getItemPriceByProductId($dbcon, $product_id, $vender_id){
	 //$query="select * from tbl_purchasecardtrn where purchasecardtrn_status=0 AND vendor_id='".$vender_id."' AND product_id='".$product_id."' AND affected_date <= '".date('Y-m-d')."' order by purchasecardtrn_id desc limit 1" ;
	 $query="select tpt.*, `u`.`user_name` from tbl_purchasecardtrn as tpt left join users as u ON `tpt`.`user_id`=`u`.`user_id` where `tpt`.`purchasecardtrn_status`=0 AND `tpt`.`vendor_id`='".$vender_id."' AND `tpt`.`product_id`='".$product_id."' AND `tpt`.`purchase_type`='1' AND `tpt`.`company_id`='".$_SESSION['company_id']."' AND `tpt`.`affected_date` <= '".date('Y-m-d')."' order by `tpt`.`purchasecardtrn_id` desc limit 1" ;
	 $result=$dbcon->query($query);
	 $row=mysqli_fetch_assoc($result);
	 return $row;
}

/*Code By Umair: Get Purchase Type For Purchase Bill*/
function purchase_type_main_bill($dbcon,$id)
{   
	$array = array('', 'General','Job Works', 'Service Order' );
	//echo '<option value="">Choose Company</option>';
	foreach($array as $key => $val)
	{	
		if($key > 0){
			$sel='';
			if($key==$id)
			{
				$sel="selected='selected'";
			}
			echo '<option '.$sel.' value="'.$key.'">'.$val.'</option>';
		}
		
	}
}

/*Code By Umair: Get Purchase Type For Purchase Bill*/
function purchase_type_second_bill($dbcon,$id)
{   
	$array = array('', 'Local Purchase From Manufacturer','Local Purchase From Dealer', 'Import', 'Capital Goods Bill', 'Capital Goods Bill (Import)', 'Job Work Bill', 'Service Tax Bill', 'Other Bill' );
	//echo '<option value="">Choose Company</option>';
	foreach($array as $key => $val)
	{	
		if($key > 0){
			$sel='';
			if($key==$id)
			{
				$sel="selected='selected'";
			}
			echo '<option '.$sel.' value="'.$key.'">'.$val.'</option>';
		}
	}
}

/*Code By Umair: Get Purchase Type For Tax Type*/
function tax_type_bill($dbcon,$id)
{   
	$array = array('', 'GST');
	//echo '<option value="">Choose Company</option>';
	foreach($array as $key => $val)
	{	
		if($key > 0){
			$sel='';
			if($key==$id)
			{
				$sel="selected='selected'";
			}
			echo '<option '.$sel.' value="'.$key.'">'.$val.'</option>';
		}
	}
}


/*Code By Umair: Get Purchase Type For ITC*/
function itc_bill($dbcon,$id)
{   
	$array = array('', 'Yes', 'No');
	//echo '<option value="">Choose Company</option>';
	foreach($array as $key => $val)
	{	
		if($key > 0){
			$sel='';
			if($key==$id)
			{
				$sel="selected='selected'";
			}
			echo '<option '.$sel.' value="'.$key.'">'.$val.'</option>';
		}
	}
}
/*Code By Umair: Get Supply Type For Purchase Bill*/
function supply_type_main_bill($dbcon,$id)
{   
	$array = array('', 'Goods','Services');
	//echo '<option value="">Choose Company</option>';
	foreach($array as $key => $val)
	{	
		if($key > 0){
			$sel='';
			if($key==$id)
			{
				$sel="selected='selected'";
			}
			echo '<option '.$sel.' value="'.$key.'">'.$val.'</option>';
		}
	}
}
/*Code By Umair: Get Supply Second Type For Purchase Bill*/
function supply_type_second_bill($dbcon,$id)
{   
	$array = array('', 'Intrastate Purchase Taxable');
	//echo '<option value="">Choose Company</option>';
	foreach($array as $key => $val)
	{	
		if($key > 0){
			$sel='';
			if($key==$id)
			{
				$sel="selected='selected'";
			}
			echo '<option '.$sel.' value="'.$key.'">'.$val.'</option>';
		}
	}
}
/*Code By Umair: Get Supply Second Type For Purchase Bill*/
function gst_type_bill($dbcon,$id)
{   
	$array = array('', 'Bill Wise', 'Item Wise');
	//echo '<option value="">Choose Company</option>';
	foreach($array as $key => $val)
	{	
		if($key > 0){
			$sel='';
			if($key==$id)
			{
				$sel="selected='selected'";
			}
			echo '<option '.$sel.' value="'.$key.'">'.$val.'</option>';
		}
	}
}
/*Code By Umair: Get Supply Second Type For Purchase Bill*/
function reverse_type_bill($dbcon,$id)
{   
	$array = array('','Yes', 'No');
	//echo '<option value="">Choose Company</option>';
	foreach($array as $key => $val)
	{	
		if($key > 0){
			$sel='';
			if($key==$id)
			{
				$sel="selected='selected'";
			}
			echo '<option '.$sel.' value="'.$key.'">'.$val.'</option>';
		}
	}
}

/*Code By Umair: Get Expense Name By ID*/
function get_expense_name_by_id($dbcon,$id)
{
	$query="select l_id,l_name from tbl_ledger where l_id='$id'";
	
	$row=$dbcon->query($query);
	
	$rel=mysqli_fetch_assoc($row);
	
	return $rel['l_name'];
}


/*Code By Umair: 04/11/2020
Comment: Get the Salary account user list
*/
function getsalaryemployee($dbcon,$id) {
	$query="select l_id,l_name from tbl_ledger where l_status=0 and l_form='emp_form'";
	$rs_cust=$dbcon->query($query);	
	$str= '<option value="">Choose Employee</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		$sel='';
		if($rel['l_id']==$id) {
			$sel="selected='selected'";
		}
		$str.= '<option '.$sel.' value="'.$rel['l_id'].'">'.$rel['l_name'].'</option>';
	}
	return $str;
}

function reciptdata($dbcon,$poid){
	$response=[];
	 $query="SELECT  trn.total_amount as clearpayment,tr.cheque_dtl as chqnumber,tr.ref_date as chqdate,tr.payment_date,tr.payment_remark
	FROM tbl_receipt_trn as trn
	left JOIN tbl_receipt as tr ON trn.receipt_id=tr.receipt_id
	where trn.status!='2' and trn.purchase_id=".$poid;
	$result1=$dbcon->query($query);
	while($rel=mysqli_fetch_assoc($result1))
	{	
		array_push($response, $rel);
	}
	return $response;
}

/*Code By Umair: 04/11/2020
Comment: Get All Resource name
*/
function get_all_resource($dbcon,$id, $where=null){
	if($where){
		$where = 'AND '.$where;
	}
	$str='';
	$query="select * from tbl_resource where resource_status=0 $where";
	$rs_product=$dbcon->query($query);
	$str.='<option value="">--Select Resource--</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['resource_id']==$id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['resource_id'].'">'.$rel['resource_name'].'</option>';
	}
	return $str;
}

/*Code By Umair: 09/11/2020
Comment: Get Resource Name By ID
*/
function get_resource_info_by_id($dbcon, $where=null){
	if($where){
		$where = 'AND '.$where;
	}
	$query="select * from tbl_resource where resource_status=0 $where";
	$rs_product=$dbcon->query($query);
	$rel=mysqli_fetch_assoc($rs_product);
	return $rel;
}

/*Code By Umair: 09/11/2020
Comment: Get Completed Date Of Resource Based On The Working Hours
*/
function get_completed_date_of_resource_based_on_working_hours($startdate, $numberofdays){
	//echo date('Y-m-d',strtotime('+1 day'));    //+1 day from today

    $d = new DateTime( $startdate );
    $t = $d->getTimestamp();

    // loop for X days
    for($i=1; $i<$numberofdays; $i++){

        // add 1 day to timestamp
        $addDay = 86400;

        // get what day it is next day
        $nextDay = date('w', ($t+$addDay));

        // if it's Saturday or Sunday get $i-1
        /*if($nextDay == 0 || $nextDay == 6) {
            $i--;
        }*/

        if($nextDay == 0) {
            $i--;
        }

        // modify timestamp, add 1 day
        $t = $t+$addDay;
    }

    $d->setTimestamp($t);

    return $d->format( 'Y-m-d' );
}

/*Code By Umair: 04/11/2020
Comment: Insert Work Order Resource Allocation In tbl_work_order_resource_allocate table
*/
function work_order_resource_allocate($dbcon, $resource_id=null, $request_id=null, $process_id=null, $product_id=null, $qty=null, $time_per_qty=null, $edit_id=null, $action_type=null, $completed_time=null){

	$process_allocate['resource_id'] = $resource_id;
	$process_allocate['request_id'] = $request_id;
	$process_allocate['process_id'] = $process_id;
	$process_allocate['product_id'] = $product_id;
	$process_allocate['qty'] = $qty;
	$process_allocate['time_per_qty'] = $time_per_qty;
	$process_allocate['total_time'] = ($time_per_qty*$qty);
	$process_allocate['completed_time'] = $completed_time;
	$process_allocate['user_id'] = $_SESSION['user_id'];
	$process_allocate['cdate'] = date('Y-m-d H:i:s');
	$process_allocate['company_id'] = $_SESSION['company_id'];
	$process_allocate['resourse_allocation_status'] = 0;
	
	$return='';
	if($action_type=='add'){
		$return = add_record('tbl_work_order_resource_allocate', $process_allocate, $dbcon);
	}
	return $return;
}

/*Code By Umair: 06/11/2020
Comment: Insert Work Order Resource Transfer. Insert Log In tbl_resource_allocation_transfer table
*/

function work_order_resource_transfer($dbcon, $resource_id_by=null, $resource_id_to=null, $process_id=null, $product_id=null, $qty=null, $resource_transfer_allocate_id=null, $work_order_id=null){

	$resource_transfer['resource_id_by'] = $resource_id_by;
	$resource_transfer['resource_id_to'] = $resource_id_to;
	$resource_transfer['resource_transfer_number'] = rand(111111,999999);
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

	$return = add_record('tbl_resource_allocation_transfer', $resource_transfer, $dbcon);
	return $return;
}

function getpaymentbilldatawithledger($dbcon,$vender_id,$postdata){
	$response=[];
	$query="SELECT tpo.po_no as billno,tpo.po_date as billdate,sum(tpot.total) as billtotal,tpo.g_total as grossamt,(select IFNULL(SUM(tbl_receipt_trn.total_amount), 0) from tbl_receipt_trn where tbl_receipt_trn.purchase_id=tpo.po_id ) as clearedpayment,tr.cheque_dtl as chqnumber,tr.ref_date as chqdate,tr.payment_date,tr.payment_remark,tpo.po_id
	FROM tbl_pono as tpo
	left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
	left JOIN tbl_receipt_trn as trt ON trt.purchase_id=tpo.po_id
	left JOIN tbl_receipt as tr ON trt.receipt_id=tr.receipt_id
	where tpo.status!='2' and tpo.vender_id=".$vender_id;

	if($postdata['po_date_type']){
		if($postdata['po_date_type']=='po'){
			$s_date=explode(' - ',$postdata['rep_po_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" and tpo.po_date>='".date('Y-m-d',strtotime($startdate))."' and tpo.po_date<='".date('Y-m-d',strtotime($enddate))."'";
		}else{
			$s_date=explode(' - ',$postdata['rep_del_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" and tpo.po_date>='".date('Y-m-d',strtotime($startdate))."' and tpo.po_date<='".date('Y-m-d',strtotime($enddate))."'";
		}
	}

	if(isset($postdata['specific_vendor'])){
		if($postdata['vendor_id']){
			$query.=' and tpo.vender_id='.$postdata['vendor_id'];
		}
	}
	if(isset($postdata['specific_item'])){
		if($postdata['item_id']){
					$query.=' and tpot.product_id='.$postdata['item_id'];
		}
	}
	if(isset($postdata['purchase_type_status'])){
		if($postdata['purchase_type_id']){
					$query.=' and tpo.purchase_bill_type='.$postdata['purchase_type_id'];
		}
	}
//	echo $query;
    $query.=' group by tpo.po_id';
	$result1=$dbcon->query($query);
	while($rel=mysqli_fetch_assoc($result1))
	{	
		array_push($response, $rel);
	}
	return $response;
}

function getpaymentbilldata($dbcon,$vender_id,$postdata){
	$response=[];
	$query="SELECT tpo.po_no as billno,tpo.po_date as billdate,sum(tpot.total) as billtotal,tpo.g_total as grossamt,(select IFNULL(SUM(tbl_receipt_trn.total_amount), 0) from tbl_receipt_trn where tbl_receipt_trn.purchase_id=tpo.po_id ) as clearedpayment
	FROM tbl_pono as tpo
	left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
	inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
    where tpo.status!='2' and tpo.vender_id=".$vender_id;

	if($postdata['po_date_type']){
		if($postdata['po_date_type']=='po'){
			$s_date=explode(' - ',$postdata['rep_po_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" and tpo.po_date>='".date('Y-m-d',strtotime($startdate))."' and tpo.po_date<='".date('Y-m-d',strtotime($enddate))."'";
		}else{
			$s_date=explode(' - ',$postdata['rep_del_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" and tpo.po_date>='".date('Y-m-d',strtotime($startdate))."' and tpo.po_date<='".date('Y-m-d',strtotime($enddate))."'";
		}
	}

	if(isset($postdata['specific_vendor'])){
		if($postdata['vendor_id']){
			$query.=' and tpo.vender_id='.$postdata['vendor_id'];
		}
	}
	if(isset($postdata['specific_item'])){
		if($postdata['item_id']){
					$query.=' and tpot.product_id='.$postdata['item_id'];
		}
	}
	if(isset($postdata['purchase_type_status'])){
		if($postdata['purchase_type_id']){
					$query.=' and tpo.purchase_bill_type='.$postdata['purchase_type_id'];
		}
	}
//	echo $query;
    $query.=' group by tpo.po_id';
	$result1=$dbcon->query($query);
	while($rel=mysqli_fetch_assoc($result1))
	{	
		array_push($response, $rel);
	}
	return $response;
}


function getpaymentbilldatabillnowise($dbcon,$postdata){
	$response=[];
	$query="SELECT tpo.po_no as billno,tl.l_name,tpo.po_date as billdate,sum(tpot.total) as billtotal,tpo.g_total as grossamt,(select IFNULL(SUM(tbl_receipt_trn.total_amount), 0) from tbl_receipt_trn where tbl_receipt_trn.purchase_id=tpo.po_id ) as clearedpayment
	FROM tbl_pono as tpo
	left JOIN tbl_ledger as tl ON tl.l_id=tpo.vender_id 
	left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
	inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
    where tpo.status!='2'";

	if($postdata['po_date_type']){
		if($postdata['po_date_type']=='po'){
			
			$startdate=$postdata['from_po_date'];
			$enddate=$postdata['to_po_date'];
			$query.=" and tpo.po_date>='".date('Y-m-d',strtotime($startdate))."' and tpo.po_date<='".date('Y-m-d',strtotime($enddate))."'";
		}else{
			$startdate=$postdata['from_po_date'];
			$enddate=$postdata['to_po_date'];
			$query.=" and tpo.po_date>='".date('Y-m-d',strtotime($startdate))."' and tpo.po_date<='".date('Y-m-d',strtotime($enddate))."'";
		}
	}

	if(isset($postdata['specific_vendor'])){
		if($postdata['vendor_id']){
			$query.=' and tpo.vender_id='.$postdata['vendor_id'];
		}
	}
	if(isset($postdata['specific_item'])){
		if($postdata['item_id']){
					$query.=' and tpot.product_id='.$postdata['item_id'];
		}
	}
	if(isset($postdata['purchase_type_status'])){
		if($postdata['purchase_type_id']){
					$query.=' and tpo.purchase_bill_type='.$postdata['purchase_type_id'];
		}
	}
//	echo $query;
    $query.=' group by tpo.po_id';
	$result1=$dbcon->query($query);
	while($rel=mysqli_fetch_assoc($result1))
	{	
		array_push($response, $rel);
	}
	return $response;
}

function getproductbysalesorder($dbcon,$prids)
{	
	
	$str='';
	
	 $query="select * from product_mst as pro where product_status=0 and  product_id in ($prids) and company_id in (0,$_SESSION[company_id]) order by product_name";
	$rs_dispatch=$dbcon->query($query);	
	$str .= '<option value="">Choose Product</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
				$sel=''; 
				if($rel['product_id']==$id)
				{$sel ="selected='selected'";}
				$str .= '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name'].' - '.$rel['product_code'].'</option>';
	}
	return $str;
}

function getstages($dbcon)
{   
	$str='';
	$query="select * from stage_mst as l where l.stage_status=0";
	$rs_cust=$dbcon->query($query);	
	$str= '<option value="">Choose Stage</option>';
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		 $sel='';
		// if($rel['l_id']==$id) { $sel="selected='selected'"; }
		$str.= '<option '.$sel.' value="'.$rel['stage_id'].'">'.$rel['stage_name'].'.</option>';
	}
	return $str;
	//return $query;
} 

function getsalesorderprdctqty($dbcon,$prid,$sales_order_id)
{
	$query="select product_qty from tbl_sales_ordertrn where product_id='$prid' and sales_order_id='$sales_order_id'";
	
	$row=$dbcon->query($query);
	
	$rel=mysqli_fetch_assoc($row);
	
	return $rel['product_qty'];
}
function getstagedata($dbcon,$field,$prid,$sales_order_id,$stageid)
{
	 $query="select ".$field." from tbl_sales_order_stage where product_id='$prid' and sales_order_id='$sales_order_id' and stage_id=$stageid";
	
	$row=$dbcon->query($query);
	
	$rel=mysqli_fetch_assoc($row);
	
	return $rel[$field];
}
//(select sum(tbl_grn_trn.product_qty)  from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as pending
function sostagereportdata($dbcon,$postdata){
	$response=[];
 	 $query="SELECT DISTINCT tsos.product_id,tsos.sales_order_id,tso.sales_order_no,tsos.sales_order_id,pm.product_name,pm.product_hsn,tl.l_name,bunit.unit_name,tsos.product_id
        FROM tbl_sales_order_stage as tsos left JOIN tbl_sales_order as tso ON tso.sales_order_id=tsos.sales_order_id inner JOIN product_mst as pm ON pm.product_id=tsos.product_id 
	left JOIN tbl_ledger as tl ON tl.l_id=tso.cust_id 
	left join unit_mst as bunit on bunit.unitid=tsos.unitid
    where tso.sales_order_status!='2'";
    
	$result1=$dbcon->query($query);
	while($rel=mysqli_fetch_assoc($result1))
	{	
		array_push($response, $rel);
	}
	return $response;
}
// left join tbl_product_stage as tps on tps.pro
function get_stage_completed_per($dbcon,$sales_order_id,$prid,$stage_id){
	$response=[];
	  $query="select sum(tsos.accept_qty) as accepttotqty,tsos.product_qty,tsos.stage_id,tps.stage_per from tbl_sales_order_stage  as tsos
		 left join tbl_product_stage as tps on tps.party_product=tsos.product_id and tps.stage_id=tsos.stage_id
	     where tsos.product_id='$prid' and tsos.sales_order_id='$sales_order_id' group by tsos.stage_id";
	$result1=$dbcon->query($query);
	$tot_pecenatge=0;
	while($rel=mysqli_fetch_assoc($result1))
	{	
		$rel['totacptqty_ratio']=$rel['accepttotqty']*100/$rel['product_qty'];
		$rel['com_percentage']=$rel['totacptqty_ratio']*$rel['stage_per']/100;
		$tot_pecenatge=$tot_pecenatge+$rel['com_percentage'];
		array_push($response, $rel);
	}
	
	return number_format($tot_pecenatge,2);
}
function getcurrentstage($dbcon,$sales_order_id,$prid)
{

	 $query="select tsos.stage_id,sm.stage_name from tbl_sales_order_stage  as tsos
		 left join tbl_product_stage as tps on tps.party_product=tsos.product_id and tps.stage_id=tsos.stage_id
		  left join  stage_mst as sm on tsos.stage_id=sm.stage_id
	     where tsos.product_id='$prid' and tsos.sales_order_id='$sales_order_id'  order by tsos.id desc limit 1";
	
	$row=$dbcon->query($query);
	
	$rel=mysqli_fetch_assoc($row);
	
	return $rel['stage_name'];
}
function getmaxtqtystagewise($dbcon,$stageid,$prdctid,$sales_order_id){
	 $query="SELECT sum(tsos.accept_qty) as acceptqty,(tso.product_qty - (IFNULL(sum(tsos.accept_qty),0))) as remaining
	 		FROM tbl_sales_ordertrn as tso
			left JOIN tbl_sales_order_stage as tsos ON tsos.product_id=tso.product_id and tsos.sales_order_id=tsos.sales_order_id
			where tsos.stage_id=".$stageid." and tsos.product_id=".$prdctid." and tsos.sales_order_id=".$sales_order_id;
	$row=$dbcon->query($query);
	$rel=mysqli_fetch_assoc($row);
	return $rel['remaining'];
}

?>