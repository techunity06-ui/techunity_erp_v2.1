<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include("../include/function_database_query.php");
	$form="Country";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$end = date("d-m-Y");
	

		$query="select * from tbl_ledger where l_form='customer_form' and cust_id=0 and l_status=0";
		$result=$dbcon->query($query); 
		while($rel=mysqli_fetch_assoc($result))
		{	
			
			$query_cust="select * from tbl_customer where cust_name='".$rel['l_name']."' and cust_status=0";
			$result_cust=$dbcon->query($query_cust); 
			$rel_cust=mysqli_fetch_assoc($result_cust);
			if(empty($rel_cust['cust_id'])){
				$branch_id = $rel['branch_id'];
				$company_id = $rel['company_id'];
				$user_id = $rel['user_id'];
				
				$infoadd['party_type']				= 0;
				$infoadd['cust_name']				= $rel['l_name'];
				$infoadd['cust_creator']			= 0;
				$infoadd['cust_code']				= get_customer_code($dbcon);
				$infoadd['cust_cat']				= "";
				$infoadd['cust_desc']				= "";
				$infoadd['cust_ind']				= "";
				$infoadd['cust_type']				= 24;
				$infoadd['cust_source']				= 12;
				$infoadd['cust_gst']				= $rel['gst_no'];
				$infoadd['cust_mobile']				= $rel['cust_mobile'];
				$infoadd['cust_email']				= $rel['cust_email'];
				$infoadd['cust_assign_user']		= "";
				$infoadd['birth_date']				= "";
				$infoadd['anniversary_date']		= "";
				$infoadd['relation']				= "";
				$infoadd['gender']					= "";
				$infoadd['cdate']					= date("Y-m-d H:i:s");
				$infoadd['cust_status']				= 0;
				$infoadd['cust_block_status']		= 0;
				$infoadd['user_id']					= $user_id;
				$infoadd['company_id']				= $company_id;
				$infoadd['cust_code_series']		= get_customer_code_series($dbcon);
				
				$inseraddid=add_record('tbl_customer', $infoadd, $dbcon, $branch_id);
				
					$info['c_add_location']			=	$rel['m_address'];
					$info['c_add_street']			=	"";
					$info['c_add_country']			=	$rel['countryid'];
					$info['c_add_state']			=	$rel['stateid'];
					$info['c_add_city']				=	$rel['cityid'];
					$info['c_add_zip']				=	$rel['cust_pincode'];
					$info['cust_id']				=	$inseraddid;
					$info['cdate']					= 	date("Y-m-d H:i:s");
					$info['user_id']				= 	$user_id;
					$info['company_id']				= 	$company_id;
					
					$inserid=add_record('tbl_cust_address', $info, $dbcon, $branch_id);
					
					
					$info_co['c_con_fname']		= $rel['cust_cont_name'];
					$info_co['c_con_lname']		= "";
					$info_co['c_con_email']		= $rel['cust_email'];
					$info_co['c_con_mobile']	= $rel['cust_mobile'];
					$info_co['c_con_phone']		= $rel['cust_mobile'];
					$info_co['c_con_job']		= "";
					$info_co['cust_id']			= $inseraddid;
					$info_co['cdate']			= date("Y-m-d H:i:s");
					$info_co['user_id']			= $user_id;
					$info_co['company_id']		= $company_id;
					
					$inserid_co=add_record('tbl_cust_contact', $info_co, $dbcon, $branch_id);
					
					$dbcon->query("update tbl_ledger set cust_id=".$inseraddid." where l_id=".$rel['l_id']);
				
			}else{
				$dbcon->query("update tbl_ledger set cust_id=".$rel_cust['cust_id']." where l_id=".$rel['l_id']);
			}
			
		}
?>

