<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/coman_function.php");
include("../../include/function_database_query.php");
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ 
{ 
    /*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "generate_report") {
				$s_date=date('Y-m-d',strtotime($POST['date']));
				$branch=$POST['branch_id'];
				$cat_id=$POST['cat_id'];
				
				$where='';
				if($cat_id=='')
				{
					$where.=" and pro.product_cat=0";
					$where1.=" and cat_pid=0";
				}
				else
				{
					$where.=" and pro.product_cat='$cat_id'";
					$where1.=" and cat_pid='$cat_id'";
				}
				
				//$where.=" and pro.branch_id='$branch'";
		if(!empty($POST['material_id']))
		{
			$where.=' and pro.product_id='.$POST['material_id'];
		}
		/*if(!empty($POST['branch_id']))
		{
			$where.=' and pro.branch_id='.$branch;
		}*/
		$query='select * from ((SELECT pro.product_id as pid,"product" as type,product_name as pr_name,minimum_stock as min_stock,opening_stock as op_stock,pur_qty,today_pur_qty,jobout_qty,today_jobout_qty,jobin_qty,today_jobin_qty,inv_qty,today_inv_qty FROM `tbl_product` as pro 
	 
		left join (select sum(opntrn.product_stock) as opening_stock,opntrn.product_id from tbl_branch_product_stock as opntrn  where opntrn.status=0 and opntrn.branch_id in (0,'.$branch.') and opntrn.company_id='.$_SESSION['company_id'].' group by opntrn.product_id) as opn_stock on opn_stock.product_id=pro.product_id
		
		left join (select sum(purtrn.product_qty) as pur_qty,purtrn.product_id from tbl_potrancation as purtrn left join tbl_pono as po on po.po_id=purtrn.po_id where purtrn.potrancation_status=0 and po.po_date < "'.$s_date.'" and purtrn.company_id='.$_SESSION['company_id'].' and purtrn.branch_id in (0,'.$branch.') group by purtrn.product_id) as grn on grn.product_id=pro.product_id
		
		left join (select sum(purtrnt.product_qty) as today_pur_qty,purtrnt.product_id from tbl_potrancation as purtrnt left join tbl_pono as po on po.po_id=purtrnt.po_id where purtrnt.potrancation_status=0 and po.po_date ="'.$s_date.'" and purtrnt.company_id='.$_SESSION['company_id'].' and purtrnt.branch_id in (0,'.$branch.') group by purtrnt.product_id) as grn1 on grn1.product_id=pro.product_id
		
		left join (select sum(jobout.outward_product_required_qty) as jobout_qty,jobout.raw_product_id from tbl_jobworktrn as jobout left join tbl_jobwork as job on job.jobwork_id=jobout.jobwork_id where jobout.jobworktrn_status=0 and job.jobwork_date < "'.$s_date.'" and jobout.jobwork_id!=0 and jobout.company_id='.$_SESSION['company_id'].' and jobout.branch_id in (0,'.$branch.') group by jobout.raw_product_id) as jout on jout.raw_product_id=pro.product_id
		
		left join (select sum(jobout1.outward_product_required_qty) as today_jobout_qty,jobout1.raw_product_id,jobout1.cdate from tbl_jobworktrn as jobout1 left join tbl_jobwork as job on job.jobwork_id=jobout1.jobwork_id where jobout1.jobworktrn_status=0 and job.jobwork_date = "'.$s_date.'" and jobout1.jobwork_id!=0 and jobout1.company_id='.$_SESSION['company_id'].' and jobout1.branch_id in (0,'.$branch.') group by jobout1.raw_product_id) as jout1 on jout1.raw_product_id=pro.product_id
		
		left join (select sum(jobin.inw_qty) as jobin_qty,jobin.product_id,jobin.jobwork_inward_date from tbl_jobwork_inward as jobin where jobin.status=0 and jobin.jobwork_inward_date < DATE_FORMAT(jobin.jobwork_inward_date,"%y-%m-%d") and jobin.company_id='.$_SESSION['company_id'].' and jobin.branch_id in (0,'.$branch.') group by jobin.product_id) as jin on jin.product_id=pro.product_id
		
		left join (select sum(jobin1.inw_qty) as today_jobin_qty,jobin1.product_id,jobin1.jobwork_inward_date from tbl_jobwork_inward as jobin1 where jobin1.status=0 and jobin1.jobwork_inward_date = DATE_FORMAT(jobin1.jobwork_inward_date,"%y-%m-%d") and jobin1.company_id='.$_SESSION['company_id'].' and jobin1.branch_id in (0,'.$branch.') group by jobin1.product_id) as jin1 on jin1.product_id=pro.product_id
		
		left join (select sum(intrn.product_qty) as inv_qty,intrn.product_id from tbl_invoicetrn as intrn left join tbl_invoice as i on i.invoice_id=intrn.invoice_id where intrn.trancation_status=0 and i.invoice_date < "'.$s_date.'" and intrn.company_id='.$_SESSION['company_id'].' and intrn.branch_id in (0,'.$branch.') group by intrn.product_id) as invt on invt.product_id=pro.product_id
		
		left join (select sum(intrn1.product_qty) as today_inv_qty,intrn1.product_id,intrn1.cdate from tbl_invoicetrn as intrn1 left join tbl_invoice as i on i.invoice_id=intrn1.invoice_id where intrn1.trancation_status=0 and i.invoice_date = "'.$s_date.'"  and  intrn1.company_id='.$_SESSION['company_id'].' and intrn1.branch_id in (0,'.$branch.') group by intrn1.product_id) as invt1 on invt1.product_id=pro.product_id
		
		where pro.product_status!=2 and pro.branch_id in (0,'.$branch.') '.$where.' group by pro.product_id order by product_name)
		
		union (select cat_id as pid,"category" as type,cat_name as pr_name,0 as min_stock,null as op_stock,"","","","","","","","" from tbl_category where cat_status!=2 '.$where1.') 
		
		) as tunion 
		
		' ;
		$rs=$dbcon->query($query);
			$str='';
			$i=1;$total_mat_val=0;
			$rel_num_rows=mysqli_num_rows($rs);
			if($rel_num_rows){
				while($rel=mysqli_fetch_assoc($rs))
				{
					
					$op_stock=(($rel['op_stock']+$rel['pur_qty']+$rel['jobin_qty'])-($rel['jobout_qty']+$rel['inv_qty']));
					
					
					$total=$op_stock+($rel['today_pur_qty']+$rel['today_jobin_qty']);
					$cl_stock=$total-($rel['today_jobout_qty']+$rel['today_inv_qty']);
					if($cl_stock<=$rel['minimum_stock']){
						$style="color: red;font-weight: bold;";
					}
					else{
						$style="";
					}
					
					//category link
					if($rel['type']=='category')
					{
						$link_cat=''.ROOT.'report-stock?id='.$rel['pid'];
						$style1="color: green;font-weight:bold";
						$style2="border-bottom:dotted 2px blue";
					}
					else
					{
						$link_cat='#';
						$style1="color: red;font-weight:bold";
						$style2="";
					}
					
					$str.='<tr>
							  <td style="text-align:center;">'.$i.'</td>
					
							  <td style="text-align:center;'.$style1.'">'.$rel['type'].'</td>
							  <td><a href='.$link_cat.' style="text-align:center;'.$style2.'">'.$rel['pr_name'].'</a></td>
							  <td style="text-align:right;">'.$op_stock.'</td>
							  <td style="text-align:right;">'.$rel['min_stock'].'</td>
							  <td style="text-align:right;'.$style.'" >'.$cl_stock.'</td>
						  </tr>';
					$i++;					  
				}
			}
			else{
				$str.= '<tr><td colspan="7" style="text-align:center;">No Data Found !!!</td></tr>';
			}
			
			
			echo $str;
			
		}
	
	}
    /*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/

?>