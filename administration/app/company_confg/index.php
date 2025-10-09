<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$getspecialConfiguration=getspecialConfiguration($dbcon);
//echo '<pre>'; print_r(@$_POST);exit;
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(strtolower($POST['mode']) == "add") {

	$admin_info['common_email_id'] = @$POST['common_email_id'];

	$updatedid=update_record('users', $admin_info,"user_id=".$_SESSION['user_id'], $dbcon);
	// echo '<pre>';print_r(@$POST);exit;
	$info['enable_cost_center']				= isset($POST['enable_cost_center']) ? '1' : '0' ;
	$info['enable_party_dashboard']			= isset($POST['enable_party_dashboard']) ? '1' : '0' ;
	$info['enable_bank_reconcilation']		= isset($POST['enable_bank_reconcilation']) ? '1' : '0' ;
	$info['enablae_pdc']					= isset($POST['enablae_pdc']) ? '1' : '0' ;
	$info['enable_month_budget']			= isset($POST['enable_month_budget']) ? '1' : '0' ;
	$info['enable_depreciation']			= isset($POST['enable_depreciation']) ? '1' : '0' ;
	$info['enable_multi_currency']			= isset($POST['enable_multi_currency']) ? '1' : '0' ;
	$info['enable_salesman']				= isset($POST['enable_salesman']) ? '1' : '0' ;
	$info['enable_transport']				= isset($POST['enable_transport']) ? '1' : '0' ;
	$info['enable_billby_bill_blnc']		= isset($POST['enable_billby_bill_blnc']) ? '1' : '0' ;
	$info['enable_scheme']					= isset($POST['enable_scheme']) ? '1' : '0' ;
	$info['enable_paramter_stock']			= isset($POST['enable_paramter_stock']) ? '1' : '0' ;
	$info['enable_batch_stock']				= isset($POST['enable_batch_stock']) ? '1' : '0' ;
	$info['enable_serial_stock']			= isset($POST['enable_serial_stock']) ? '1' : '0' ;
	$info['enable_mrp_stock']				= isset($POST['enable_mrp_stock']) ? '1' : '0' ;
	$info['enable_free_qty']				= isset($POST['enable_free_qty']) ? '1' : '0' ;
	$info['enable_negative_qty']			= isset($POST['enable_negative_qty']) ? '1' : '0' ;
	$info['enable_voucher_approval']		= isset($POST['enable_voucher_approval']) ? '1' : '0' ;
	$info['enable_old_dashbord']			= isset($POST['enable_old_dashbord']) ? '1' : '0' ;
	$info['enable_sms']						= isset($POST['enable_sms']) ? '1' : '0' ;
	$info['enable_email']					= isset($POST['enable_email']) ? '1' : '0' ;
	$info['enable_tds_reporting']			= isset($POST['enable_tds_reporting']) ? '1' : '0' ;
	$info['enable_tcs_reporting']			= isset($POST['enable_tcs_reporting']) ? '1' : '0' ;
	$info['enable_eway_bill']				= isset($POST['enable_eway_bill']) ? '1' : '0' ;
	$info['enable_einvoice']				= isset($POST['enable_einvoice']) ? '1' : '0' ;
	$info['enable_gst_filling']				= isset($POST['enable_gst_filling']) ? '1' : '0' ;	
	$info['enable_consolidate_item'] 		= isset($POST['enable_consolidate_item']) ? '1' : '0' ;
	$info['gross_balance_limit'] 			= @$POST['gross_balance_limit'];
	$info['gross_balance_tds_limit'] 		= isset($POST['gross_balance_tds_limit']) ? $POST['gross_balance_tds_limit'] : '0' ;
	$info['item_code_generate']				= isset($POST['item_code_generate']) ? '1' : '0' ;
	$info['common_item_diff_company']		= isset($POST['common_item_diff_company']) ? '1' : '0' ;
	$info['party_code_generate']			= isset($POST['party_code_generate']) ? '1' : '0' ;
	$info['multiple_make_item_master']		= isset($POST['multiple_make_item_master']) ? '1' : '0' ;
	$info['grn_sticker_print']				= isset($POST['grn_sticker_print']) ? '1' : '0' ;
	$info['enable_consolidate_item'] 		= isset($POST['enable_consolidate_item']) ? '1' : '0' ;
	$info['design_department'] 				= isset($POST['design_department']) ? '1' : '0' ;
	$info['design_so_customization'] 		= isset($POST['design_so_customization']) ? '1' : '0' ;
	$info['enable_count_outstanding_target'] = isset($POST['enable_count_outstanding_target']) ? '1' : '0' ;
	$info['enable_hypothication'] = isset($POST['enable_hypothication']) ? '1' : '0' ;
	$info['batchno_as_grnno'] = isset($POST['batchno_as_grnno']) ? '1' : '0' ;
	$info['enable_post_crm'] 		= isset($POST['enable_post_crm']) ? '1' : '0' ;
	$info['enable_installation_type'] 		= isset($POST['enable_installation_type']) ? '1' : '0' ;

	$info['crm_pro_search']					= trim(implode(",", @$_POST['crm_pro_search']),",");
	$info['purchase_pro_search'] 			= trim(implode(",", @$_POST['purchase_pro_search']),",");
	$info['production_pro_search'] 			= trim(implode(",", @$_POST['production_pro_search']),",");
	$info['sales_pro_search'] 				= trim(implode(",", @$_POST['sales_pro_search']),",");
	$info['bom_pro_search'] 				= trim(implode(",", @$_POST['bom_pro_search']),",");
	$info['service_pro_search']				= trim(implode(",", @$_POST['service_pro_search']),",");
	$info['crm_pro_print']					= trim(implode(",", @$_POST['crm_pro_print']),",");
	$info['purchase_pro_print'] 			= trim(implode(",", @$_POST['purchase_pro_print']),",");
	$info['production_pro_print'] 			= trim(implode(",", @$_POST['production_pro_print']),",");
	$info['sales_pro_print'] 				= trim(implode(",", @$_POST['sales_pro_print']),",");
	$info['bom_pro_print'] 					= trim(implode(",", @$_POST['bom_pro_print']),",");
	$info['sales_party_show'] 				= trim(implode(",", @$_POST['sales_party_show']),",");
	$info['purchase_party_show'] 			= trim(implode(",", @$_POST['purchase_party_show']),",");
	$info['inventory_party_show'] 			= trim(implode(",", @$_POST['inventory_party_show']),",");
	$info['crm_pro_type'] 					= trim(implode(",", @$_POST['crm_pro_type']),",");
	$info['crm_user_type'] 					= trim(implode(",", @$_POST['crm_user_type']),",");
	$info['so_pro_type'] 					= trim(implode(",", @$_POST['so_pro_type']),",");
	$info['indent_po_pro_type'] 			= trim(implode(",", @$_POST['indent_po_pro_type']),",");
	$info['production_pro_type']			= trim(implode(",", @$_POST['production_pro_type']),",");

	$info['inventory_pro_type']				= trim(implode(",", @$_POST['inventory_pro_type']),",");
	$info['rejection_pro_type']				= trim(implode(",", @$_POST['rejection_pro_type']),",");
	$info['service_pro_type']				= trim(implode(",", @$_POST['service_pro_type']),",");
	$info['trans_dash_user_type']			= trim(implode(",", @$_POST['trans_dash_user_type']),",");
	$info['generate_item_code'] 			= @$_POST['generate_item_code'];
	$info['po_terms_conditions'] 			= stripcslashes(str_replace(array("\n", "\r", "\N"), '', @$_POST['po_terms_conditions']));
	$info['trading_stock']					= @$POST['trading_stock'];
	$info['batch_wise_stock']				= @$POST['batch_wise_stock'];
	$info['grn_diff_from_po']				= @$POST['grn_diff_from_po'];
	$info['batch_stock']					= @$POST['batch_stock'];
	$info['batch_no_stock']					= @$POST['batch_no_stock'];
	$info['sales_time_load_pro']			= @$POST['sales_time_load_pro'];
	$info['enable_assing_user'] 			= @$_POST['enable_assing_user'];	
	$info['gsp_username']					= @$POST['gsp_username'];
	$info['gsp_password']					= @$POST['gsp_password'];
	$info['upload_reciept']					= @$POST['upload_receipt'];
	$info['qc_upload_receipt']				= @$POST['qc_upload_receipt'];
	$info['store_approval']					= @$POST['store_approval'];
	$info['outside_jobwork']				= @$POST['outside_jobwork'];
	$info['resource_wise_production']		= @$POST['resource_wise_production'];
	$info['round_up_qty']					= @$POST['round_up_qty'];
	$info['process_end_time_qc']			= @$POST['process_end_time_qc'];
	$info['extra_stock']					= @$POST['extra_stock'];
	$info['bom_extra_no']					= @$POST['bom_extra_no'];
	$info['workorder_wise_production_merge']= @$POST['workorder_wise_production_merge'];
	$info['ledger_code']					= @$POST['ledger_code'];

	$info['cdate']							= date("Y-m-d H:i:s");
	$info['user_id']						= $_SESSION['user_id'];
	$info['company_id']						= $_SESSION['company_id'];
	$info['usertype_id']					= $_SESSION['user_type'];
	// $info['grn_sticker_print']				= @$POST['grn_sticker_print'];
	$info['batch_type']						= @$POST['batch_type'];
	$info['batch_process']					= @$POST['batch_process'];
	$info['po_work_order_wise']				= @$POST['po_work_order_wise'];
	$info['direct_po_create']				= @$POST['direct_po_create'];
	$info['crm_task_order']					= @$POST['crm_task_order'];
	$info['enable_quotation_limit']			= @$POST['enable_quotation_limit'];
	$info['quotation_disc_limit']			= @$POST['quotation_disc_limit'];
	$info['enable_inquiry_autoclose']		= @$POST['enable_inquiry_autoclose'];
	$info['inquiry_autoclose_limit']		= @$POST['inquiry_autoclose_limit'];
	$info['production_start_type']			= @$POST['production_start_type']; // 0 manually , 1 FIFO wise
	$info['stock_type_a']				= @$POST['stock_type_a'];
	$info['stock_type_b']				= @$POST['stock_type_b'];
	$info['stock_type_c']				= @$POST['stock_type_c'];
	$info['po_document_required']		= @$POST['po_document_required'];
	$info['hierarchy_inq_assign']		= @$POST['hierarchy_inq_assign'];
	$info['tax_editable']		= isset($POST['tax_editable']) ? '1' : '0' ;
	$info['quotation_rate_fixed']				= @$POST['quotation_rate_fixed'];
	$info['sales_wise_branch_planning']	= @$POST['sales_wise_branch_planning'];
	$info['sales_wise_branch_planning_before_bom']	= @$POST['sales_wise_branch_planning_before_bom'];
	$info['workorder_planning']				= @$POST['workorder_planning'];
	$info['production_start_stop_time']		= @$POST['production_start_stop_time'];
	$info['sending_blue_api_key']		= @$POST['sending_blue_api_key'];
	$info['sendinblue_mail_id']		= @$POST['sendinblue_mail_id'];
	$info['inq_product_required']		= @$POST['inq_product_required'];
	$info['so_description_required']		= @$POST['so_description_required'];
	$info['resource_time'] 		=  @$POST['resource_time'];
	$info['shift_count'] 		=  isset($POST['shift_count']) ? $POST['shift_count'] : '0' ;
	$info['shift_days'] 		= trim(implode(",", @$_POST['shift_days']),",");
	$info['resource_display'] 		=  @$POST['resource_display'];
	$info['automrp_display'] 		=  @$POST['automrp_display'];

	$info['automatic_approval_indent']		=  @$POST['automatic_approval_indent'];
	$info['automatic_approval_po']			=  @$POST['automatic_approval_po'];
	$info['automatic_finance_approval_po']	=  @$POST['automatic_finance_approval_po'];
	$info['automatic_shortclose_approval_po'] =  @$POST['automatic_shortclose_approval_po'];
	$info['automatic_approval_quotation'] =  @$POST['automatic_approval_quotation'];
	$info['automatic_approval_proforma'] =  @$POST['automatic_approval_proforma'];
	$info['automatic_approval_so'] =  @$POST['automatic_approval_so'];
	$info['automatic_approval_order_acceptance'] =  @$POST['automatic_approval_order_acceptance'];

	$info['enable_item_image'] =  @$POST['enable_item_image'];
	$info['enable_item_description'] =  @$POST['enable_item_description'];
	$info['header_text'] =  @$POST['header_text'];
	$info['header_logo'] =  @$POST['header_logo'];

	$info['ewb_username'] =  @$_POST['ewb_username'];
	$info['ewb_password'] =  @$_POST['ewb_password'];
	$info['einv_username'] =  @$_POST['einv_username'];
	$info['einv_password'] =  @$_POST['einv_password'];

	$info['enable_material_center'] =  @$_POST['enable_material_center'];
	$info['so_invo_descri_transfer'] =  @$_POST['so_invo_descri_transfer'];

	$info['crm_print_letterhead_per'] =  @$_POST['crm_print_letterhead_per'];
	$info['purchase_print_letterhead_per'] =  @$_POST['purchase_print_letterhead_per'];
	$info['finance_print_letterhead_per'] =  @$_POST['finance_print_letterhead_per'];
	$info['sales_print_letterhead_per'] =  @$_POST['sales_print_letterhead_per'];
	$info['production_print_letterhead_per'] =  @$_POST['production_print_letterhead_per'];
	
	$info['forecast_base'] =  @$_POST['forecast_base'];
	$info['forecast_calculation'] =  @$_POST['forecast_calculation'];

	$info['so_discount_editable'] 			=  @$_POST['so_discount_editable'];
	$info['so_calculation_discount_show'] 	=  @$_POST['so_calculation_discount_show'];
	$info['invoice_discount_editable'] 		=  @$_POST['invoice_discount_editable'];
	$info['invoice_calculation_discount_show'] =  @$_POST['invoice_calculation_discount_show'];
	$info['quot_revise_time_rate_with_discount'] = @$_POST['quot_revise_time_rate_with_discount'];
	$info['sales_order_print_after_approval'] = @$_POST['sales_order_print_after_approval'];

	$info['crm_sales_order_user_selecation'] =  @$_POST['crm_sales_order_user_selecation'];
	$info['crm_sales_order_user_type_selecation'] =  trim(implode(",", @$_POST['crm_sales_order_user_type_selecation']),",");

	$info['heat_no_saperator']			=	@$_POST['heat_no_saperator'];
	$info['followup_inquiry_show']		=	@$_POST['followup_inquiry_show'];
	$info['packing_module']				=	@$_POST['packing_module'];
	$info['direct_sales_allocate']		=	@$_POST['direct_sales_allocate'];
	$info['category_selection_active']	=	@$_POST['category_selection_active'];
	$info['cat_wise_product_load']		=	@$_POST['cat_wise_product_load'];
	
	$info['ip_add_login']	=	@$_POST['ip_add_login'];


	$info['quotation_header_content'] = stripcslashes(str_replace(array("\n", "\r", "\N"), '', @$_POST['quotation_header_content']));
	$info['so_header_content'] = stripcslashes(str_replace(array("\n", "\r", "\N"), '', @$_POST['so_header_content']));
	$info['po_header_content'] = stripcslashes(str_replace(array("\n", "\r", "\N"), '', @$_POST['po_header_content']));
	$info['invoice_header_content'] = stripcslashes(str_replace(array("\n", "\r", "\N"), '', @$_POST['invoice_header_content']));

	$info['store_relese_first_process'] = $_POST['store_relese_first_process'];

	$info_3['label_print_process_id']	= $_POST['label_print_process_id'];
	$info_3['supplier_tc_no']			= @$_POST['supplier_tc_no'];
	$info_3['wo_bw_alloc_stock']		= @$_POST['wo_bw_alloc_stock'];
	$info_3['customer_show_in_production']		= @$_POST['customer_show_in_production'];
	$info_3['production_on_dashboard']		= @$_POST['production_on_dashboard'];
	$info_3['set_reserve_godown']		= @$_POST['set_reserve_godown'];
	$info_3['default_godown_id']		= @$_POST['default_godown_id'];
	$info_3['jobwork_grn']				= @$_POST['jobwork_grn'];
	$info_3['smpl_mfg_licence']			= @$_POST['smpl_mfg_licence'];
	$info_3['qc_unit']			= @$_POST['qc_unit'];

	if(@$POST['closing_date_diff'] != ""){
			$info['closing_date_diff']			= @$POST['closing_date_diff'];
		}

		$info['inq_name_using_comapany']			= @$POST['inq_name_using_comapany'];

	if($getspecialConfiguration['smpl_permission'] ==1)
	{
	$info['smpl_batch_prefix'] = $_POST['smpl_batch_prefix'];
	$info_3['smpl_dl_no'] = $_POST['smpl_dl_no'];
	}
	//pathik add 17-10-2022 start
	$info['default_branch_id'] = isset($POST['branch_id']) ? $POST['branch_id'] : '0' ;
	//pathik add 17-10-2022 end

	//pathik so stock allocate start
	
	$info['so_temp_auto_allocate'] = $POST['so_temp_auto_allocate'];
	//pathik so stock allocate end
	

	//echo "<pre>"; print_r($info);echo"</pre>";
	if(empty(@$_POST['company_conf_id'])){
		$insertUpdate=add_record('tbl_company_configuration', $info, $dbcon);
		$insertUpdate1=update_record('tbl_company_configuration', $info_3,"company_conf_id=".$insertUpdate, $dbcon);
		// $insertUpdate=add_record('tbl_company_configuration', $info_3, $dbcon);
		
		if(@$POST['finance_year_type']!='')
		{
			$info1['finance_year_type'] 	= @$POST['finance_year_type'];
			$info1['fiancial_year'] 		= @$POST['fiancial_year'];
			$info1['financial_start_date'] 	= date("Y-m-d",strtotime(@$POST['financial_start_date']));
			$info1['financial_end_date'] 	= date("Y-m-d",strtotime(@$POST['financial_end_date']));
			
			add_record('tbl_financial_year', $info1, $dbcon);
		}

		if(@$POST['aging_start_days1']!='' && @$POST['aging_start_days1']!='0')
		{
			for($i=1;$i<=5;$i++)
			{
				$aging_start_days = @$POST['aging_start_days'.$i];
				$aging_end_days = @$POST['aging_end_days'.$i];

				$info22['slab_start_day'] = @$aging_start_days;
				$info22['slab_end_day'] = @$aging_end_days;

				add_record('tbl_aging_slab', $info22,$dbcon);
			}
		}

		
	}else{
		$insertUpdate=update_record('tbl_company_configuration', $info,"company_conf_id=".@$_POST['company_conf_id'] , $dbcon);
		$insertUpdate1=update_record('tbl_company_configuration', $info_3,"company_conf_id=".@$_POST['company_conf_id'] , $dbcon);
		if(@$POST['finance_year_type']!='')
		{
			$info1['finance_year_type'] 	= @$POST['finance_year_type'];
			$info1['fiancial_year'] 		= @$POST['fiancial_year'];
			$info1['financial_start_date'] 	= date("Y-m-d",strtotime(@$POST['financial_start_date']));
			$info1['financial_end_date'] 	= date("Y-m-d",strtotime(@$POST['financial_end_date']));
			
			update_record('tbl_financial_year', $info1,"current_status='1' AND company_id=".$_SESSION['company_id'] ,$dbcon);
		}
		if(@$POST['inventory_management'] != ''){
			$info2['inventory_management'] 		= @$POST['inventory_management']; 
		}
		if(@$POST['send_email'] != ''){
			$info2['send_email'] 				= @$POST['send_email']; 
		}
		/*if(@$POST['qc_unit'] != ''){
			$info2['qc_unit'] 				= @$POST['qc_unit']; 
		}*/
		if(@$POST['crm_auto_mail'] != ""){
			$info4['crm_auto_mail'] 			= @$POST['crm_auto_mail']; 
		}
		if(@$POST['project_wise_manufacturing'] != ""){
			$info4['project_wise_manufacturing']= @$POST['project_wise_manufacturing'];
		}
		if(@$POST['project_wise_item_rate'] != ""){
			$info4['project_wise_item_rate']	= @$POST['project_wise_item_rate'];
		}
		if(@$POST['max_followup_date'] != ""){
			$info4['max_followup_date']			= @$POST['max_followup_date'];
		}

		//if(@$_POST['quotation_print_content']){
			$info4['quotation_print_content']	= stripcslashes(str_replace(array("\n", "\r", "\N"), '', @$_POST['quotation_print_content']));
		/*}
		if($_POST['quotation_footer_content']){*/
			$info4['quotation_footer_content']	= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $_POST['quotation_footer_content']));
		//}
		
		if(@$POST['smtp_email'] != ""){
			$info2['smtp_email']		= @$POST['smtp_email'];		
		}
		if(@$POST['smtp_password'] != ""){
			$info2['smtp_password']		= @$POST['smtp_password'];
		}
		$info2['letter_head_top_margin']	= @$POST['letter_head_top_margin'];
		$info2['letter_head_bottom_margin']	= @$POST['letter_head_bottom_margin'];
		$info2['letter_head_left_margin']	= @$POST['letter_head_left_margin'];
		$info2['letter_head_right_margin']	= @$POST['letter_head_right_margin'];

		$info2['header_logo_height']		= @$POST['header_logo_height'];
		$info2['header_logo_width']			= @$POST['header_logo_width']; 	

		update_record('tbl_company', $info2,"company_id=$_SESSION[company_id]" ,$dbcon);
		update_record('tbl_company_settings', $info4,"id=$POST[com_set_id]" ,$dbcon);
		//echo @$POST['aging_start_days1'];exit;
		if(@$POST['aging_start_days1']!='')
		{
			for($i=1;$i<=5;$i++)
			{
				$aging_start_days = @$POST['aging_start_days'.$i];
				$aging_end_days = @$POST['aging_end_days'.$i];

				$info22['slab_start_day'] = $aging_start_days;
				$info22['slab_end_day'] = $aging_end_days;

				update_record('tbl_aging_slab', $info22,"slab_name='$i' and company_id='$_SESSION[company_id]'" ,$dbcon);
			}
		}
	}
	add_batch_wise_stock($dbcon); //  add BATCH NO in tbl_invoicetype if not exist
	
	if($insertUpdate)
		echo "1";
	else
		echo "0";
}
else if(strtolower($POST['mode']) == "edit") {
	
	$infousr['user_name'] =	$info['company_name']= @$POST['company_name'];
	$infousr['user_address'] = $info['address']	= stripcslashes(str_replace(array("\n", "\r", "\N"), '',@$_POST['address']));
	$info['contact_no']	= @$POST['contact_no'];
	$info['website']	= @$POST['website'];
	$info['company_website']= @$POST['company_website'];
	$info['bank_name']	= @$POST['bank_name'];
	$info['ac_no']		= @$POST['ac_no'];
	$info['ifcs']		= @$POST['ifcs'];
	$info['branch_name']    = @$POST['branch_name'];
	$info['vatno']		= strtoupper(@$POST['gstno']);
	$info['iec_no']		= strtoupper(@$POST['iec_no']);
	$info['lut_no']		= strtoupper(@$POST['lut_no']);
	$info['cin']		= @$POST['cin'];
	$filter_valid_till_date 	= explode(" - ",@$POST['valid_till_date']);
	$info['valid_till_date_start']= date('Y-m-d',strtotime($filter_valid_till_date[0]));
	$info['valid_till_date_end']= date('Y-m-d',strtotime($filter_valid_till_date[1]));
	$info['pan_no']		= @$POST['pan_no'];
	$info['stateid']	= @$POST['stateid'];
	$info['city_id']	= @$POST['city_id'];
	$info['pincode'] = @$POST['pincode'];
	$info['currency_id'] = @$POST['currency_id'];
	$info['serno']		= @$POST['serno'];
	$info['ser_date']	= date('Y-m-d',strtotime(@$POST['ser_date']));
	$info['pan_no']		= @$POST['pan_no'];
	$info['quot_condition']		= @$POST['quot_condition'];
	$info['coverlator_content']	= @$POST['coverlator_content'];
	$info['quot_content']		= @$POST['quot_email_content']; 

    $info['quot_validity']		= @$POST['quot_validity']; // added by Dimple Panchal
    $info['inventory_management']	= @$POST['inventory_management']; // added by Dimple Panchal
    $info['tcs_applicable']	= @$POST['tcs_applicable']; // added by Dimple Panchal
    $info['send_email']	= @$POST['send_email']; // added by Sanat Mamtora : 10-08-2021
    // $info['qc_unit']	= @$POST['qc_unit']; // added by Sanat Mamtora : 10-08-2021

	
    if(!empty(@$_FILES['logo']['tmp_name'])) {
    	$q="select * from tbl_company where company_id=".@$POST['eid'];
    	$row=mysqli_fetch_assoc($dbcon->query($q));		
    	$file=$row['logo'];
    	unlink(LOGO_A.$file);
    	unlink(LOGO_A."thumb//".$file);
		$comp = get_company_data($dbcon,$_SESSION['company_id']);
    	$info['logo'] = upload_image($_FILES,$comp['cmp_unique_id']);
    }
    if(!empty(@$_FILES['f_logo']['tmp_name'])) {
    	$q="select * from tbl_company where company_id=".@$POST['eid'];
    	$row=mysqli_fetch_assoc($dbcon->query($q));
    	$file=$row['f_logo'];
    	unlink(LOGO_A.$file);
    	unlink(LOGO_A."thumb//".$file);
    	$comp = get_company_data($dbcon,$_SESSION['company_id']);
    	$info['f_logo']	= upload_image1($_FILES,$comp['cmp_unique_id']);
    }
    if(!empty(@$_FILES['authorized_signature']['tmp_name'])) {
    	$q="select * from tbl_company where company_id=".@$POST['eid'];
    	$row=mysqli_fetch_assoc($dbcon->query($q));
    	$file=$row['authorized_signature'];
    	unlink("../../../view/upload/signature/".$file);
    	unlink("../../../view/upload/signature/thumb//".$file);
    	$comp = get_company_data($dbcon,$_SESSION['company_id']);
    	$info['authorized_signature']	= upload_image2($_FILES,$comp['cmp_unique_id']);
    	$infousr['authorized_signature']	= upload_image2($_FILES,$comp['cmp_unique_id']);
    }
    $info['perfoma_condition']		= stripslashes(@$_POST['export_condition']);
    $info['conditions']			= stripslashes(@$_POST['condition']);
    $info['challan_condition']	= stripslashes(@$_POST['challan_condition']);
    $info['quot_subject']		= stripslashes(@$_POST['quot_subject']);
    $info['po_condition']		= @$_POST['po_condition'];
    $info['logo_content']		= @$_POST['logo_content'];
    $info['dispatch_head_content']		= @$_POST['dispatch_head_content'];
    $info['dispatch_footer_content']	= @$_POST['dispatch_footer_content'];
    $info['lead_email_content']		= @$_POST['lead_email_content'];
    $info['inquiry_email_content']	= @$_POST['inquiry_email_content'];
    $info['installation_warranty']	= @$_POST['installation_warranty'];
    $info['signature']	= @$_POST['signature'];
    $info['cdate']		= date("Y-m-d H:i:s");
    $info['user_id']	= $_SESSION['user_id'];
	//$info['grn_sticker_print']				= @$_POST['grn_sticker_print'];
	//$info['batch_type']						= @$_POST['batch_type'];
	//$info['batch_process']					= @$_POST['batch_process'];


	//Amish Soni Start 04-02-2021
    $cmp_unique_id = strtoupper(@$POST['cmp_unique_id']);
    $uniqueQuery = "select company_id from tbl_company where cmp_unique_id = '$cmp_unique_id'";
    $uniqueRow = mysqli_fetch_assoc($dbcon->query($uniqueQuery));
    $existingCompanyId = $uniqueRow['company_id'];

    if($existingCompanyId && $existingCompanyId != @$POST['eid']) {
    	echo "-2";
    } else {
    	$updateid = update_record('tbl_company', $info, "company_id=" . @$POST['eid'], $dbcon);
		//	$infousr['user_rid']  = $inserid;

    	$infousr['user_company'] = @$POST['company_name'];

    	$updateuserid = update_record('users', $infousr, "user_type=2 and company_id='" . @$POST['eid'] . "' and user_rid=" . @$POST['eid'], $dbcon);

    	if ($updateid)
    		echo "update";
    	else
    		echo "0" . $dbcon->error;
    }
} else if(strtolower($POST['mode']) == "load_financial_year"){
	$appData = array();
	$i=1;
	$aColumns = array('fyear.financial_year_id','fyear.finance_year_type','fyear.fiancial_year','fyear.financial_start_date','fyear.financial_end_date','fyear.current_status');
	$sIndexColumn = "fyear.financial_year_id";
	$isWhere = array("fyear.isdelete = 0 and fyear.company_id IN (0,$_SESSION[company_id])");
	$sTable = "tbl_financial_year as fyear";			
	$isJOIN = array("");
	$hOrder = "fyear.financial_year_id desc";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$color = $ap_status = $ap_title = $app_btn = '';
		if($row['current_status']==1){
			$color = 'color: blue; font-weight: bold;';
		}else{
			$color = '';
		}
		$row_data[] = '<span style="'.$color.'">'.$row['sr'].'</span>';
		$row_data[] = '<span style="'.$color.'">'.(($row['finance_year_type']==1) ? 'March-April' : 'January-December').'</span>';
		$row_data[] = '<span style="'.$color.'">'.$row['fiancial_year'].'</span>';
		$row_data[] = '<span style="'.$color.'">'.date("d-M-Y",strtotime($row['financial_start_date'])).' - '.date("d-M-Y",strtotime($row['financial_end_date'])).'</span>';
		$row_data[] = '<span style="'.$color.'">'.(($row['current_status']==1) ? 'Current Year' : '-').'</span>';
		$ap_status = ($row['current_status']==1) ? 0 : 1;
		$ap_title = ($row['current_status']==1) ? 'Deactive Current Year' : 'Active Current Year';
		if($row['current_status']==1){
			$app_btn = '<button type="button" class="btn btn-xs btn-primary" data-original-title="'.$ap_title.'" data-toggle="tooltip" data-placement="top" onClick="active_financial_year('.$row['financial_year_id'].','.$ap_status.');"><i class="fa fa-check"></i></button>';
		}else{
			$app_btn = '<button type="button" class="btn btn-xs btn-warning" data-original-title="'.$ap_title.'" data-toggle="tooltip" data-placement="top" onClick="active_financial_year('.$row['financial_year_id'].','.$ap_status.');"><i class="fa fa-times"></i></button>';
		}
		$row_data[] = $app_btn; 
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
} else if(strtolower($POST['mode']) == "active_financial_year"){
	if($POST['status']==0){
		$info1['current_status'] 	= @$POST['status'];
		update_record('tbl_financial_year', $info1,"financial_year_id=".$POST['eid'] ,$dbcon);
		echo "1";
	}else{
		$info12['current_status'] 	= '0';
		update_record('tbl_financial_year', $info12,"current_status='1' AND company_id=".$_SESSION['company_id'] ,$dbcon);
		$info1['current_status'] 	= @$POST['status'];
		update_record('tbl_financial_year', $info1,"financial_year_id=".$POST['eid'] ,$dbcon);

		$chfinace = $dbcon->query("SELECT * FROM tbl_financial_year WHERE financial_year_id = ".$POST['eid']);
		$getfi = mysqli_fetch_assoc($chfinace);
		$_SESSION['financial_year_id']=$getfi['financial_year_id'];
		$_SESSION['fiancial_year']=$getfi['fiancial_year'];
		$_SESSION['financial_start_date']=$getfi['financial_start_date'];
		$_SESSION['financial_end_date']=$getfi['financial_end_date'];

		echo "1";
// echo "0";
	}
} else if(strtolower($POST['mode']) == "add_new_fyear"){
	$chfi = $dbcon->query("SELECT * FROM tbl_financial_year WHERE isdelete = 0 AND company_id=".$_SESSION['company_id']." AND finance_year_type = ".$POST['finance_year_type']." AND fiancial_year = ".$POST['fiancial_year']);
	if(brp_mysqli_num_rows($chfi)<=0){
		$chfina = $dbcon->query("SELECT * FROM tbl_financial_year WHERE isdelete = 0 AND current_status = 1 AND company_id=".$_SESSION['company_id']);
		$getfi = brp_mysqli_fetch_assoc($chfina);
		if($POST['status']==1){
			$info12['current_status'] 	= '0';
			update_record('tbl_financial_year', $info12,"current_status='1' AND company_id=".$_SESSION['company_id'] ,$dbcon);
		}
		$info1['finance_year_type'] 	= @$POST['finance_year_type'];
		$info1['fiancial_year'] 		= @$POST['fiancial_year'];
		$info1['financial_start_date'] 	= date("Y-m-d",strtotime(@$POST['financial_start_date']));
		$info1['financial_end_date'] 	= date("Y-m-d",strtotime(@$POST['financial_end_date']));
		$info1['isdelete'] 				= 0;
		$info1['current_status'] 		= @$POST['status'];
		$info1['user_id'] 				= @$_SESSION['user_id'];
		$info1['company_id'] 			= @$_SESSION['company_id'];
		$info1['usertype_id'] 			= @$_SESSION['user_type'];
		$info1['cdate'] 			= date("Y-m-d h:i:s");

		$insertUpdate = add_record('tbl_financial_year', $info1, $dbcon);
		if($insertUpdate){
			if($POST['status']==1){
				$_SESSION['financial_year_id']=@$insertUpdate;
				$_SESSION['fiancial_year']=@$POST['fiancial_year'];
				$_SESSION['financial_start_date']=date("Y-m-d",strtotime(@$POST['financial_start_date']));
				$_SESSION['financial_end_date']=date("Y-m-d",strtotime(@$POST['financial_end_date']));
			}
			$start_invo_ser = ($POST['series_start_text']==0) ? '0' : '`taxinvoice_start`';
			// $financial_year_id = ($POST['status']==1) ? $insertUpdate : $getfi['financial_year_id'];
			$chfinace = $dbcon->query("SELECT * FROM tbl_invoicetype WHERE financial_year_id = ".$insertUpdate." AND company_id=".$_SESSION['company_id']);
			if(brp_mysqli_num_rows($chfinace)<=0){
				$dbcon->query("INSERT INTO `tbl_invoicetype`(`invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`,`deletable`,`cdate`, `user_id`,`usertype_id`, `company_id`, `branch_id`,`gst_code`,`financial_year_id`) SELECT  `invoice_type`, ".$start_invo_ser.", `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, '".$POST['series_end_text']."', `deletable`,'".date("Y-m-d h:i:s")."', `user_id`,`usertype_id`, `company_id`, `branch_id`,`gst_code`, '".$insertUpdate."' FROM `tbl_invoicetype` WHERE status=0 AND company_id='".$_SESSION['company_id']."'");
			}
			echo "1";
		}
		else{
			echo "0";
		}
	}else{
		echo "2";
	}
} else if(strtolower($POST['mode']) == "branch_wise_manages"){
	$dbcon->query("UPDATE `tbl_company_configuration` SET `branch_wise_manage` = '".$POST['val']."'  WHERE isdelete = 0 AND company_id = ".$_SESSION['company_id']);
	if($POST['val']!='1'){
		$dbcon->query("UPDATE `tbl_company_configuration` SET `sales_wise_branch_planning` = '0'  WHERE company_id = ".$_SESSION['company_id']);
		$qrys = $dbcon->query("SELECT branch_id FROM branch_mst WHERE branch_status = 0 AND company_id = ".$_SESSION['company_id']." ORDER BY branch_id ASC LIMIT 1");
		$rtys = brp_mysqli_fetch_assoc($qrys);
		$dbcon->query("UPDATE `branch_mst` SET `isdefault` = '1' WHERE `branch_id` = '".$rtys['branch_id']."'");
		$_SESSION['branch_id'] = $rtys['branch_id'];
		$query = "SHOW TABLES";
		$q = $dbcon->query($query);
		$res = brp_mysqli_fetch_all($q);
		$database = DB;
		$qry = $dbcon->query("SELECT branch_id FROM branch_mst WHERE isdefault = 1 AND branch_status = 0 AND company_id = ".$_SESSION['company_id']." LIMIT 1");
		$rty = brp_mysqli_fetch_assoc($qry);
		foreach($res as $branchs){
			$dbcon->query("UPDATE ".$branchs['Tables_in_' . $database]." SET `branch_id` = '".$rty['branch_id']."' WHERE `company_id` = '".$_SESSION['company_id']."'");
		}
	}else{
		$dbcon->query("UPDATE `tbl_company_configuration` SET `sales_wise_branch_planning` = '0'  WHERE company_id = ".$_SESSION['company_id']);
		$res = getUserDetailById($dbcon,$_SESSION['user_id']);
		$_SESSION['branch_id'] = $res['branch_id'];
	}
	echo "1";
} else if(strtolower($POST['mode']) == "add_user_wise_approval"){
	$query = "select * from tbl_userwise_approval_setting where status=0 and permission_user_id=".$POST['permission_user_id']." and module_type=".$POST['module_type']." and company_id=".$_SESSION['company_id'];
	$result = $dbcon->query($query);
	$cnt = brp_mysqli_num_rows($result);
	if($cnt>0){
		$arr['msg'] = "-1";
	}else{
		
		$info['permission_user_id'] = $POST['permission_user_id'];
		$info['amount_type'] = $POST['amount_percentage_type'];
		if ($POST['amount_percentage_type'] == 1) {
			$info['amount']		= $POST['amount'];
		} else {
			$info['percentage'] = $POST['percentage'];
		}	
		
		$info['auto_approval'] 		= $POST['auto_approval'];
		$info['module_type']		= $POST['module_type'];
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];
		$info['cdate'] 				= date("Y-m-d h:i:s");
		$insertid = add_record('tbl_userwise_approval_setting', $info, $dbcon);

		if($insertid){
			$arr['msg'] = "1";
		}else{
			$arr['msg'] = "0";
		}
	}
	echo json_encode($arr);
} else if(strtolower($POST['mode']) == "load_userwise_approval"){

	$appData = array();
	$i=1;

	$where='';
	if($POST['module_type']){
		$where = ' and module_type='.$POST['module_type'];
	}
	$aColumns = array('uaps.aprv_setting_id','uaps.amount','uaps.percentage','uaps.auto_approval','us.user_name','ust.usertype_name','uaps.status','uaps.module_type');
	$sIndexColumn = "uaps.aprv_setting_id";
	$isWhere = array("uaps.status = 0 and uaps.company_id IN (0,$_SESSION[company_id])".$where);
	$sTable = "tbl_userwise_approval_setting as uaps";			
	$isJOIN = array("left join users as us on us.user_id=uaps.permission_user_id","left join tbl_usertype as ust on ust.usertype_id=us.user_type");
	$hOrder = "uaps.aprv_setting_id desc";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();

		if($row['auto_approval']==1){
			$auto_app = '<strong style="color:green">Yes</strong>';
		}else{
			$auto_app = '<strong style="color:red">No</strong>';
		}
		/*var_dump($auto_app);*/
		$row_data[] = $id;
		$row_data[] = $row['user_name'].' ('.$row['usertype_name'].')';
		$row_data[] = $row['amount'];
		if($POST['module_type'] == 1){
			$row_data[] = $row['percentage'];
		}
		$row_data[] = $auto_app;
		
		$edit = '';$delete='';

		$edit = '<button type="button" class="btn btn-xs " style="color:#fff;border-color:#eea236;background-color:#f0ad4e" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onclick="edit_userwise_approval('.$row['aprv_setting_id'].');"><i class="fa fa-pencil"></i></button>';
		
		$delete = '<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_userwise_approval('.$row['aprv_setting_id'].','.$row['module_type'].')"><i class="fa fa-trash-o"></i></button>';
		
		/*var_dump($edit);*/

		$row_data[] = $edit.' '.$delete; 
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
} else if(strtolower($POST['mode']) == "preedit"){
	$query = "select * from tbl_userwise_approval_setting where aprv_setting_id=".$POST['id'];
	$result = $dbcon->query($query);
	$row = brp_mysqli_fetch_array($result);
	echo json_encode($row);
} else if(strtolower($POST['mode']) == "delete_userwise_approval"){

	$info['status']=2;
	$insertid = update_record('tbl_userwise_approval_setting', $info,"aprv_setting_id=".$POST['eid'] ,$dbcon);
	if($insertid)
		$row['res']="1";
	else
		$row['res']="0";
	echo json_encode($row);
} else if(strtolower($POST['mode']) == "update_user_wise_approval"){
	$query = "select * from tbl_userwise_approval_setting where status=0 and permission_user_id=".$POST['permission_user_id']." and module_type=".$POST['module_type']." and company_id=".$_SESSION['company_id']." and aprv_setting_id !=".$POST['aprv_setting_id'];
	$result = $dbcon->query($query);
	$cnt = brp_mysqli_num_rows($result);
	if($cnt>0){
		$arr['msg'] = "-1";
	}else{
		$info['permission_user_id'] = $POST['permission_user_id'];
		$info['amount']				= $POST['amount'];
		$info['percentage']				= $POST['percentage'];
		$info['auto_approval'] 		= $POST['auto_approval'];
		$info['module_type']		= $POST['module_type'];
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];
		$info['cdate'] 				= date("Y-m-d h:i:s");
		//var_dump($info);exit;
		$insertid = update_record('tbl_userwise_approval_setting', $info,"aprv_setting_id=".$POST['aprv_setting_id'] ,$dbcon);
		if($insertid){
			$arr['msg'] = "1";
			$arr['module_type'] = $POST['module_type'];
		}else{
			$arr['msg'] = "0";
		}
	}
	echo json_encode($arr);
} else if(strtolower($POST['mode']) == "fetch_new_year"){
		$last_id=find_last_fince_year_id($dbcon);
		$query = "select financial_year_id,financial_start_date,financial_end_date from tbl_financial_year where isdelete=0 and financial_year_id=".$last_id;
		$result = $dbcon->query($query);
		$row = brp_mysqli_fetch_array($result);
		$start_year_old=date('Y', strtotime($row['financial_start_date']));
		$start_year_old_s=date('y', strtotime($row['financial_start_date']));
		$end_year_old=date('Y', strtotime($row['financial_end_date']));
		$end_year_old_s=date('y', strtotime($row['financial_end_date']));

		$start_year=$start_year_old+1;
		$start_year_s=$start_year_old_s+1;
		$end_year=$end_year_old+1;
		$end_year_s=$end_year_old_s+1;
		$year=$start_year."-".$end_year_s;
		$start_date_old=date('d-m', strtotime($row['financial_start_date']));
		$end_date_old=date('d-m', strtotime($row['financial_end_date']));

		$start_date=$start_date_old."-".$start_year;
		$end_date=$end_date_old."-".$end_year;

		$series_year_new="/".$start_year_s."-".$end_year_s;

		$row['start_date']=$start_date;
		$row['end_date']=$end_date;
		$row['year']=$year;
		$row['year_perent_id']=$last_id;
		$row['series_year_new']=$series_year_new;

	echo json_encode($row);
}else if(strtolower($POST['mode']) == "update_year_change"){
		$info1['current_status'] = 0;
		$insertwid = update_record('tbl_financial_year', $info1,"isdelete=0 and company_id=".$_SESSION['company_id'] ,$dbcon);
		
		$query = "select financial_year_id,finance_year_type from tbl_financial_year where financial_year_id=".$POST['year_perent_id'];
		$result = $dbcon->query($query);
		$row = brp_mysqli_fetch_array($result);

		$info['finance_year_type'] 		= $row['finance_year_type'];
		$info['fiancial_year']			= $POST['year_new'];
		$info['financial_start_date'] 	= date('Y-m-d', strtotime($POST['start_year_new']));
		$info['financial_end_date']		= date('Y-m-d', strtotime($POST['end_year_new']));
		$info['current_status']			= 1;
		$info['perent_id']				= $row['financial_year_id'];

		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];
		$info['cdate'] 				= date("Y-m-d h:i:s");
		$insertid = add_record('tbl_financial_year', $info, $dbcon);

		if($insertid){
			$_SESSION['financial_year_id']=$insertid;
			$_SESSION['fiancial_year']=$info['fiancial_year'];
			$_SESSION['financial_start_date']=$info['financial_start_date'];
			$_SESSION['financial_end_date']=$info['financial_end_date'];

			$query1 = "select * from tbl_invoicetype where status=0 and financial_year_id=".$row['financial_year_id'];
			$result1 = $dbcon->query($query1);
			while($row1=mysqli_fetch_array($result1)){
				
				if($POST['start_series_update_new']=="1"){
					$start_series_update_new=0;
				}else{
					$start_series_update_new=$row1['taxinvoice_start'];
				}
				if($POST['end_formate_series']==1){
					$end_format_value=$POST['series_year_new'];
				}else{
					$end_format_value=$row1['end_format_value'];
				}
				$info2['invoice_type'] 			= $row1['invoice_type'];
				$info2['taxinvoice_start']		= $start_series_update_new;
				$info2['exciseinvoice_start'] 	= $row1['exciseinvoice_start'];
				$info2['type_id']				= $row1['type_id'];
				$info2['invoice_format']		= $row1['invoice_format'];;
				$info2['format_value']			= $row1['format_value'];
				$info2['end_format_value']		= $end_format_value;
				$info2['deletable']				= $row1['deletable'];
				$info2['branch_id']				= $row1['branch_id'];
				$info2['gst_code']				= $row1['gst_code'];
				$info2['financial_year_id']		= $insertid;
				
				$info2['user_id']				= $_SESSION['user_id'];
				$info2['company_id']			= $_SESSION['company_id'];
				$info2['cdate'] 				= date("Y-m-d h:i:s");
				$insertids = add_record('tbl_invoicetype', $info2, $dbcon);
			}
			$row['res']=1;
		}else{
			$row['res']=0;
		}
		echo json_encode($row);
	}else if(strtolower($POST['mode']) == "add_whatsapp_config"){

		$company_conf_id = $_POST['company_conf_id'];
		$info['whatsapp_api_key']	= trim($POST['whatsapp_key']);
		$info['enable_whatsapp']	= $POST['enable_whatsapp'];
		$info['whatsapp_api_url']	= trim($POST['whatsapp_url']);
		$info['whatsapp_template']= trim($POST['whatsapp_template']);
		
		$insertUpdate = update_record('tbl_company_configuration', $info,"company_conf_id=".$company_conf_id , $dbcon);

		if($insertUpdate)
			$res['res']=1;
		else
			$res['res']=0;

		echo json_encode($res);
	}

function find_last_fince_year_id($dbcon){
	$query = "select financial_year_id from tbl_financial_year where isdelete=0 and company_id=".$_SESSION['company_id'];
	$result = $dbcon->query($query);
	while($row = brp_mysqli_fetch_array($result)){
		$query1 = "select financial_year_id from tbl_financial_year where isdelete=0 and perent_id=".$row['financial_year_id']." and company_id=".$_SESSION['company_id'];
		$result1 = $dbcon->query($query1);
		$cnt = brp_mysqli_num_rows($result1);
		//$row1 = brp_mysqli_fetch_array($result1);
		if($cnt==0){
			$res_id=$row['financial_year_id'];
		}
		
	}
	return $res_id;
}

function upload_image($FILES,$cmp_unique_id)
{
	 $cmp_unique_id =trim($cmp_unique_id);
	$rand=rand(0,9999);
	if(!empty($FILES['logo']['tmp_name'])) {

		list($width, $height, $type, $attr) = getimagesize($FILES['logo']['tmp_name']);
		
		if (isset($type) && in_array($type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
			$allowedExts = array("gif", "jpeg", "jpg", "png");
			$temp = explode(".", $FILES["logo"]["name"]);
			$extension = strtolower(end($temp));
			if (in_array($extension, $allowedExts)) {
				$File = $cmp_unique_id."_header_".$rand.".".$extension;
				$File = str_replace("/", "-", $File);
				$tmp_name = $FILES["logo"]["tmp_name"];				
				$r = move_uploaded_file($tmp_name,LOGO_A.$File);
				// smart_resize_image(LOGO_A.$File,792,100);
			}
		}
		return  $File;				
	}
}

function upload_image1($FILES,$cmp_unique_id)
{
	 $cmp_unique_id =trim($cmp_unique_id);
	$rand=rand(0,9999);
	if(!empty($FILES['f_logo']['tmp_name'])) {
		list($width, $height, $type, $attr) = getimagesize($FILES['f_logo']['tmp_name']);
		if (isset($type) && in_array($type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
			$allowedExts = array("gif", "jpeg", "jpg", "png");
			$temp = explode(".", $FILES["f_logo"]["name"]);
			$extension = strtolower(end($temp));
			if (in_array($extension, $allowedExts)) {
				$File = $cmp_unique_id."_footer_".$rand.".".$extension;
				$File = str_replace("/", "-", $File);
				$tmp_name = $FILES["f_logo"]["tmp_name"];
				move_uploaded_file($tmp_name,LOGO_A.$File);
				// smart_resize_image(LOGO_A.$File,792,80);
			}
		}
		return  $File;				
	}	
}

function upload_image2($FILES,$cmp_unique_id)
{
	 $cmp_unique_id =trim($cmp_unique_id);
	$rand=rand(0,9999);
	if(!empty($FILES['authorized_signature']['tmp_name'])) {
		list($width, $height, $type, $attr) = getimagesize($FILES['authorized_signature']['tmp_name']);
		if (isset($type) && in_array($type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
			$allowedExts = array("gif", "jpeg", "jpg", "png");
			$temp = explode(".", $FILES["authorized_signature"]["name"]);
			$extension = strtolower(end($temp));
			if (in_array($extension, $allowedExts)) {
				$File = $cmp_unique_id."_signature_".$rand.".".$extension;
				$File = str_replace("/", "-", $File);
				$tmp_name = $FILES["authorized_signature"]["tmp_name"];
				move_uploaded_file($tmp_name,SIGNATURE.$File);
				// smart_resize_image(SIGNATURE.$File,80,80);
			}
		}
		return  $File;				
	}	
}

//Sanat Mamtora Start 17-09-2021
function add_batch_wise_stock($dbcon){
	$query="select * from tbl_invoicetype where status=0 and type_id=30 and company_id=".$_SESSION['company_id'];
	// echo $query;die;
	$result=$dbcon->query($query);
	$count=brp_mysqli_num_rows($result);

	if($count == 0){
		$info['invoice_type'] = "BATCH";
		$info['taxinvoice_start'] = "0";
		$info['exciseinvoice_start'] = "0";
		$info['type_id'] = "30";
		$info['invoice_format'] = "3";
		$info['format_value'] = "BATCH/";
		$info['end_format_value'] = "/2020-21";
		$info['deletable'] = "1";
		$info['status'] = "0";
		$info['cdate']	= date("Y-m-d H:i:s");
		$info['user_id'] = $_SESSION['user_id'];
		$info['usertype_id'] = $_SESSION['usertype_id'];
		$info['company_id']	= $_SESSION['company_id'];

		add_record('tbl_invoicetype', $info, $dbcon);

	}
}